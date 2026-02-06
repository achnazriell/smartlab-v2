<?php

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;

// ==================== CONTROLLER IMPORTS ====================
use App\Http\Controllers\BerandaController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\HomeguruController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\UserPageController;
use App\Http\Controllers\FeedbackController;
use App\Http\Controllers\SearchController;
use App\Http\Controllers\CariController;
use App\Http\Controllers\TaskController;
use App\Http\Controllers\MateriController;
use App\Http\Controllers\ClassesController;
use App\Http\Controllers\SubjectController;
use App\Http\Controllers\StudentController;
use App\Http\Controllers\TeacherController;
use App\Http\Controllers\AssessmentController;
use App\Http\Controllers\CollectionController;
use App\Http\Controllers\CommentController;

// Guru Controllers
use App\Http\Controllers\Guru\ExamController as GuruExamController;
use App\Http\Controllers\Guru\QuestionController as GuruQuestionController;
use App\Http\Controllers\Guru\ClassController as GuruClassController;
use App\Http\Controllers\Guru\ExamResultController;

// Murid Controllers
use App\Http\Controllers\Murid\ExamController as MuridExamController;

// Admin Controllers
use App\Http\Controllers\Admin\FeedbackController as AdminFeedbackController;

// ==================== AUTHENTICATION ROUTES ====================
Auth::routes(['register' => false]);

// Custom logout
Route::post('/logout', function (Request $request) {
    Auth::logout();
    $request->session()->invalidate();
    $request->session()->regenerateToken();
    return redirect('/Beranda');
})->name('logout');

// Health check
Route::get('/health', function () {
    return response('OK', 200);
});

// ==================== LANDING & PUBLIC ROUTES ====================
Route::get('/', function () {
    return redirect('/Beranda');
});

Route::controller(BerandaController::class)->group(function () {
    Route::get('/Beranda', 'index')->name('beranda');
    Route::get('/Fitur', 'features')->name('features');
    Route::get('/Tentang', 'about')->name('about');
    Route::get('/Kontak', 'contact')->name('contact');
});

// ==================== SHARED AUTHENTICATED ROUTES ====================
Route::middleware(['auth'])->group(function () {
    // Profile Routes
    Route::prefix('profile')->name('profile.')->group(function () {
        Route::get('/', [ProfileController::class, 'index'])->name('index');
        Route::post('/update-photo', [ProfileController::class, 'updatePhoto'])->name('update-photo');
        Route::delete('/delete-photo', [ProfileController::class, 'deletePhoto'])->name('delete-photo');
    });

    // Feedback Routes
    Route::prefix('feedback')->name('feedbacks.')->group(function () {
        Route::get('/', [FeedbackController::class, 'index'])->name('index');
        Route::get('/create', [FeedbackController::class, 'create'])->name('create');
        Route::post('/', [FeedbackController::class, 'store'])->name('store');
        Route::delete('/{feedback}', [FeedbackController::class, 'destroy'])->name('destroy');
    });

    // Search (Available for all authenticated users)
    Route::get('/search', [SearchController::class, 'index'])->name('search');
});

