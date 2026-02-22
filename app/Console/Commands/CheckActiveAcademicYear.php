<?php

namespace App\Console\Commands;

use App\Models\AcademicYear;
use App\Models\Classes;
use App\Models\Student;
use App\Models\StudentClassAssignment;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

/**
 * php artisan academic:check-active
 *
 * Tugas command ini:
 * 1. Menemukan tahun ajaran yang seharusnya aktif berdasarkan tanggal hari ini.
 * 2. Mengaktifkan tahun ajaran tersebut jika belum aktif.
 * 3. Saat pergantian tahun ajaran, menjalankan promosi otomatis:
 *    - Siswa kelas X  → XI  (grade naik, nomor kelas dipertahankan jika ada)
 *    - Siswa kelas XI → XII
 *    - Siswa kelas XII → status diubah menjadi 'lulus'
 * 4. Me-regenerate student_code untuk tahun ajaran baru.
 */
class CheckActiveAcademicYear extends Command
{
    protected $signature   = 'academic:check-active';
    protected $description = 'Cek & aktifkan tahun ajaran yang sesuai, lalu promosikan siswa jika berganti tahun.';

    // Peta promosi grade
    private const GRADE_PROMOTION = [
        'X'   => 'XI',
        'XI'  => 'XII',
        'XII' => null,   // null = lulus
    ];

    public function handle(): int
    {
        $this->info('[' . now()->format('Y-m-d') . '] Mengecek tahun ajaran aktif...');

        // ── 1. Cari tahun ajaran yang sesuai hari ini ──────────────────────────
        $today      = now()->toDateString();
        $targetYear = AcademicYear::where('start_date', '<=', $today)
            ->where('end_date', '>=', $today)
            ->orderBy('start_date', 'desc')
            ->first();

        if (! $targetYear) {
            $this->warn('Tidak ada tahun ajaran yang mencakup tanggal hari ini. Tidak ada perubahan.');
            return self::SUCCESS;
        }

        $this->info("Tahun ajaran yang seharusnya aktif: [{$targetYear->id}] {$targetYear->name}");

        $currentActive = AcademicYear::where('is_active', true)->first();
        $isYearChanged = $currentActive && $currentActive->id !== $targetYear->id;

        // ── 2. Aktifkan tahun ajaran target ───────────────────────────────────
        if (! $currentActive || $currentActive->id !== $targetYear->id) {
            $oldName = $currentActive?->name ?? '-';
            $this->info("Pergantian: [{$oldName}] → [{$targetYear->name}]");

            AcademicYear::query()->update(['is_active' => false]);
            $targetYear->is_active = true;
            $targetYear->save();

            $this->info("✅ Tahun ajaran [{$targetYear->name}] berhasil diaktifkan.");
        } else {
            $this->info("✅ Tahun ajaran [{$targetYear->name}] sudah aktif. Tidak ada perubahan.");
            return self::SUCCESS;
        }

        // ── 3. Promosi siswa (hanya saat benar-benar berganti tahun ajaran) ───
        if ($isYearChanged) {
            $this->info('Menjalankan promosi otomatis siswa (X→XI, XI→XII, XII→lulus)...');
            $this->runPromotion($currentActive->id, $targetYear->id);
        }

        return self::SUCCESS;
    }

