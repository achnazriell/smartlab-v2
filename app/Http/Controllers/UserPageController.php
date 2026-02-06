<?php

namespace App\Http\Controllers;

use App\Models\Task;
use App\Models\User;
use App\Models\Materi;
use App\Models\Subject;
use App\Models\Collection;
use App\Models\Exam;
use App\Models\ExamAttempt;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

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

        // Cari subjects melalui teacher_class_subjects
        $subjects = Subject::whereHas('teacherClassSubjects.teacherClass', function ($query) use ($kelasId) {
            $query->where('classes_id', $kelasId);
        })
            ->withCount([
                'materi' => function ($query) use ($kelasId) {
                    $query->whereHas('classes', function ($q) use ($kelasId) {
                        $q->where('classes.id', $kelasId);
                    });
                },
                'Task as unfinished_task_count' => function ($query) use ($user, $kelasId) {
                    $query->whereHas('collections', function ($subQuery) use ($user) {
                        $subQuery->where('user_id', $user->id)
                            ->where('status', 'Belum mengumpulkan');
                    })
                        ->whereHas('classes', function ($q) use ($kelasId) {
                            $q->where('classes.id', $kelasId);
                        });
                }
            ])
            ->orderBy('name_subject')
            ->paginate(6);

        // Jika tidak ada subjects melalui teacher_class_subjects, coba cara lain
        if ($subjects->isEmpty()) {
            // Ambil subjects dari materi yang ada di kelas
            $subjectsFromMateri = Subject::whereHas('materi.classes', function ($query) use ($kelasId) {
                $query->where('classes.id', $kelasId);
            })->pluck('id');

            // Ambil subjects dari tugas yang ada di kelas
            $subjectsFromTask = Subject::whereHas('Task.classes', function ($query) use ($kelasId) {
                $query->where('classes.id', $kelasId);
            })->pluck('id');

            $subjectIds = $subjectsFromMateri->merge($subjectsFromTask)->unique();

            $subjects = Subject::whereIn('id', $subjectIds)
                ->withCount([
                    'materi' => function ($query) use ($kelasId) {
                        $query->whereHas('classes', function ($q) use ($kelasId) {
                            $q->where('classes.id', $kelasId);
                        });
                    },
                    'Task as unfinished_task_count' => function ($query) use ($user, $kelasId) {
                        $query->whereHas('collections', function ($subQuery) use ($user) {
                            $subQuery->where('user_id', $user->id)
                                ->where('status', 'Belum mengumpulkan');
                        })
                            ->whereHas('classes', function ($q) use ($kelasId) {
                                $q->where('classes.id', $kelasId);
                            });
                    }
                ])
                ->orderBy('name_subject')
                ->paginate(6);
        }

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
            return view('Siswa.tugas', [
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
            ->orderByRaw("
                CASE
                    WHEN collections.status = 'Belum mengumpulkan' THEN 1
                    WHEN collections.status = 'Sudah mengumpulkan' THEN 2
                    WHEN collections.status = 'Tidak mengumpulkan' THEN 3
                    ELSE 4
                END ASC
            ")
            ->orderBy('tasks.created_at', 'desc');

        if ($status) {
            if ($status === 'Belum mengumpulkan') {
                $tasksQuery->where(function ($query) {
                    $query->whereNull('collections.status')
                        ->orWhere('collections.status', 'Belum mengumpulkan');
                });
            } else {
                $tasksQuery->where('collections.status', $status);
            }
        }

        $tasks = $tasksQuery->paginate(5);

        // Update task status yang sudah lewat deadline
        $this->updateTaskStatus();

        return view('Siswa.tugas', compact('tasks'));
    }

    private function updateTaskStatus()
    {
        $now = now();

        // Update collections yang sudah melewati deadline
        Collection::whereHas('task', function ($query) use ($now) {
            $query->where('date_collection', '<', $now);
        })
            ->where('status', 'Belum mengumpulkan')
            ->update(['status' => 'Tidak mengumpulkan']);

        // Update tasks yang tidak memiliki collection tapi deadline sudah lewat
        Task::whereDoesntHave('collections', function ($query) {
            $query->where('user_id', Auth::id());
        })
            ->where('date_collection', '<', $now)
            ->get()
            ->each(function ($task) {
                Collection::create([
                    'task_id' => $task->id,
                    'user_id' => Auth::id(),
                    'status' => 'Tidak mengumpulkan',
                    'created_at' => now(),
                    'updated_at' => now()
                ]);
            });
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
        $student = $user->student;

        $class = $student?->class?->name_class;

        // Hitung tugas
        $countNotCollected = Collection::where('status', 'Belum mengumpulkan')
            ->where('user_id', $user->id)
            ->count();

        $countCollected = Collection::where('status', 'Sudah mengumpulkan')
            ->where('user_id', $user->id)
            ->count();

        // Hitung total tugas untuk progress
        $totalTasks = $countCollected + $countNotCollected;
        $progressPercentage = $totalTasks > 0 ? round(($countCollected / $totalTasks) * 100) : 0;

        // Ambil aktivitas terakhir dari berbagai sumber
        $recentActivities = $this->getRecentActivities($user);

        return view('Siswa.dashboard', compact(
            'class',
            'countNotCollected',
            'countCollected',
            'progressPercentage',
            'recentActivities'
        ));
    }

    private function getRecentActivities($user)
    {
        $activities = [];

        // 1. Tugas yang baru dikumpulkan (dari collections)
        $recentCollections = Collection::where('user_id', $user->id)
            ->with('task.subject')
            ->where('status', 'Sudah mengumpulkan')
            ->orderBy('updated_at', 'desc')
            ->limit(3)
            ->get();

        foreach ($recentCollections as $collection) {
            if ($collection->task) {
                $activities[] = [
                    'title' => 'Mengumpulkan Tugas',
                    'subtitle' => $collection->task->title_task,
                    'time' => $this->formatTimeAgo($collection->updated_at),
                    'timestamp' => $collection->updated_at,
                    'subject' => $collection->task->subject->name_subject ?? 'Umum',
                    'type' => 'task_submission',
                    'icon' => '📝'
                ];
            }
        }

        // 2. Ujian yang baru dikerjakan (dari exam_attempts)
        $recentExams = ExamAttempt::where('student_id', $user->id)
            ->with('exam.subject')
            ->where('status', 'submitted')
            ->orderBy('updated_at', 'desc')
            ->limit(3 - count($activities))
            ->get();

        foreach ($recentExams as $examAttempt) {
            if ($examAttempt->exam) {
                $score = $examAttempt->score ? " (Nilai: {$examAttempt->score})" : "";
                $activities[] = [
                    'title' => 'Menyelesaikan Ujian',
                    'subtitle' => $examAttempt->exam->title . $score,
                    'time' => $this->formatTimeAgo($examAttempt->updated_at),
                    'timestamp' => $examAttempt->updated_at,
                    'subject' => $examAttempt->exam->subject->name_subject ?? 'Umum',
                    'type' => 'exam_submission',
                    'icon' => '📊'
                ];
            }
        }

        // 3. Ujian baru yang tersedia (dari exams yang belum dikerjakan)
        if (count($activities) < 3) {
            $student = $user->student;
            if ($student && $student->class_id) {
                // Cari ujian yang baru dibuat dan belum dikerjakan
                $newExams = Exam::where('class_id', $student->class_id)
                    ->where('status', 'active')
                    ->where(function ($query) {
                        $query->whereNull('start_at')
                            ->orWhere('start_at', '<=', now());
                    })
                    ->where(function ($query) {
                        $query->whereNull('end_at')
                            ->orWhere('end_at', '>=', now());
                    })
                    ->whereDoesntHave('attempts', function ($query) use ($user) {
                        $query->where('student_id', $user->id)
                            ->where('status', 'submitted');
                    })
                    ->where('created_at', '>=', Carbon::now()->subDays(3)) // Hanya ambil yang dibuat 3 hari terakhir
                    ->orderBy('created_at', 'desc')
                    ->limit(3 - count($activities))
                    ->get();

                foreach ($newExams as $exam) {
                    $activities[] = [
                        'title' => 'Ujian Baru Tersedia',
                        'subtitle' => $exam->title . " ({$exam->questions()->count()} soal)",
                        'time' => $this->formatTimeAgo($exam->created_at),
                        'timestamp' => $exam->created_at,
                        'subject' => $exam->subject->name_subject ?? 'Umum',
                        'type' => 'new_exam',
                        'icon' => '📋'
                    ];
                }
            }
        }

        // 4. Ujian yang akan segera dimulai/berakhir
        if (count($activities) < 3) {
            $student = $user->student;
            if ($student && $student->class_id) {
                $upcomingExams = Exam::where('class_id', $student->class_id)
                    ->where('status', 'active')
                    ->where(function ($query) {
                        // Ujian yang akan dimulai dalam 24 jam
                        $query->whereBetween('start_at', [now(), now()->addHours(24)])
                            ->orWhere(function ($q) {
                                // Atau ujian yang akan berakhir dalam 24 jam
                                $q->whereNotNull('end_at')
                                    ->whereBetween('end_at', [now(), now()->addHours(24)]);
                            });
                    })
                    ->whereDoesntHave('attempts', function ($query) use ($user) {
                        $query->where('student_id', $user->id)
                            ->where('status', 'submitted');
                    })
                    ->orderBy('start_at', 'asc')
                    ->limit(3 - count($activities))
                    ->get();

                foreach ($upcomingExams as $exam) {
                    $timeLeft = '';

                    if ($exam->start_at && $exam->start_at > now()) {
                        $timeLeft = ' (Dimulai dalam ' . $this->formatTimeLeft($exam->start_at) . ')';
                    } elseif ($exam->end_at && $exam->end_at > now()) {
                        $timeLeft = ' (Berakhir dalam ' . $this->formatTimeLeft($exam->end_at) . ')';
                    }

                    $activities[] = [
                        'title' => 'Ujian ' . ($exam->start_at > now() ? 'Akan Dimulai' : 'Akan Berakhir'),
                        'subtitle' => $exam->title . $timeLeft,
                        'time' => $this->formatTimeAgo($exam->updated_at),
                        'timestamp' => $exam->updated_at,
                        'subject' => $exam->subject->name_subject ?? 'Umum',
                        'type' => 'exam_reminder',
                        'icon' => '⏰'
                    ];
                }
            }
        }

        // 5. Materi yang baru dibuka (ambil materi terbaru dari kelasnya)
        if (count($activities) < 3) {
            $student = $user->student;
            if ($student && $student->class_id) {
                $recentMateri = Materi::whereHas('classes', function ($q) use ($student) {
                    $q->where('classes.id', $student->class_id);
                })
                    ->where('created_at', '>=', Carbon::now()->subDays(7)) // Hanya ambil 7 hari terakhir
                    ->orderBy('created_at', 'desc')
                    ->limit(3 - count($activities))
                    ->get();

                foreach ($recentMateri as $materi) {
                    $activities[] = [
                        'title' => 'Materi Baru Tersedia',
                        'subtitle' => $materi->title_materi,
                        'time' => $this->formatTimeAgo($materi->created_at),
                        'timestamp' => $materi->created_at,
                        'subject' => $materi->subject->name_subject ?? 'Umum',
                        'type' => 'new_materi',
                        'icon' => '📚'
                    ];
                }
            }
        }

        // 6. Jika masih kurang dari 3, tambahkan aktivitas default
        if (count($activities) < 3) {
            $defaultActivities = [
                [
                    'title' => 'Bergabung di Kelas',
                    'subtitle' => 'Memulai pembelajaran online',
                    'time' => 'Awal semester',
                    'timestamp' => Carbon::now()->subDays(30),
                    'subject' => 'Sistem',
                    'type' => 'system',
                    'icon' => '👋'
                ],
                [
                    'title' => 'Menyelesaikan Kuis',
                    'subtitle' => 'Latihan soal pertama',
                    'time' => 'Minggu lalu',
                    'timestamp' => Carbon::now()->subDays(7),
                    'subject' => 'Latihan',
                    'type' => 'quiz',
                    'icon' => '✅'
                ],
                [
                    'title' => 'Membaca Materi',
                    'subtitle' => 'Pengenalan materi baru',
                    'time' => '2 hari yang lalu',
                    'timestamp' => Carbon::now()->subDays(2),
                    'subject' => 'Pembelajaran',
                    'type' => 'study',
                    'icon' => '📖'
                ]
            ];

            for ($i = count($activities); $i < 3; $i++) {
                $activities[] = $defaultActivities[$i] ?? $defaultActivities[0];
            }
        }

        // Urutkan berdasarkan timestamp (terbaru dulu)
        usort($activities, function ($a, $b) {
            return $b['timestamp'] <=> $a['timestamp'];
        });

        // Hapus field tambahan sebelum dikembalikan
        foreach ($activities as &$activity) {
            unset($activity['timestamp'], $activity['type'], $activity['icon']);
        }

        return array_slice($activities, 0, 3);
    }

    private function formatTimeAgo($timestamp)
    {
        if (!$timestamp) return 'Beberapa waktu lalu';

        $now = Carbon::now();

        $diffInSeconds = $now->diffInSeconds($timestamp);
        $diffInMinutes = $now->diffInMinutes($timestamp);
        $diffInHours   = $now->diffInHours($timestamp);
        $diffInDays    = $now->diffInDays($timestamp);

        // 0–59 detik
        if ($diffInSeconds < 60) {
            return 'Baru saja';
        }

        // 1–59 menit
        if ($diffInMinutes < 60) {
            return $diffInMinutes . ' menit yang lalu';
        }

        // 1–23 jam
        if ($diffInHours < 24) {
            return $diffInHours . ' jam yang lalu';
        }

        // 1–29 hari
        if ($diffInDays < 30) {
            return $diffInDays . ' hari yang lalu';
        }

        // Lebih dari 30 hari → tanggal lengkap
        return Carbon::parse($timestamp)
            ->locale('id')
            ->translatedFormat('d F Y');
    }

    private function formatTimeLeft($futureTime)
    {
        if (!$futureTime || $futureTime <= now()) {
            return '';
        }

        $totalMinutes = now()->diffInMinutes($futureTime);

        $hours = intdiv($totalMinutes, 60);
        $minutes = $totalMinutes % 60;

        if ($hours > 0 && $minutes > 0) {
            return "{$hours} jam {$minutes} menit";
        }

        if ($hours > 0) {
            return "{$hours} jam";
        }

        return "{$minutes} menit";
    }


    // Di UserPageController - perbaiki method showSoal()
    public function showSoal(Request $request)
    {
        try {
            $user = Auth::user();

            // Cek jika user adalah siswa
            if (!$user->hasRole('Murid')) {
                return back()->with('error', 'Hanya siswa yang dapat mengakses halaman ini.');
            }

            // Cek jika user memiliki data student
            if (!$user->student) {
                return view('Siswa.soal', [
                    'exams' => collect(),
                    'error' => 'Data siswa tidak ditemukan. Silakan hubungi administrator.'
                ]);
            }

            // Cek jika student memiliki class_id
            $student = $user->student;
            if (!$student->class_id) {
                return view('Siswa.soal', [
                    'exams' => collect(),
                    'error' => 'Anda belum memiliki kelas. Silakan tunggu hingga administrator menugaskan Anda ke kelas.'
                ]);
            }

            $kelasId = $student->class_id;
            $search = $request->input('search');
            $status = $request->input('status');

            // Query dasar dengan scope untuk exam yang aktif dan tersedia
            $query = Exam::with([
                'subject',
                'teacher.user',
                'questions' // Tambahkan untuk menghitung jumlah soal
            ])
                ->where('class_id', $kelasId)
                ->where('status', 'active') // Hanya exam yang aktif
                ->where(function ($query) {
                    $query->whereNull('end_at')
                        ->orWhere('end_at', '>=', now());
                });

            // Filter pencarian
            if ($search) {
                $query->where(function ($q) use ($search) {
                    $q->where('title', 'like', '%' . $search . '%')
                        ->orWhere('type', 'like', '%' . $search . '%')
                        ->orWhereHas('subject', function ($subjectQuery) use ($search) {
                            $subjectQuery->where('name_subject', 'like', '%' . $search . '%');
                        })
                        ->orWhereHas('teacher.user', function ($teacherQuery) use ($search) {
                            $teacherQuery->where('name', 'like', '%' . $search . '%');
                        });
                });
            }

            // Filter berdasarkan status
            if ($status) {
                switch ($status) {
                    case 'available':
                        // Belum dikerjakan dan tersedia
                        $query->where(function ($q) use ($user) {
                            $q->whereDoesntHave('attempts', function ($subQuery) use ($user) {
                                $subQuery->where('student_id', $user->id)
                                    ->whereIn('status', ['submitted', 'timeout']);
                            })
                                ->orWhereHas('attempts', function ($subQuery) use ($user) {
                                    $subQuery->where('student_id', $user->id)
                                        ->where('status', 'in_progress');
                                });
                        });
                        break;

                    case 'completed':
                        // Sudah dikerjakan
                        $query->whereHas('attempts', function ($q) use ($user) {
                            $q->where('student_id', $user->id)
                                ->whereIn('status', ['submitted', 'timeout']);
                        });
                        break;

                    case 'expired':
                        // Kadaluarsa
                        $query->where('end_at', '<', now());
                        break;

                    case 'upcoming':
                        // Akan datang
                        $query->where('start_at', '>', now());
                        break;
                }
            }

            $exams = $query->orderBy('created_at', 'desc')->paginate(12);

            // Tambahkan informasi status untuk setiap exam
            foreach ($exams as $exam) {
                $attempt = ExamAttempt::where('exam_id', $exam->id)
                    ->where('student_id', $user->id)
                    ->latest()
                    ->first();

                $exam->attempt = $attempt;
                $exam->questions_count = $exam->questions->count();
                $exam->status = $this->getExamStatus($exam, $attempt);

                // Hitung berapa kali sudah attempt
                $exam->attempt_count = ExamAttempt::where('exam_id', $exam->id)
                    ->where('student_id', $user->id)
                    ->whereIn('status', ['submitted', 'timeout'])
                    ->count();

                $exam->can_retake = $exam->limit_attempts > $exam->attempt_count;
            }

            return view('Siswa.soal', compact('exams'));
        } catch (\Exception $e) {
            \Log::error('Error in showSoal: ' . $e->getMessage() . ' at ' . $e->getFile() . ':' . $e->getLine());
            return back()->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    public function showSoalDetail($examId)
    {
        try {
            $user = Auth::user();
            $student = $user->student;

            if (!$student || !$student->class_id) {
                return back()->with('error', 'Anda belum memiliki kelas.');
            }

            $exam = Exam::with([
                'subject',
                'teacher.user',
                'questions' => function ($query) {
                    $query->with('choices');
                }
            ])
                ->where('id', $examId)
                ->where('class_id', $student->class_id)
                ->first();

            if (!$exam) {
                return redirect()->route('soal.index')
                    ->with('error', 'Ujian tidak ditemukan atau tidak tersedia untuk kelas Anda.');
            }

            // PERBAIKI: Gunakan isAccessibleForStudent() bukan cek manual
            if (!$exam->isAccessibleForStudent()) {
                $status = $exam->getTimeStatus();
                $message = 'Ujian tidak dapat diakses. ';

                if ($status === 'upcoming') {
                    $message .= 'Mulai: ' . $exam->start_at->format('d M Y H:i');
                } elseif ($status === 'finished') {
                    $message .= 'Berakhir: ' . $exam->end_at->format('d M Y H:i');
                } elseif ($status === 'inactive') {
                    $message .= 'Status: ' . $exam->status;
                }

                return redirect()->route('soal.index')
                    ->with('error', $message);
            }

            // Cek attempt - PERBAIKI: Gunakan status 'in_progress' bukan 'ongoing'
            $latestAttempt = ExamAttempt::where('exam_id', $examId)
                ->where('student_id', $user->id)
                ->orderBy('created_at', 'desc')
                ->first();

            $attemptCount = ExamAttempt::where('exam_id', $examId)
                ->where('student_id', $user->id)
                ->whereIn('status', ['submitted', 'timeout'])
                ->count();

            $exam->questions_count = $exam->questions->count();
            $canRetake = $exam->limit_attempts > $attemptCount;

            return view('Siswa.soal-detail', compact('exam', 'latestAttempt', 'attemptCount', 'canRetake'));
        } catch (\Exception $e) {
            Log::error('Error in showDetail: ' . $e->getMessage());
            return back()->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    private function getExamStatus($exam, $attempt)
    {
        if (!$exam) return 'not_available';

        // Cek jika sudah melewati batas waktu
        if ($exam->end_at && now() > $exam->end_at) {
            return 'expired';
        }

        // Cek jika belum dimulai
        if ($exam->start_at && now() < $exam->start_at) {
            return 'upcoming';
        }

        // Cek berdasarkan attempt
        if ($attempt) {
            if ($attempt->status === 'submitted' || $attempt->status === 'timeout') {
                return 'completed';
            } elseif ($attempt->status === 'in_progress') {
                return 'ongoing';
            }
        }

        // Default: tersedia untuk dikerjakan
        return 'available';
    }
}