// ==================== ADMIN ROUTES ====================
// PERBAIKAN: Gunakan 'role:Admin' bukan 'role:Admin'
Route::middleware(['auth', 'role:Admin'])->group(function () {
    // Dashboard
    Route::get('/home', [HomeController::class, 'index'])->name('home');

    // Resources
    Route::resource('subject', SubjectController::class);
    Route::post('/subject/import', [SubjectController::class, 'import'])->name('subject.import');

    Route::resource('classes', ClassesController::class);
    Route::post('/classes/import', [ClassesController::class, 'import'])->name('classes.import');

    Route::resource('comments', CommentController::class);

    // Teacher Management
    Route::prefix('teachers')->name('teachers.')->group(function () {
        Route::get('/', [TeacherController::class, 'index'])->name('index');
        Route::post('/', [TeacherController::class, 'store'])->name('store');
        Route::put('/{id}', [TeacherController::class, 'update'])->name('update');
        Route::delete('/{id}', [TeacherController::class, 'destroy'])->name('destroy');
        Route::post('/import', [TeacherController::class, 'import'])->name('import');
        Route::get('/{teacher}/detail', [TeacherController::class, 'detail'])->name('detail');
        Route::get('/download-template', [TeacherController::class, 'downloadTemplate'])->name('download-template');
        Route::get('/export', [TeacherController::class, 'exportFiltered'])->name('export');
        Route::put('/update/{teacher}', [TeacherController::class, 'updateAssign'])->name('updateAssign');
    });

    // Student Management
    Route::resource('students', StudentController::class);
    Route::post('/students/import', [StudentController::class, 'import'])->name('students.import');
    Route::get('/students/export-template', [StudentController::class, 'exportTemplate'])->name('students.export-template');
    Route::get('/Students', [StudentController::class, 'index'])->name('Students');
    Route::put('/student/{student}', [StudentController::class, 'assign'])->name('murid.assignMurid');

    // Class Approval
    Route::post('/approve', [StudentController::class, 'store'])->name('class.approval.store');
    Route::put('/class-approvals/{id}/approve', [StudentController::class, 'approve'])->name('class-approvals.approve');
    Route::post('/class-approval/{id}/reject', [StudentController::class, 'reject'])->name('class.approval.reject');

    // Admin Feedback Management
    Route::prefix('admin/feedback')->name('feedback.')->group(function () {
        Route::get('/', [AdminFeedbackController::class, 'index'])->name('index');
        Route::get('/{feedback}', [AdminFeedbackController::class, 'show'])->name('show');
        Route::put('/{feedback}/status', [AdminFeedbackController::class, 'updateStatus'])->name('update-status');
        Route::delete('/{feedback}', [AdminFeedbackController::class, 'destroy'])->name('destroy');
        Route::post('/mark-all-read', [AdminFeedbackController::class, 'markAllAsRead'])->name('mark-all-read');
    });
});

