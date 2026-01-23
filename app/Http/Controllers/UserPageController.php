<?php

namespace App\Http\Controllers;

use App\Models\Task;
use App\Models\User;
use App\Models\Materi;
use App\Models\Subject;
use App\Models\Collection;
use App\Models\Exam;
use App\Models\ExamAttempt;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use PhpOffice\PhpSpreadsheet\Calculation\Statistical\Distributions\F;

class UserPageController extends Controller
{
    public function showSubject()
    {
        $user = auth()->user();
        $student = $user->student;

        if (!$student || !$student->class_id) {
            return redirect()->back()->with('error', 'Anda belum memiliki kelas.');
        }

        $kelasId = $student->class_id;

        $subjects = Subject::withCount([
            'materi' => function ($query) use ($kelasId) {
                $query->whereHas('classes', function ($q) use ($kelasId) {
                    $q->where('classes.id', $kelasId);
                });
            },
            'Task as task_count' => function ($query) use ($user) {
                $query->whereHas('collections', function ($subQuery) use ($user) {
                    $subQuery->where('user_id', $user->id)
                        ->where('status', 'Belum mengumpulkan');
                });
            }
        ])->paginate(6);

        return view('Siswa.mapel', compact('subjects'));
    }

    public function showMateriBySubject(Request $request, $materi_id)
    {
        $user = auth()->user();
        $order = $request->input('order', 'desc');
        $search = $request->input('search');
        $activeTab = $request->input('tab', 'materis');
        $status = $request->input('status');
        $student = $user->student;
        if ($activeTab === 'materi') {
            // Query untuk tab "materi"
        } elseif ($activeTab === 'tugas') {
            // Query untuk tab "tugas"
        }
        $student = $user->student;

        if (!$student || !$student->class_id) {
            return redirect()->back()->with('error', 'Anda belum memiliki kelas.');
        }

        $kelasID = [$student->class_id];


        // Query Materi
        $materis = Materi::whereHas('classes', function ($query) use ($kelasID) {
            $query->whereIn('class_id', $kelasID);
        })
            ->with('subject', 'classes')
            ->where('subject_id', $materi_id)
            ->where('title_materi', 'like', '%' . $search . '%')
            ->orderBy('created_at', $order)
            ->paginate(5);

        // Ambil URL file dari item pertama
        $fileUrl = $materis->first()?->file_materi
            ? Storage::url($materis->first()->file_materi)
            : null;

        // Query Task
        $tasksQuery = Task::select('tasks.*', 'collections.status as collection_status')
            ->with([
                'collections' => function ($query) {
                    $query->where('user_id', Auth::id());
                }
            ])
            ->leftJoin('collections', function ($join) {
                $join->on('tasks.id', '=', 'collections.task_id')
                    ->where('collections.user_id', '=', Auth::id());
            })
            ->whereHas('classes', function ($query) use ($kelasID) {
                $query->whereIn('classes.id', $kelasID);
            })
            ->where(function ($query) use ($search) {
                $query->where('title_task', 'like', '%' . $search . '%')
                    ->orWhereHas('Subject', function ($q) use ($search) {
                        $q->where('name_subject', 'like', '%' . $search . '%');
                    });
            })
            ->where('subject_id', $materi_id)
            ->orderByRaw("FIELD(collections.status, 'Belum mengumpulkan', 'Sudah mengumpulkan', 'Tidak mengumpulkan') ASC")
            ->orderBy('created_at', 'desc');

        if ($status) {
            $tasksQuery->whereHas('collections', function ($query) use ($status) {
                $query->where('user_id', Auth::id());
                if ($status == 'Sudah mengumpulkan') {
                    $query->where('status', 'Sudah mengumpulkan');
                } elseif ($status == 'Belum mengumpulkan') {
                    $query->whereNotIn('status', ['Sudah mengumpulkan', 'Tidak mengumpulkan']);
                } elseif ($status == 'Tidak mengumpulkan') {
                    $query->where('status', 'Tidak mengumpulkan');
                }
            });
        }

        $tasks = $tasksQuery->paginate(5);
        $countSiswa = User::whereHas('roles', function ($query) {
            $query->where('roles.name', 'Murid');
        })
            ->whereHas('classes', function ($query) use ($kelasID) {
                $query->whereIn('classes_id', $kelasID);
            })
            ->count();

        $subjectName = Subject::whereHas('materi', function ($query) use ($materi_id) {
            $query->where('subject_id', $materi_id);
        })
            ->orWhereHas('Task', function ($q) use ($materi_id) {
                $q->where('subject_id', $materi_id);
            })->distinct()->pluck('name_subject')->first();

        $teacherName = User::whereHas('tasks', function ($query) use ($materi_id) {
            $query->where('subject_id', $materi_id);
        })
            ->orWhereHas('materis', function ($query) use ($materi_id) {
                $query->where('subject_id', $materi_id);
            })
            ->distinct()->pluck('name')->first();

        return view('Siswa.materi', compact('materis', 'tasks', 'subjectName', 'materi_id', 'activeTab', 'countSiswa', 'teacherName'));
    }

