<?php

namespace App\Http\Controllers;

use App\Models\Task;
use App\Models\User;
use App\Models\Materi;
use App\Models\Subject;
use App\Models\Collection;
use App\Models\Exam;
use App\Models\ExamAttempt;
use App\Models\Student;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class UserPageController extends Controller
{
    // =========================================================
    // DASHBOARD
    // =========================================================

    public function Dashboard()
    {
        $user    = auth()->user();
        $student = $user->student;

        $class = $student?->currentClass()?->first()?->name_class ?? 'Belum ada kelas';

        $currentAssignment = $student?->classAssignments()
            ->whereHas('academicYear', fn($q) => $q->where('is_active', true))
            ->first();
        $kelasId = $currentAssignment?->class_id;

        $countNotCollected = 0;
        $countCollected    = 0;

        if ($kelasId) {
            $countNotCollected = Collection::where('status', 'Belum mengumpulkan')
                ->where('user_id', $user->id)
                ->whereHas('task', fn($q) => $q->whereHas('classes', fn($cq) => $cq->where('classes.id', $kelasId)))
                ->count();

            $countCollected = Collection::where('status', 'Sudah mengumpulkan')
                ->where('user_id', $user->id)
                ->whereHas('task', fn($q) => $q->whereHas('classes', fn($cq) => $cq->where('classes.id', $kelasId)))
                ->count();
        }

        $totalTasks         = $countCollected + $countNotCollected;
        $progressPercentage = $totalTasks > 0 ? round(($countCollected / $totalTasks) * 100) : 0;
        $recentActivities   = $this->getRecentActivities($user);

        return view('Siswa.dashboard', compact(
            'class', 'countNotCollected', 'countCollected', 'progressPercentage', 'recentActivities'
        ));
    }

    private function getRecentActivities($user)
    {
        $activities = [];

        $recentCollections = Collection::where('user_id', $user->id)
            ->with('task.subject')
            ->where('status', 'Sudah mengumpulkan')
            ->orderBy('updated_at', 'desc')
            ->limit(3)
            ->get();

        foreach ($recentCollections as $collection) {
            if ($collection->task) {
                $activities[] = [
                    'title'     => 'Mengumpulkan Tugas',
                    'subtitle'  => $collection->task->title_task,
                    'time'      => $this->formatTimeAgo($collection->updated_at),
                    'timestamp' => $collection->updated_at,
                    'subject'   => $collection->task->subject->name_subject ?? 'Umum',
                ];
            }
        }

        if (count($activities) < 3) {
            $recentExams = ExamAttempt::where('student_id', $user->id)
                ->with('exam.subject')
                ->where('status', 'submitted')
                ->orderBy('updated_at', 'desc')
                ->limit(3 - count($activities))
                ->get();

            foreach ($recentExams as $ea) {
                if ($ea->exam) {
                    $score        = $ea->score ? " (Nilai: {$ea->score})" : '';
                    $activities[] = [
                        'title'     => 'Menyelesaikan Ujian',
                        'subtitle'  => $ea->exam->title . $score,
                        'time'      => $this->formatTimeAgo($ea->updated_at),
                        'timestamp' => $ea->updated_at,
                        'subject'   => $ea->exam->subject->name_subject ?? 'Umum',
                    ];
                }
            }
        }

        if (count($activities) < 3) {
            $student           = $user->student;
            $currentAssignment = $student?->classAssignments()
                ->whereHas('academicYear', fn($q) => $q->where('is_active', true))
                ->first();

            if ($currentAssignment) {
                $kelasId  = $currentAssignment->class_id;
                $newExams = Exam::where('class_id', $kelasId)
                    ->where('status', 'active')
                    ->where(fn($q) => $q->whereNull('start_at')->orWhere('start_at', '<=', now()))
                    ->where(fn($q) => $q->whereNull('end_at')->orWhere('end_at', '>=', now()))
                    ->whereDoesntHave('attempts', fn($q) => $q->where('student_id', $user->id)->where('status', 'submitted'))
                    ->where('created_at', '>=', Carbon::now()->subDays(3))
                    ->orderBy('created_at', 'desc')
                    ->limit(3 - count($activities))
                    ->get();

                foreach ($newExams as $exam) {
                    $activities[] = [
                        'title'     => 'Ujian Baru Tersedia',
                        'subtitle'  => $exam->title . " ({$exam->questions()->count()} soal)",
                        'time'      => $this->formatTimeAgo($exam->created_at),
                        'timestamp' => $exam->created_at,
                        'subject'   => $exam->subject->name_subject ?? 'Umum',
                    ];
                }
            }
        }

        if (count($activities) < 3) {
            $student           = $user->student;
            $currentAssignment = $student?->classAssignments()
                ->whereHas('academicYear', fn($q) => $q->where('is_active', true))
                ->first();

            if ($currentAssignment) {
                $kelasId      = $currentAssignment->class_id;
                $recentMateri = Materi::whereHas('classes', fn($q) => $q->where('classes.id', $kelasId))
                    ->where('created_at', '>=', Carbon::now()->subDays(7))
                    ->orderBy('created_at', 'desc')
                    ->limit(3 - count($activities))
                    ->get();

                foreach ($recentMateri as $materi) {
                    $activities[] = [
                        'title'     => 'Materi Baru Tersedia',
                        'subtitle'  => $materi->title_materi,
                        'time'      => $this->formatTimeAgo($materi->created_at),
                        'timestamp' => $materi->created_at,
                        'subject'   => $materi->subject->name_subject ?? 'Umum',
                    ];
                }
            }
        }

        if (count($activities) < 3) {
            $defaults = [
                ['title' => 'Bergabung di Kelas',   'subtitle' => 'Memulai pembelajaran online', 'time' => 'Awal semester',  'timestamp' => Carbon::now()->subDays(30), 'subject' => 'Sistem'],
                ['title' => 'Menyelesaikan Kuis',   'subtitle' => 'Latihan soal pertama',        'time' => 'Minggu lalu',    'timestamp' => Carbon::now()->subDays(7),  'subject' => 'Latihan'],
                ['title' => 'Membaca Materi',        'subtitle' => 'Pengenalan materi baru',      'time' => '2 hari yang lalu','timestamp' => Carbon::now()->subDays(2),  'subject' => 'Pembelajaran'],
            ];
            for ($i = count($activities); $i < 3; $i++) {
                $activities[] = $defaults[$i] ?? $defaults[0];
            }
        }

        usort($activities, fn($a, $b) => $b['timestamp'] <=> $a['timestamp']);

        return array_slice($activities, 0, 3);
    }

    private function formatTimeAgo($timestamp)
    {
        if (!$timestamp) return 'Beberapa waktu lalu';
        $now = Carbon::now();
        $s   = $now->diffInSeconds($timestamp);
        $m   = $now->diffInMinutes($timestamp);
        $h   = $now->diffInHours($timestamp);
        $d   = $now->diffInDays($timestamp);
        if ($s < 60)   return 'Baru saja';
        if ($m < 60)   return "$m menit yang lalu";
        if ($h < 24)   return "$h jam yang lalu";
        if ($d < 30)   return "$d hari yang lalu";
        return Carbon::parse($timestamp)->locale('id')->translatedFormat('d F Y');
    }

    private function formatTimeLeft($futureTime)
    {
        if (!$futureTime || $futureTime <= now()) return '';
        $total   = now()->diffInMinutes($futureTime);
        $hours   = intdiv($total, 60);
        $minutes = $total % 60;
        if ($hours > 0 && $minutes > 0) return "{$hours} jam {$minutes} menit";
        if ($hours > 0) return "{$hours} jam";
        return "{$minutes} menit";
    }

    // =========================================================
    // SOAL / UJIAN
    // =========================================================

    public function showSoal(Request $request)
    {
        try {
            $user = Auth::user();

            if (!$user->hasRole('Murid')) {
                return back()->with('error', 'Hanya siswa yang dapat mengakses halaman ini.');
            }
            if (!$user->student) {
                return view('Siswa.soal', ['exams' => collect(), 'error' => 'Data siswa tidak ditemukan.']);
            }

            $student           = $user->student;
            $currentAssignment = $student->classAssignments()
                ->whereHas('academicYear', fn($q) => $q->where('is_active', true))
                ->first();

            if (!$currentAssignment) {
                return view('Siswa.soal', ['exams' => collect(), 'error' => 'Anda belum memiliki kelas.']);
            }

            $kelasId = $currentAssignment->class_id;
            $search  = $request->input('search');
            $status  = $request->input('status');

            $query = Exam::with(['subject', 'teacher.user'])
                ->where('class_id', $kelasId)
                ->where('status', 'active')
                ->where(fn($q) => $q->whereNull('end_at')->orWhere('end_at', '>=', now()));

            if ($search) {
                $query->where(fn($q) => $q
                    ->where('title', 'like', "%$search%")
                    ->orWhere('type', 'like', "%$search%")
                    ->orWhereHas('subject', fn($sq) => $sq->where('name_subject', 'like', "%$search%"))
                    ->orWhereHas('teacher.user', fn($tq) => $tq->where('name', 'like', "%$search%"))
                );
            }

            if ($status) {
                switch ($status) {
                    case 'available':
                        $query->where(fn($q) => $q
                            ->whereDoesntHave('attempts', fn($sub) => $sub->where('student_id', $user->id)->whereIn('status', ['submitted', 'timeout']))
                            ->orWhereHas('attempts', fn($sub) => $sub->where('student_id', $user->id)->where('status', 'in_progress'))
                        );
                        break;
                    case 'completed':
                        $query->whereHas('attempts', fn($q) => $q->where('student_id', $user->id)->whereIn('status', ['submitted', 'timeout']));
                        break;
                    case 'expired':
                        $query->where('end_at', '<', now());
                        break;
                    case 'upcoming':
                        $query->where('start_at', '>', now());
                        break;
                }
            }

            $exams = $query->orderBy('created_at', 'desc')->paginate(12);

            foreach ($exams as $exam) {
                $attempt               = ExamAttempt::where('exam_id', $exam->id)->where('student_id', $user->id)->latest()->first();
                $exam->attempt         = $attempt;
                $exam->questions_count = $exam->questions()->count();
                $exam->attempt_count   = ExamAttempt::where('exam_id', $exam->id)->where('student_id', $user->id)->whereIn('status', ['submitted', 'timeout'])->count();
                $exam->can_retake      = $exam->limit_attempts > $exam->attempt_count;
            }

            return view('Siswa.soal', compact('exams'));
        } catch (\Exception $e) {
            Log::error('Error in showSoal: ' . $e->getMessage());
            return back()->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    public function showSoalDetail($examId)
    {
        try {
            $user    = Auth::user();
            $student = $user->student;

            if (!$student) return back()->with('error', 'Data siswa tidak ditemukan.');

            $currentAssignment = $student->classAssignments()
                ->whereHas('academicYear', fn($q) => $q->where('is_active', true))
                ->first();

            if (!$currentAssignment) return back()->with('error', 'Anda belum memiliki kelas.');

            $exam = Exam::with(['subject', 'teacher.user', 'questions' => fn($q) => $q->with('choices')])
                ->where('id', $examId)
                ->where('class_id', $currentAssignment->class_id)
                ->first();

            if (!$exam) return redirect()->route('soal.index')->with('error', 'Ujian tidak ditemukan.');

            if (!$this->isExamAccessibleForStudent($exam)) {
                return redirect()->route('soal.index')->with('error', 'Ujian tidak dapat diakses. ' . $this->getExamAccessibilityMessage($exam));
            }

            $attemptCount = ExamAttempt::where('exam_id', $examId)->where('student_id', $user->id)->whereIn('status', ['submitted', 'timeout'])->count();
            $canRetake    = $exam->limit_attempts > $attemptCount;
            $latestAttempt = ExamAttempt::where('exam_id', $examId)->where('student_id', $user->id)->orderBy('created_at', 'desc')->first();

            $exam->questions_count = $exam->questions->count();

            return view('Siswa.soal-detail', compact('exam', 'latestAttempt', 'attemptCount', 'canRetake'));
        } catch (\Exception $e) {
            Log::error('Error in showSoalDetail: ' . $e->getMessage());
            return back()->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    private function isExamAccessibleForStudent($exam): bool
    {
        if ($exam->status !== 'active') return false;
        if ($exam->type !== 'QUIZ') {
            if ($exam->start_at && now() < $exam->start_at) return false;
            if ($exam->end_at   && now() > $exam->end_at)   return false;
        }
        return true;
    }

    private function getExamAccessibilityMessage($exam): string
    {
        if ($exam->status !== 'active') return 'Status ujian: ' . $exam->status;
        if ($exam->type !== 'QUIZ') {
            if ($exam->start_at && now() < $exam->start_at) return 'Ujian dimulai pada ' . $exam->start_at->format('d M Y H:i');
            if ($exam->end_at   && now() > $exam->end_at)   return 'Ujian telah berakhir pada ' . $exam->end_at->format('d M Y H:i');
        }
        return 'Ujian tidak tersedia.';
    }

    // =========================================================
    // MATA PELAJARAN
    // =========================================================

    public function showSubject(Request $request)
    {
        $user    = auth()->user();
        $student = $user->student;

        if (!$student) return redirect()->back()->with('error', 'Data siswa tidak ditemukan.');

        $currentAssignment = $student->classAssignments()
            ->whereHas('academicYear', fn($q) => $q->where('is_active', true))
            ->with('academicYear')
            ->first();

        if (!$currentAssignment) return redirect()->back()->with('error', 'Anda belum memiliki kelas di tahun ajaran aktif.');

        $kelasId        = $currentAssignment->class_id;
        $academicYearId = $currentAssignment->academicYear->id ?? null;
        $search         = $request->input('search');
        $sort           = in_array($request->input('sort'), ['asc', 'desc']) ? $request->input('sort') : 'asc';
        $tugasFilter    = $request->input('tugas');

        $subjectIds = DB::table('teacher_subject_assignments')
            ->where('class_id', $kelasId)
            ->when($academicYearId, fn($q) => $q->where('academic_year_id', $academicYearId))
            ->pluck('subject_id')
            ->unique();

        if ($subjectIds->isEmpty()) {
            $subjectIds = Materi::whereHas('classes', fn($q) => $q->where('classes.id', $kelasId))
                ->pluck('subject_id')
                ->merge(Task::whereHas('classes', fn($q) => $q->where('classes.id', $kelasId))->pluck('subject_id'))
                ->unique();
        }

        if ($subjectIds->isEmpty()) {
            $subjects = Subject::whereRaw('1=0')->paginate(6);
        } else {
            $query = Subject::whereIn('id', $subjectIds)
                ->withCount([
                    'materis as materi_count' => fn($q) => $q->whereHas('classes', fn($sq) => $sq->where('classes.id', $kelasId)),
                    'tasks as unfinished_task_count' => fn($q) => $q
                        ->whereHas('classes', fn($sq) => $sq->where('classes.id', $kelasId))
                        ->whereHas('collections', fn($sq) => $sq->where('user_id', $user->id)->where('status', 'Belum mengumpulkan')),
                ])
                ->with(['teacherAssignments' => fn($q) => $q
                    ->where('class_id', $kelasId)
                    ->when($academicYearId, fn($sq) => $sq->where('academic_year_id', $academicYearId))
                    ->with('teacher.user')
                ]);

            if ($search) $query->where('name_subject', 'like', "%$search%");

            $subjects = $query->orderBy('name_subject', $sort)->paginate(6)->withQueryString();
        }

        return view('Siswa.mapel', compact('subjects', 'kelasId'));
    }

    // =========================================================
    // MATERI PER MAPEL
    // =========================================================

    public function showMateriBySubject(Request $request, $subjectId)
    {
        $user    = auth()->user();
        $student = $user->student;

        if (!$student) return redirect()->back()->with('error', 'Data siswa tidak ditemukan.');

        $currentAssignment = $student->classAssignments()
            ->whereHas('academicYear', fn($q) => $q->where('is_active', true))
            ->first();

        if (!$currentAssignment) return redirect()->back()->with('error', 'Anda belum memiliki kelas.');

        $kelasId   = $currentAssignment->class_id;
        $order     = in_array($request->input('order'), ['asc', 'desc']) ? $request->input('order') : 'desc';
        $search    = $request->input('search');
        $activeTab = in_array($request->input('tab'), ['materi', 'tugas']) ? $request->input('tab') : 'materi';
        $status    = $request->input('status');

        $materis = Materi::where('subject_id', $subjectId)
            ->whereHas('classes', fn($q) => $q->where('classes.id', $kelasId))
            ->when($search && $activeTab === 'materi', fn($q) => $q->where('title_materi', 'like', "%$search%"))
            ->orderBy('created_at', $order)
            ->paginate(5, ['*'], 'materi_page');

        $tasksQuery = Task::with(['collections' => fn($q) => $q->where('user_id', $user->id)])
            ->leftJoin('collections', function ($join) use ($user) {
                $join->on('tasks.id', '=', 'collections.task_id')
                    ->where('collections.user_id', $user->id);
            })
            ->select('tasks.*', 'collections.status as collection_status')
            ->where('subject_id', $subjectId)
            ->whereHas('classes', fn($q) => $q->where('classes.id', $kelasId))
            ->when($search && $activeTab === 'tugas', fn($q) => $q->where('title_task', 'like', "%$search%"))
            ->orderByRaw("FIELD(collections.status, 'Belum mengumpulkan', 'Sudah mengumpulkan', 'Tidak mengumpulkan') ASC")
            ->orderBy('tasks.created_at', 'desc');

        if ($status) {
            $tasksQuery->whereHas('collections', fn($q) => $q->where('user_id', $user->id)->where('status', $status));
        }

        $tasks       = $tasksQuery->paginate(5, ['*'], 'tugas_page');
        $subjectName = Subject::find($subjectId)?->name_subject ?? 'Mata Pelajaran';

        $countSiswa = $kelasId
            ? Student::whereHas('classAssignments', fn($q) => $q->where('class_id', $kelasId)->whereHas('academicYear', fn($ay) => $ay->where('is_active', true)))->count()
            : 0;

        $teacherName = 'Guru';
        if ($materis->first()?->user) {
            $teacherName = $materis->first()->user->name;
        } elseif ($tasks->first() && isset($tasks->first()->user)) {
            $teacherName = $tasks->first()->user->name;
        }

        return view('Siswa.materi', compact('materis', 'tasks', 'subjectName', 'subjectId', 'activeTab', 'countSiswa', 'teacherName'));
    }

    // =========================================================
    // SEMUA TUGAS
    // =========================================================

    public function showTask(Request $request)
    {
        $user    = auth()->user();
        $student = $user->student;

        if (!$student) return view('Siswa.tugas', ['tasks' => collect()]);

        $currentAssignment = $student->classAssignments()
            ->whereHas('academicYear', fn($q) => $q->where('is_active', true))
            ->first();

        if (!$currentAssignment) return view('Siswa.tugas', ['tasks' => collect()]);

        $kelasId = $currentAssignment->class_id;
        $search  = $request->input('search');
        $status  = $request->input('status');

        // FIX: updateTaskStatus SEBELUM query agar status sudah benar saat ditampilkan
        $this->updateTaskStatus($user->id, $kelasId);

        $tasksQuery = Task::leftJoin('collections', function ($join) use ($user) {
                $join->on('tasks.id', '=', 'collections.task_id')
                    ->where('collections.user_id', $user->id);
            })
            ->select('tasks.*', DB::raw("COALESCE(collections.status, 'Belum mengumpulkan') as collection_status"))
            ->whereHas('classes', fn($q) => $q->where('classes.id', $kelasId))
            // FIX: eager load 'materi' dan 'collections' dengan where user_id untuk tampil file jawaban di modal
            ->with([
                'subject',
                'materi',
                'collections' => fn($q) => $q->where('user_id', $user->id)->with('assessment'),
            ]);

        if ($search) {
            $tasksQuery->where(fn($q) => $q
                ->where('tasks.title_task', 'like', "%$search%")
                ->orWhereHas('subject', fn($sq) => $sq->where('name_subject', 'like', "%$search%"))
            );
        }

        if ($status) {
            if ($status === 'Belum mengumpulkan') {
                $tasksQuery->where(fn($q) => $q->whereNull('collections.status')->orWhere('collections.status', 'Belum mengumpulkan'));
            } else {
                $tasksQuery->where('collections.status', $status);
            }
        }

        $tasksQuery->orderByRaw("CASE
            WHEN collections.status = 'Belum mengumpulkan' OR collections.status IS NULL THEN 1
            WHEN collections.status = 'Sudah mengumpulkan' THEN 2
            WHEN collections.status = 'Tidak mengumpulkan' THEN 3
            ELSE 4 END ASC")
            ->orderBy('tasks.created_at', 'desc');

        $tasks = $tasksQuery->paginate(5);

        return view('Siswa.tugas', compact('tasks'));
    }

    /**
     * FIX: Tambah parameter $userId dan $kelasId agar tidak update tugas dari kelas lain.
     * Sebelumnya: update semua task milik user tanpa filter kelas → bisa salah scope.
     */
    private function updateTaskStatus(int $userId, int $kelasId): void
    {
        $now = now();

        // Update collections yang sudah melewati deadline
        Collection::where('user_id', $userId)
            ->where('status', 'Belum mengumpulkan')
            ->whereHas('task', fn($q) => $q
                ->where('date_collection', '<', $now)
                ->whereHas('classes', fn($cq) => $cq->where('classes.id', $kelasId))
            )
            ->update(['status' => 'Tidak mengumpulkan']);

        // Buat collection "Tidak mengumpulkan" untuk tugas yang belum punya collection sama sekali
        $tasksWithoutCollection = Task::whereHas('classes', fn($q) => $q->where('classes.id', $kelasId))
            ->where('date_collection', '<', $now)
            ->whereDoesntHave('collections', fn($q) => $q->where('user_id', $userId))
            ->get();

        foreach ($tasksWithoutCollection as $task) {
            Collection::firstOrCreate(
                ['task_id' => $task->id, 'user_id' => $userId],
                ['status' => 'Tidak mengumpulkan']
            );
        }
    }

    // =========================================================
    // SEMUA MATERI
    // =========================================================

    public function showAllMateri(Request $request)
    {
        $user    = auth()->user();
        $student = $user->student;

        if (!$student) return redirect()->back()->with('error', 'Data siswa tidak ditemukan.');

        $currentAssignment = $student->classAssignments()
            ->whereHas('academicYear', fn($q) => $q->where('is_active', true))
            ->first();

        if (!$currentAssignment) return redirect()->back()->with('error', 'Anda belum memiliki kelas.');

        $kelasId = $currentAssignment->class_id;
        $search  = $request->input('search');

        $materis = Materi::with(['subject', 'classes'])
            ->whereHas('classes', fn($q) => $q->where('classes.id', $kelasId))
            ->when($search, fn($q) => $q
                ->where('title_materi', 'like', "%$search%")
                ->orWhereHas('subject', fn($sq) => $sq->where('name_subject', 'like', "%$search%"))
            )
            ->orderBy('created_at', 'desc')
            ->paginate(6);

        return view('Siswa.semuamateri', compact('materis'));
    }

    // =========================================================
    // DETAIL MATERI
    // =========================================================

    public function showMateri($id)
    {
        try {
            $user    = auth()->user();
            $student = $user->student;

            if (!$student) abort(403, 'Data siswa tidak ditemukan.');

            $currentAssignment = $student->classAssignments()
                ->whereHas('academicYear', fn($q) => $q->where('is_active', true))
                ->first();

            if (!$currentAssignment) abort(403, 'Anda belum memiliki kelas.');

            $kelasId = $currentAssignment->class_id;

            $materi = Materi::with(['subject', 'classes'])
                ->whereHas('classes', fn($q) => $q->where('classes.id', $kelasId))
                ->where('id', $id)
                ->firstOrFail();

            // FIX: Normalisasi path file_materi agar tidak ada prefix public/ atau slash ganda
            // yang menyebabkan FileHelper::url() menghasilkan URL salah → 404
            if ($materi->file_materi) {
                $normalized = ltrim($materi->file_materi, '/');
                if (str_starts_with($normalized, 'public/')) {
                    $normalized = substr($normalized, 7);
                }
                // Assign ke object agar blade langsung pakai nilai yang bersih
                // (tidak ubah DB, hanya runtime)
                $materi->file_materi = $normalized;
            }

            // Tandai materi sudah dibaca di session server-side
            session()->put('materi_read_' . $materi->id, true);

            return view('Siswa.materi-detail', compact('materi'));

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            abort(404, 'Materi tidak ditemukan.');
        } catch (\Exception $e) {
            Log::error('showMateri error: ' . $e->getMessage(), ['id' => $id]);
            return redirect()->route('semuamateri')->with('error', 'Terjadi kesalahan saat memuat materi.');
        }
    }
}