// ==================== GURU ROUTES ====================
// PERBAIKAN: Pisahkan route Guru dan Admin jika ada konflik
Route::middleware(['auth', 'role:Guru'])->group(function () {
    // Dashboard Guru
    Route::get('/teacher/dashboard', [HomeguruController::class, 'index'])->name('homeguru');
    Route::get('/teacher/dashboard/class-details/{classId}', [HomeguruController::class, 'getClassDetails']);

    // Resources
    Route::resource('materis', MateriController::class);
    Route::resource('tasks', TaskController::class);

    // Collections & Assessments
    Route::get('/assessment/{task}', [AssessmentController::class, 'index'])->name('assesments');
    Route::get('/collections/task/{task}', [CollectionController::class, 'byTask'])->name('collections.byTask');
    Route::post('/assessments/store/{task}', [AssessmentController::class, 'store'])->name('assessments.store');

    // Search for Guru
    Route::get('/cari', [CariController::class, 'index'])->name('cari');

    // AJAX Helper Routes
    Route::prefix('guru')->group(function () {
        Route::get('/subjects/{subject}/materi', function ($subjectId) {
            return \App\Models\Materi::where('subject_id', $subjectId)
                ->where('user_id', auth()->id())
                ->get();
        });

        Route::get('/subjects/{subject}/classes', function ($subjectId) {
            $teacher = auth()->user()->teacher;
            return $teacher->teacherClasses()
                ->whereHas('subjects', function ($q) use ($subjectId) {
                    $q->where('subjects.id', $subjectId);
                })
                ->with('classes')
                ->get()
                ->pluck('classes')
                ->unique('id')
                ->values();
        });

        Route::get('/materi/{materi}/kelas', function ($materiId) {
            return \App\Models\Materi::where('id', $materiId)
                ->with('classes:id,name_class')
                ->firstOrFail()
                ->classes
                ->unique('id')
                ->values();
        });
    });

    // Kelas Routes
    Route::prefix('class')->name('class.')->group(function () {
        Route::get('/', [GuruClassController::class, 'index'])->name('index');
        Route::get('/{kelas}/students', [GuruClassController::class, 'showStudents'])->name('students');
        Route::get('/{kelas}/students/{siswa}', [GuruClassController::class, 'showStudentDetail'])->name('student-detail');
    });

    Route::get('/guru/kelas', [HomeguruController::class, 'kelasSaya'])->name('guru.kelas');

    // ==================== GURU EXAM ROUTES ====================
    Route::prefix('guru')->name('guru.')->group(function () {
        // Exams Resource
        Route::resource('exams', GuruExamController::class);

        // Exam Specific Routes
        Route::get('exams/{exam}/soal', [GuruExamController::class, 'soal'])->name('exams.soal');
        Route::post('exams/{exam}/soal', [GuruExamController::class, 'storeQuestion'])->name('exams.store-question');
        Route::get('exams/get-classes-by-subject/{subjectId}', [GuruExamController::class, 'getClassesBySubject'])->name('exams.get-classes-by-subject');
        Route::put('exams/{exam}/update-status', [GuruExamController::class, 'updateStatus'])->name('exams.update-status');
        Route::post('exams/{exam}/finalize', [GuruExamController::class, 'finalize'])->name('exams.finalize');
        Route::post('exams/{exam}/toggle-status', [GuruExamController::class, 'toggleStatus'])->name('exams.toggle-status');
        Route::post('exams/{exam}/duplicate', [GuruExamController::class, 'duplicate'])->name('exams.duplicate');
        Route::get('exams/{exam}/results', [GuruExamController::class, 'results'])->name('exams.results');
        Route::get('exams/{exam}/results/export', [GuruExamController::class, 'exportResults'])->name('exams.results.export');
        Route::get('exams/{exam}/preview', [GuruExamController::class, 'preview'])->name('exams.preview');
        Route::post('exams/{exam}/publish', [GuruExamController::class, 'publish'])->name('exams.publish');
        Route::post('exams/{exam}/unpublish', [GuruExamController::class, 'unpublish'])->name('exams.unpublish');
        Route::get('/exams/{id}/edit', [GuruExamController::class, 'edit'])->name('exams.edit');
        Route::put('/exams/{id}', [GuruExamController::class, 'update'])->name('exams.update');


        // Questions Routes
        Route::prefix('exams/{exam}')->group(function () {
            Route::get('/questions/create', [GuruQuestionController::class, 'create'])->name('exams.questions.create');
            Route::post('/questions', [GuruQuestionController::class, 'store'])->name('exams.questions.store');
            Route::get('/questions/{question}', [GuruQuestionController::class, 'show'])->name('exams.questions.show');
            Route::get('/questions/{question}/edit', [GuruQuestionController::class, 'edit'])->name('exams.questions.edit');
            Route::put('/questions/{question}', [GuruQuestionController::class, 'update'])->name('exams.questions.update');
            Route::delete('/questions/{question}', [GuruQuestionController::class, 'destroy'])->name('exams.questions.destroy');
        });

        // Exam Results Management
        Route::prefix('exams/{exam}/results')->name('exams.results.')->group(function () {
            Route::get('/', [ExamResultController::class, 'index'])->name('index');
            Route::get('/{attempt}', [ExamResultController::class, 'show'])->name('show');
            Route::get('/student/{student}', [ExamResultController::class, 'byStudent'])->name('by-student');
            Route::post('/{attempt}/score', [ExamResultController::class, 'updateScore'])->name('update-score');
            Route::post('/{attempt}/regrade', [ExamResultController::class, 'regrade'])->name('regrade');
            Route::post('/{attempt}/reset', [ExamResultController::class, 'resetAttempt'])->name('reset-attempt');
            Route::get('/export/{format?}', [ExamResultController::class, 'export'])->name('export');
            Route::get('/question-analysis', [ExamResultController::class, 'questionAnalysis'])->name('question-analysis');
        });
    });
});

// ==================== ADMIN ACCESS TO GURU ROUTES ====================
// Route khusus untuk Admin yang ingin mengakses fitur Guru
Route::middleware(['auth', 'role:Admin'])->group(function () {
    // Admin bisa mengakses dashboard guru dengan route yang berbeda
    Route::get('/admin/teacher/dashboard', [HomeguruController::class, 'index'])->name('admin.homeguru');

    // Admin bisa melihat materi
    Route::get('/admin/materis', [MateriController::class, 'index'])->name('admin.materis.index');

    // Tambahkan route lain yang perlu diakses Admin sebagai Guru
});