    public function showTask(Request $request)
    {
        $user = auth()->user();
        $search = $request->input('search');
        $status = $request->input('status');

        $student = $user->student;

        // JIKA USER BUKAN SISWA
        if (!$student) {
            return view('user.tugas', [
                'tasks' => collect()
            ]);
        }

        // AMBIL CLASS ID SISWA
        $classId = $student->class_id;

        $tasksQuery = Task::leftJoin('collections', function ($join) {
            $join->on('tasks.id', '=', 'collections.task_id')
                ->where('collections.user_id', auth()->id());
        })
            ->select(
                'tasks.*',
                DB::raw("COALESCE(collections.status, 'Belum mengumpulkan') as collection_status")
            )
            ->whereHas('classes', function ($query) use ($classId) {
                $query->where('classes.id', $classId);
            })
            ->when($search, function ($query) use ($search) {
                $query->where('tasks.title_task', 'like', "%$search%")
                    ->orWhereHas('subject', function ($q) use ($search) {
                        $q->where('name_subject', 'like', "%$search%");
                    });
            })
            ->when($status, function ($query) use ($status) {
                if ($status === 'Belum mengumpulkan') {
                    $query->whereNull('collections.status');
                } else {
                    $query->where('collections.status', $status);
                }
            })
            ->orderByRaw("
        FIELD(collections.status,
            'Belum mengumpulkan',
            'Sudah mengumpulkan',
            'Tidak mengumpulkan'
        )
    ");

        if ($status) {
            $tasksQuery->whereHas('collections', function ($query) use ($status) {
                $query->where('user_id', Auth::id());
                $query->where('status', $status);
            });
        }

        $tasks = $tasksQuery->paginate(5);

        $this->updateTaskStatus();

        return view('Siswa.tugas', compact('tasks'));
    }

    private function updateTaskStatus()
    {
        $now = now();

        // Perbarui status jika sudah melewati deadline
        $collections = Collection::whereHas('task', function ($query) use ($now) {
            $query->where('date_collection', '<', $now);
        })->where('status', '!=', 'Tidak mengumpulkan')->get();

        foreach ($collections as $collection) {
            $collection->update(['status' => 'Tidak mengumpulkan']);
        }
    }

    public function showAllMateri(Request $request)
    {
        $user = auth()->user();
        $student = $user->student;

        if (!$student || !$student->class_id) {
            return redirect()->back()->with('error', 'Kelas siswa tidak ditemukan');
        }

        $classId = $student->class_id;
        $search  = $request->search;

        $materis = Materi::with(['subject', 'classes'])
            ->whereHas('classes', function ($q) use ($classId) {
                $q->where('classes.id', $classId);
            })
            ->when($search, function ($q) use ($search) {
                $q->where(function ($qq) use ($search) {
                    $qq->where('title_materi', 'like', "%$search%")
                        ->orWhereHas('subject', function ($sub) use ($search) {
                            $sub->where('name_subject', 'like', "%$search%");
                        });
                });
            })
            ->orderBy('created_at', 'desc')
            ->paginate(6);

        return view('Siswa.semuamateri', compact('materis'));
    }

    public function showMateri($id)
    {
        $user = auth()->user();
        $student = $user->student;

        if (!$student || !$student->class_id) {
            abort(403, 'Kelas siswa tidak ditemukan');
        }

        $materi = Materi::with(['subject', 'classes'])
            ->whereHas('classes', function ($q) use ($student) {
                $q->where('classes.id', $student->class_id);
            })
            ->where('id', $id)
            ->firstOrFail();

        return view('Siswa.materi-detail', compact('materi'));
    }


    public function Dashboard()
    {
        $user = auth()->user();
        if ($user && $user->class) {
            $class = $user->class;
        } else {
            $class = collect();
        }

        $countNotCollected = Collection::where('status', 'Belum mengumpulkan')->where('user_id', $user->id)->count();
        $countCollected = Collection::where('status', 'Sudah mengumpulkan')->where('user_id', $user->id)->count();
        return view('Siswa.dashboard', compact('class', 'countNotCollected', 'countCollected'));
    }

    public function showSoal(Request $request)
    {
        try {
            $user = Auth::user();

            // Debug untuk memastikan user adalah siswa
            \Log::info('User Info:', [
                'user_id' => $user->id,
                'name' => $user->name,
                'roles' => $user->getRoleNames(),
                'has_student' => $user->student ? 'Yes' : 'No'
            ]);

            // Cek jika user adalah siswa
            if (!$user->hasRole('Murid')) {
                return back()->with('error', 'Hanya siswa yang dapat mengakses halaman ini.');
            }

            // Cek jika user memiliki data student
            if (!$user->student) {
                \Log::error('User does not have student record:', ['user_id' => $user->id]);
                return view('Siswa.soal', [
                    'exams' => collect(),
                    'error' => 'Data siswa tidak ditemukan. Silakan hubungi administrator.'
                ]);
            }

            // Cek jika student memiliki class_id
            $student = $user->student;
            if (!$student->class_id) {
                \Log::warning('Student has no class:', ['student_id' => $student->id, 'user_id' => $user->id]);
                return view('Siswa.soal', [
                    'exams' => collect(),
                    'error' => 'Anda belum memiliki kelas. Silakan tunggu hingga administrator menugaskan Anda ke kelas.'
                ]);
            }

            $kelasId = $student->class_id;
            $search = $request->input('search');
            $status = $request->input('status');

            // Query dasar dengan relasi yang benar
            $query = Exam::with([
                'subject',
                'teacher.user' // PERBAIKAN: Ambil teacher lalu user dari teacher
            ])
                ->where('class_id', $kelasId)
                ->where('status', 'active')
                ->where(function ($query) {
                    $query->whereNull('end_at')
                        ->orWhere('end_at', '>=', now());
                });

            // Filter pencarian
            if ($search) {
                $query->where(function ($q) use ($search) {
                    $q->where('title', 'like', '%' . $search . '%')
                        ->orWhereHas('subject', function ($subjectQuery) use ($search) {
                            $subjectQuery->where('name_subject', 'like', '%' . $search . '%');
                        })
                        ->orWhereHas('teacher.user', function ($teacherQuery) use ($search) {
                            $teacherQuery->where('name', 'like', '%' . $search . '%');
                        });
                });
            }

            // Debug query
            \Log::info('Exam Query:', [
                'kelas_id' => $kelasId,
                'sql' => $query->toSql(),
                'bindings' => $query->getBindings()
            ]);

            // Ambil semua data untuk filtering status
            $allExams = $query->orderBy('created_at', 'desc')->get();

            // Tambahkan status untuk setiap exam
            foreach ($allExams as $exam) {
                $attempt = ExamAttempt::where('exam_id', $exam->id)
                    ->where('student_id', $user->id)
                    ->latest()
                    ->first();

                $exam->status = $this->getExamStatus($exam, $attempt);
                $exam->attempt = $attempt;
                $exam->questions_count = $exam->questions()->count();

                // Debug untuk melihat data guru
                \Log::info('Exam Teacher Data:', [
                    'exam_id' => $exam->id,
                    'exam_title' => $exam->title,
                    'teacher_id' => $exam->teacher_id,
                    'teacher_exists' => $exam->teacher ? 'Yes' : 'No',
                    'teacher_user_name' => $exam->teacher ? ($exam->teacher->user->name ?? 'No user name') : 'No teacher',
                    'teacher_user_email' => $exam->teacher ? ($exam->teacher->user->email ?? 'No email') : 'No teacher'
                ]);
            }

            // Filter berdasarkan status jika ada
            if ($status) {
                $statusMap = [
                    'belum_dikerjakan' => 'available',
                    'sudah_dikerjakan' => 'completed',
                    'kadaluarsa' => 'expired'
                ];

                $targetStatus = $statusMap[$status] ?? $status;

                $filteredExams = $allExams->filter(function ($exam) use ($targetStatus) {
                    return $exam->status === $targetStatus;
                });

                // Convert ke paginator
                $page = $request->input('page', 1);
                $perPage = 9;
                $paginatedExams = new \Illuminate\Pagination\LengthAwarePaginator(
                    $filteredExams->forPage($page, $perPage),
                    $filteredExams->count(),
                    $perPage,
                    $page,
                    ['path' => $request->url(), 'query' => $request->query()]
                );

                $exams = $paginatedExams;
            } else {
                // Jika tidak ada filter status, gunakan pagination biasa
                $exams = $query->orderBy('created_at', 'desc')->paginate(9);

                // Tambahkan status untuk exams yang dipaginate
                foreach ($exams as $exam) {
                    $attempt = ExamAttempt::where('exam_id', $exam->id)
                        ->where('student_id', $user->id)
                        ->latest()
                        ->first();

                    $exam->status = $this->getExamStatus($exam, $attempt);
                    $exam->attempt = $attempt;
                    $exam->questions_count = $exam->questions()->count();

                    // Debug untuk melihat data guru
                    \Log::info('Exam Teacher Data (Paginated):', [
                        'exam_id' => $exam->id,
                        'teacher_name' => $exam->teacher ? ($exam->teacher->user->name ?? 'Unknown') : 'No teacher'
                    ]);
                }
            }

            return view('Siswa.soal', compact('exams'));
        } catch (\Exception $e) {
            \Log::error('Error in showSoal: ' . $e->getMessage() . ' at ' . $e->getFile() . ':' . $e->getLine());
            return back()->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    public function showSoalDetail($exam_id)
    {
        try {
            $user = Auth::user();

            // Pastikan user adalah siswa
            if (!$user->hasRole('Murid')) {
                abort(403, 'Hanya siswa yang dapat mengakses halaman ini.');
            }

            // Pastikan user memiliki data student
            if (!$user->student) {
                abort(403, 'Data siswa tidak ditemukan.');
            }

            // Pastikan student memiliki kelas
            if (!$user->student->class_id) {
                abort(403, 'Anda belum memiliki kelas.');
            }

            // Gunakan Exam model dengan relasi yang benar
            $exam = Exam::with([
                'subject',
                'teacher.user', // PERBAIKAN: Ambil teacher lalu user dari teacher
                'questions.choices'
            ])
                ->where('id', $exam_id)
                ->firstOrFail();

            // Debug: Cek data guru
            \Log::info('Exam Detail - Teacher Info:', [
                'exam_id' => $exam->id,
                'title' => $exam->title,
                'teacher_id' => $exam->teacher_id,
                'teacher_exists' => !is_null($exam->teacher),
                'teacher_user_name' => $exam->teacher ? ($exam->teacher->user->name ?? 'Teacher user not found') : 'Teacher not found'
            ]);

            // Cek apakah exam tersedia untuk kelas siswa
            if ($exam->class_id !== $user->student->class_id) {
                return back()->with('error', 'Anda tidak memiliki akses ke ujian ini');
            }

            // Cek apakah sudah ada attempt yang belum disubmit
            $ongoingAttempt = ExamAttempt::where('exam_id', $exam_id)
                ->where('student_id', $user->id)
                ->where('status', 'in_progress')
                ->first();

            // Cek apakah sudah pernah submit
            $completedAttempt = ExamAttempt::where('exam_id', $exam_id)
                ->where('student_id', $user->id)
                ->where('status', 'submitted')
                ->latest()
                ->first();

            return view('Siswa.soal-detail', compact('exam', 'ongoingAttempt', 'completedAttempt'));
        } catch (\Exception $e) {
            \Log::error('Error in showSoalDetail: ' . $e->getMessage());
            return back()->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    private function getExamStatus($exam, $attempt)
    {
        if (!$exam) return 'not_available';

        if ($attempt) {
            if ($attempt->status === 'submitted') {
                return 'completed';
            } elseif ($attempt->status === 'in_progress') {
                return 'ongoing';
            }
        }

        // Cek apakah masih dalam rentang waktu
        if ($exam->start_at && now() < $exam->start_at) {
            return 'upcoming';
        }

        if ($exam->end_at && now() > $exam->end_at) {
            return 'expired';
        }

        return 'available';
    }
}