    /**
     * Jalankan promosi untuk semua siswa aktif:
     * - Ambil kelas siswa di tahun ajaran lama
     * - Tentukan kelas baru (grade naik, nomor kelas sama jika ada)
     * - Buat StudentClassAssignment baru
     * - Generate student_code baru
     * - Siswa XII → status 'lulus', tidak mendapat assignment baru
     *
     * @param  int $oldYearId   ID tahun ajaran yang baru saja dinonaktifkan
     * @param  int $newYearId   ID tahun ajaran yang baru saja diaktifkan
     */
    private function runPromotion(int $oldYearId, int $newYearId): void
    {
        // Ambil semua assignment di tahun ajaran lama beserta kelas & siswa
        $oldAssignments = StudentClassAssignment::with(['student.user', 'class.department'])
            ->where('academic_year_id', $oldYearId)
            ->get();

        if ($oldAssignments->isEmpty()) {
            $this->warn('  Tidak ada data assignment di tahun ajaran sebelumnya. Promosi dilewati.');
            return;
        }

        $promoted  = 0;
        $graduated = 0;
        $skipped   = 0;
        $errors    = 0;

        $bar = $this->output->createProgressBar($oldAssignments->count());
        $bar->start();

        foreach ($oldAssignments as $assignment) {
            $bar->advance();

            $student = $assignment->student;
            $class   = $assignment->class;

            if (! $student || ! $class) {
                $skipped++;
                continue;
            }

            // Skip siswa yang sudah lulus / keluar
            if (in_array($student->status, ['lulus', 'keluar'])) {
                $skipped++;
                continue;
            }

            $currentGrade = Student::extractGrade($class->name_class);
            $nextGrade    = self::GRADE_PROMOTION[$currentGrade] ?? null;

            DB::beginTransaction();
            try {
                if ($nextGrade === null) {
                    // ── Kelas XII: ubah status menjadi lulus ─────────────────
                    $student->status          = 'lulus';
                    $student->graduation_date = now()->toDateString();
                    $student->save();
                    $graduated++;
                } else {
                    // ── Kelas X / XI: cari kelas tujuan ─────────────────────
                    $classNumber = Student::extractClassNumber($class->name_class);
                    $deptId      = $class->department_id;

                    // Cari kelas dengan grade baru, jurusan sama, nomor kelas sama
                    $targetClass = Classes::where('department_id', $deptId)
                        ->get()
                        ->first(function ($cls) use ($nextGrade, $classNumber) {
                            return Student::extractGrade($cls->name_class)       === $nextGrade
                                && Student::extractClassNumber($cls->name_class) === $classNumber;
                        });

                    if (! $targetClass) {
                        // Fallback: ambil kelas pertama dengan grade & jurusan yang sesuai
                        $targetClass = Classes::where('department_id', $deptId)
                            ->get()
                            ->first(fn ($cls) => Student::extractGrade($cls->name_class) === $nextGrade);
                    }

                    if (! $targetClass) {
                        $this->newLine();
                        $this->warn("  ⚠ Kelas tujuan ({$nextGrade}) tidak ditemukan untuk siswa #{$student->id} ({$student->user?->name}). Dilewati.");
                        $skipped++;
                        DB::rollBack();
                        continue;
                    }

                    // Pastikan belum ada assignment di tahun baru
                    $alreadyAssigned = StudentClassAssignment::where('student_id', $student->id)
                        ->where('academic_year_id', $newYearId)
                        ->exists();

                    if (! $alreadyAssigned) {
                        StudentClassAssignment::create([
                            'student_id'       => $student->id,
                            'class_id'         => $targetClass->id,
                            'academic_year_id' => $newYearId,
                        ]);
                    }

                    // Generate kode baru untuk tahun ajaran baru
                    $student->assignNewCode($targetClass->id, $newYearId);
                    $promoted++;
                }

                DB::commit();
            } catch (\Exception $e) {
                DB::rollBack();
                $errors++;
                $this->newLine();
                $this->error("  ✗ Error pada siswa #{$student->id}: " . $e->getMessage());
            }
        }

        $bar->finish();
        $this->newLine(2);

        $this->info("Hasil promosi:");
        $this->info("  ✅ Dipromosikan  : {$promoted} siswa");
        $this->info("  🎓 Dinyatakan lulus : {$graduated} siswa");
        $this->info("  ⏭  Dilewati      : {$skipped} siswa");
        if ($errors > 0) {
            $this->error("  ✗  Error         : {$errors} siswa");
        }

        // ── 4. Re-generate semua kode untuk tahun baru ───────────────────────
        $this->info('Memperbarui kode siswa untuk tahun ajaran baru...');
        Student::updateCodesForAcademicYear($newYearId);
        $this->info('✅ Kode siswa berhasil diperbarui.');
    }
}