// ==================== MURID ROUTES ====================
Route::middleware(['auth', 'role:Murid'])->group(function () {
    // Dashboard & Main Pages
    Route::get('/student/dashboard', [UserPageController::class, 'Dashboard'])->name('dashboard');
    Route::get('/mapel', [UserPageController::class, 'showSubject'])->name('mapel');
    Route::get('/tugas', [UserPageController::class, 'showTask'])->name('Tugas');

    // Materi Routes
    Route::get('/semuamateri', [UserPageController::class, 'showAllMateri'])->name('semuamateri');
    Route::get('/materi/{materi_id}', [UserPageController::class, 'showMateriBySubject'])->name('Materi');
    Route::get('/siswa/materi/{id}', [UserPageController::class, 'showMateri'])->name('materi.show');

    // Tasks
    Route::put('/tasks/{task_id}/collection', [CollectionController::class, 'updateCollection'])->name('updateCollection');

    // ==================== MURID EXAM ROUTES ====================
    Route::prefix('soal')->name('soal.')->group(function () {
        // Exam Listing
        Route::get('/list', [UserPageController::class, 'showSoal'])->name('list');
        Route::get('/', [MuridExamController::class, 'indexSoal'])->name('index');

        // Exam Detail & Attempt
        Route::get('/{exam}/detail', [MuridExamController::class, 'showDetail'])->name('detail');
        Route::get('/{exam}/kerjakan', [MuridExamController::class, 'attemptFromSession'])->name('kerjakan');
        Route::post('/{exam}/start', [MuridExamController::class, 'start'])->name('start');
        Route::post('/{exam}/submit', [MuridExamController::class, 'submit'])->name('submit');
        Route::get('/{exam}/hasil/{attempt}', [MuridExamController::class, 'result'])->name('hasil');

        // Security & Violation
        Route::post('/{exam}/force-submit-violation', [MuridExamController::class, 'forceSubmitViolation'])->name('force-submit-violation');
        Route::post('/exams/{exam}/violation-submit', [MuridExamController::class, 'handleViolationSubmit'])->name('violation-submit');
        Route::post('/{exam}/enable-fullscreen', [MuridExamController::class, 'enableFullscreen'])->name('enable-fullscreen');
    });

    // Additional Exam Routes (Maintaining your structure)
    Route::prefix('exams')->name('exams.')->group(function () {
        Route::get('/', [MuridExamController::class, 'index'])->name('index');
        Route::get('/active', [MuridExamController::class, 'active'])->name('active');
        Route::get('/upcoming', [MuridExamController::class, 'upcoming'])->name('upcoming');
        Route::get('/completed', [MuridExamController::class, 'completed'])->name('completed');
        Route::get('/{exam}', [MuridExamController::class, 'show'])->name('show');
        Route::get('/soal/{id}/direct', [MuridExamController::class, 'directAttempt'])->name('direct');

        // Exam Attempt Management
        Route::post('/{exam}/continue', [MuridExamController::class, 'continueAttempt'])->name('continue');
        Route::post('/{exam}/save-answer', [MuridExamController::class, 'saveAnswer'])->name('save-answer');
        Route::post('/{exam}/auto-save', [MuridExamController::class, 'autoSave'])->name('auto-save');
        Route::get('/{exam}/attempt/{attempt}', [MuridExamController::class, 'attempt'])->name('attempt');
        Route::get('/{exam}/review/{attempt}', [MuridExamController::class, 'review'])->name('review');

        // Exam Results
        Route::get('/{exam}/result/{attempt}', [MuridExamController::class, 'result'])->name('result');
        Route::get('/{exam}/answers/{attempt}', [MuridExamController::class, 'answers'])->name('answers');

        // Security & Monitoring
        Route::post('/{exam}/heartbeat', [MuridExamController::class, 'heartbeat'])->name('heartbeat');
        Route::post('/{exam}/violation', [MuridExamController::class, 'logViolation'])->name('violation');
    });
});

// ==================== PUBLIC ROUTES ====================
Route::get('/pilihkelasmateri', function () {
    return view('Siswa.pilihkelasmateri');
})->name('pilihkelasmateri');

// ==================== FALLBACK FOR LEGACY ROUTES ====================
// These ensure backward compatibility
Route::middleware(['auth'])->group(function () {
    Route::get('/dashboard', function () {
        $user = auth()->user();
        if ($user->hasRole('Admin')) {
            return redirect()->route('home');
        } elseif ($user->hasRole('Guru')) {
            return redirect()->route('homeguru');
        } elseif ($user->hasRole('Murid')) {
            return redirect()->route('dashboard');
        }
        return redirect('/Beranda');
    });
});

// ==================== ERROR HANDLING ====================
Route::fallback(function () {
    if (Auth::check()) {
        $user = Auth::user();
        if ($user->hasRole('Admin')) {
            return redirect()->route('home');
        } elseif ($user->hasRole('Guru')) {
            return redirect()->route('homeguru');
        } elseif ($user->hasRole('Murid')) {
            return redirect()->route('dashboard');
        }
    }
    return redirect('/Beranda');
});
