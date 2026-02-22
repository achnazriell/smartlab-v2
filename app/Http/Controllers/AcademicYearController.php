<?php

namespace App\Http\Controllers;

use App\Models\AcademicYear;
use App\Models\Student;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AcademicYearController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $search  = $request->input('search', '');
        $sort    = $request->input('sort', 'newest');
        $perPage = $request->input('per_page', 10);

        $query = AcademicYear::query();

        if ($search) {
            $query->where('name', 'like', '%' . $search . '%');
        }

        switch ($sort) {
            case 'name_asc':
                $query->orderBy('name', 'asc');
                break;
            case 'name_desc':
                $query->orderBy('name', 'desc');
                break;
            case 'start_date_asc':
                $query->orderBy('start_date', 'asc');
                break;
            case 'start_date_desc':
                $query->orderBy('start_date', 'desc');
                break;
            case 'oldest':
                $query->orderBy('created_at', 'asc');
                break;
            case 'newest':
            default:
                $query->orderBy('created_at', 'desc');
                break;
        }

        $academicYears      = $query->paginate($perPage)->withQueryString();
        $totalAcademicYears = AcademicYear::count();
        $activeAcademicYear = AcademicYear::active()->first();

        return view('Admins.AcademicYears.index', compact(
            'academicYears',
            'search',
            'totalAcademicYears',
            'activeAcademicYear'
        ));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name'       => 'required|string|max:255|unique:academic_years,name',
            'start_date' => 'required|date',
            'end_date'   => 'required|date|after:start_date',
            'is_active'  => 'boolean',
        ], [
            'name.required'    => 'Nama tahun ajaran wajib diisi',
            'name.unique'      => 'Tahun ajaran sudah ada',
            'start_date.required' => 'Tanggal mulai wajib diisi',
            'end_date.required'   => 'Tanggal selesai wajib diisi',
            'end_date.after'      => 'Tanggal selesai harus setelah tanggal mulai',
        ]);

        DB::beginTransaction();
        try {
            if ($request->is_active) {
                AcademicYear::where('is_active', true)->update(['is_active' => false]);
            }

            AcademicYear::create([
                'name'       => $request->name,
                'start_date' => $request->start_date,
                'end_date'   => $request->end_date,
                'is_active'  => $request->is_active ?? false,
            ]);

            DB::commit();

            return redirect()->route('academic-years.index')
                ->with('success', 'Tahun ajaran ' . $request->name . ' berhasil ditambahkan');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Gagal menambahkan tahun ajaran: ' . $e->getMessage())->withInput();
        }
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        $academicYear = AcademicYear::withCount(['studentAssignments', 'teacherAssignments'])->findOrFail($id);

        return response()->json([
            'success' => true,
            'data'    => [
                'id'                        => $academicYear->id,
                'name'                      => $academicYear->name,
                'start_date'                => $academicYear->start_date->format('d/m/Y'),
                'end_date'                  => $academicYear->end_date->format('d/m/Y'),
                'is_active'                 => $academicYear->is_active,
                'student_assignments_count' => $academicYear->student_assignments_count,
                'teacher_assignments_count' => $academicYear->teacher_assignments_count,
                'createdAt'                 => $academicYear->created_at->format('d/m/Y H:i'),
                'updatedAt'                 => $academicYear->updated_at->format('d/m/Y H:i'),
            ],
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id)
    {
        $academicYear = AcademicYear::findOrFail($id);

        return response()->json([
            'success' => true,
            'data'    => [
                'id'         => $academicYear->id,
                'name'       => $academicYear->name,
                'start_date' => $academicYear->start_date->format('Y-m-d'),
                'end_date'   => $academicYear->end_date->format('Y-m-d'),
                'is_active'  => $academicYear->is_active,
            ],
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        $academicYear = AcademicYear::findOrFail($id);

        $request->validate([
            'name'       => 'required|string|max:255|unique:academic_years,name,' . $id,
            'start_date' => 'required|date',
            'end_date'   => 'required|date|after:start_date',
            'is_active'  => 'boolean',
        ], [
            'name.required'    => 'Nama tahun ajaran wajib diisi',
            'name.unique'      => 'Tahun ajaran sudah ada',
            'start_date.required' => 'Tanggal mulai wajib diisi',
            'end_date.required'   => 'Tanggal selesai wajib diisi',
            'end_date.after'      => 'Tanggal selesai harus setelah tanggal mulai',
        ]);

        DB::beginTransaction();
        try {
            if ($request->is_active && !$academicYear->is_active) {
                AcademicYear::where('is_active', true)->update(['is_active' => false]);
            }

            $academicYear->update([
                'name'       => $request->name,
                'start_date' => $request->start_date,
                'end_date'   => $request->end_date,
                'is_active'  => $request->is_active ?? false,
            ]);

            DB::commit();

            return redirect()->route('academic-years.index')
                ->with('success', 'Tahun ajaran ' . $request->name . ' berhasil diperbarui');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Gagal memperbarui tahun ajaran: ' . $e->getMessage())->withInput();
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        try {
            $academicYear = AcademicYear::findOrFail($id);

            if ($academicYear->is_active) {
                return response()->json([
                    'success' => false,
                    'message' => 'Tidak dapat menghapus tahun ajaran yang sedang aktif',
                ], 400);
            }

            $hasAssignments = $academicYear->studentAssignments()->count() > 0
                           || $academicYear->teacherAssignments()->count() > 0;

            if ($hasAssignments) {
                return response()->json([
                    'success' => false,
                    'message' => 'Tahun ajaran tidak dapat dihapus karena memiliki data penugasan terkait',
                ], 400);
            }

            $name = $academicYear->name;
            $academicYear->delete();

            return response()->json([
                'success' => true,
                'message' => 'Tahun ajaran ' . $name . ' berhasil dihapus',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal menghapus tahun ajaran: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Aktifkan tahun ajaran dan OTOMATIS naikkan kelas semua siswa.
     *
     * Alur:
     *  1. Simpan ID tahun ajaran yang sedang aktif (= tahun lama)
     *  2. Non-aktifkan semua tahun ajaran
     *  3. Aktifkan tahun ajaran yang dipilih (= tahun baru)
     *  4. Panggil Student::promoteStudentsToNewAcademicYear()
     *     → X  naik ke XI  (jurusan & nomor kelas sama)
     *     → XI naik ke XII (jurusan & nomor kelas sama)
     *     → XII dilewati (tidak dipindah otomatis)
     *  5. Kode siswa di-generate ulang sesuai kelas & tahun baru
     */
    public function setActive($id)
    {
        DB::beginTransaction();
        try {
            // 1. Catat tahun ajaran yang sedang aktif sebelum diubah
            $previousActiveYear = AcademicYear::where('is_active', true)->first();
            $oldAcademicYearId  = $previousActiveYear ? $previousActiveYear->id : null;

            // 2. Non-aktifkan semua
            AcademicYear::where('is_active', true)->update(['is_active' => false]);

            // 3. Aktifkan tahun ajaran yang dipilih
            $academicYear            = AcademicYear::findOrFail($id);
            $academicYear->is_active = true;
            $academicYear->save();

            // 4. Jalankan promosi otomatis jika ada tahun sebelumnya
            //    dan tahun yang diaktifkan berbeda dari tahun sebelumnya
            $promotionStats = null;
            if ($oldAcademicYearId && $oldAcademicYearId !== (int) $id) {
                $promotionStats = Student::promoteStudentsToNewAcademicYear(
                    $oldAcademicYearId,
                    $academicYear->id
                );
            }

            DB::commit();

            // Susun pesan respons
            $message = 'Tahun ajaran "' . $academicYear->name . '" berhasil diaktifkan.';
            if ($promotionStats) {
                $message .= ' Promosi kelas: ' . $promotionStats['promoted'] . ' siswa naik kelas';
                if ($promotionStats['skipped'] > 0) {
                    $message .= ', ' . $promotionStats['skipped'] . ' dilewati (kelas XII / sudah ada assignment)';
                }
                if ($promotionStats['not_found'] > 0) {
                    $message .= ', ' . $promotionStats['not_found'] . ' kelas tujuan tidak ditemukan';
                }
                $message .= '.';
            }

            return response()->json([
                'success'         => true,
                'message'         => $message,
                'promotion_stats' => $promotionStats,
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengaktifkan tahun ajaran: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Quick view for academic year details.
     */
    public function detail($id)
    {
        $academicYear = AcademicYear::withCount(['studentAssignments', 'teacherAssignments'])->findOrFail($id);

        return response()->json([
            'success' => true,
            'data'    => [
                'id'            => $academicYear->id,
                'name'          => $academicYear->name,
                'start_date'    => $academicYear->start_date->format('d/m/Y'),
                'end_date'      => $academicYear->end_date->format('d/m/Y'),
                'is_active'     => $academicYear->is_active,
                'student_count' => $academicYear->student_assignments_count,
                'teacher_count' => $academicYear->teacher_assignments_count,
                'createdAt'     => $academicYear->created_at->format('d/m/Y H:i'),
            ],
        ]);
    }
}
