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

// [TAMBAHAN] Import controller baru
use App\Http\Controllers\DepartmentController;
use App\Http\Controllers\AcademicYearController;

// Guru Controllers
use App\Http\Controllers\Guru\ExamController as GuruExamController;
use App\Http\Controllers\Guru\QuestionController as GuruQuestionController;
use App\Http\Controllers\Guru\ClassController as GuruClassController;
use App\Http\Controllers\Guru\ExamResultController;
use App\Http\Controllers\Guru\QuizController as GuruQuizController;

// Murid Controllers
use App\Http\Controllers\Murid\ExamController as MuridExamController;
use App\Http\Controllers\Murid\QuizController as MuridQuizController;

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
});

// ==================== ADMIN ROUTES ====================
Route::middleware(['auth', 'role:Admin'])->prefix('admin')->group(function () {
    // Dashboard
    Route::get('/dashboard', [HomeController::class, 'index'])->name('home');

    // Resources
    Route::resource('subject', SubjectController::class);
    Route::post('/subject/import', [SubjectController::class, 'import'])->name('subject.import');

    Route::resource('classes', ClassesController::class);
    Route::post('/classes/import', [ClassesController::class, 'import'])->name('classes.import');

    // Department Routes (Jurusan)
    Route::resource('departments', DepartmentController::class);
    Route::get('departments/{id}/detail', [DepartmentController::class, 'detail'])->name('departments.detail');
    Route::get('departments/export', [DepartmentController::class, 'export'])->name('departments.export');

    // Academic Year Routes (Tahun Ajaran)
    Route::resource('academic-years', AcademicYearController::class);
    Route::post('academic-years/{id}/set-active', [AcademicYearController::class, 'setActive'])->name('academic-years.set-active');

    // Teacher Management
    // Custom routes BEFORE resource
    Route::post('teachers/import', [TeacherController::class, 'import'])->name('teachers.import');
    Route::get('teachers/export', [TeacherController::class, 'export'])->name('teachers.export');
    Route::get('teachers/{teacher}/detail', [TeacherController::class, 'detail'])->name('teachers.detail');
    Route::get('teachers/download-template', [TeacherController::class, 'downloadTemplate'])->name('teachers.download-template');
    // Jika ada route exportFiltered, pastikan namanya unik
    Route::get('teachers/export-filtered', [TeacherController::class, 'exportFiltered'])->name('teachers.exportFiltered');
    // Resource
    Route::resource('teachers', TeacherController::class);

    // Student Management
    // Custom routes BEFORE resource
    Route::get('/students/print-attendance', [StudentController::class, 'printAttendance'])->name('students.print-attendance');
    Route::post('students/import', [StudentController::class, 'import'])->name('students.import');
    Route::get('students/export', [StudentController::class, 'export'])->name('students.export');
    Route::get('students/{id}/detail', [StudentController::class, 'detail'])->name('students.detail');
    Route::post('students/{id}/update-class', [StudentController::class, 'updateClass'])->name('students.update-class');
    // Resource
    Route::resource('students', StudentController::class);

    // Admin Feedback Management
    Route::prefix('feedback')->name('feedback.')->group(function () {
        Route::get('/', [AdminFeedbackController::class, 'index'])->name('index');
        Route::get('/{feedback}', [AdminFeedbackController::class, 'show'])->name('show');
        Route::put('/{feedback}/status', [AdminFeedbackController::class, 'updateStatus'])->name('update-status');
        Route::delete('/{feedback}', [AdminFeedbackController::class, 'destroy'])->name('destroy');
        Route::post('/mark-all-read', [AdminFeedbackController::class, 'markAllAsRead'])->name('mark-all-read');
    });
});

// ==================== GURU ROUTES ====================
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
        // Exams Resource (untuk UH, UTS, UAS, LAINNYA)
        Route::resource('exams', GuruExamController::class);

        // Create Exam (umum untuk semua jenis)
        Route::get('/exams/create', [GuruExamController::class, 'create'])->name('exams.create');
        Route::post('/exams', [GuruExamController::class, 'store'])->name('exams.store');

        Route::prefix('exams/{exam}/questions')->name('exams.questions.')->group(function () {
            Route::post('/', [GuruExamController::class, 'storeQuestion'])->name('store');
            Route::get('/{question}', [GuruExamController::class, 'getQuestion'])->name('show');
            Route::put('/{question}', [GuruExamController::class, 'updateQuestion'])->name('update');
            Route::delete('/{question}', [GuruExamController::class, 'deleteQuestion'])->name('destroy');
        });

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

    // ==================== GURU QUIZ ROUTES ====================
    Route::prefix('guru/quiz')->name('guru.quiz.')->group(function () {
        // Quiz Management (tanpa parameter quiz)
        Route::get('/', [GuruQuizController::class, 'index'])->name('index');
        Route::get('/create', [GuruQuizController::class, 'create'])->name('create');
        Route::post('/', [GuruQuizController::class, 'store'])->name('store');
        Route::get('/{quiz}/edit', [GuruQuizController::class, 'edit'])->name('edit');
        Route::put('/{quiz}', [GuruQuizController::class, 'update'])->name('update');
        Route::delete('/{quiz}', [GuruQuizController::class, 'destroy'])->name('destroy');

        // Quiz Room Management (tanpa parameter quiz)
        Route::get('/{quiz}/room', [GuruQuizController::class, 'showRoom'])->name('room');
        Route::get('/{quiz}/room/participants', [GuruQuizController::class, 'getRoomParticipants'])->name('room.participants');
        Route::post('{quiz}/room/participant/{participant}/rejoin', [GuruQuizController::class, 'rejoinParticipant'])->name('room.participant.rejoin');

        // Violation Management
        Route::get('/{quiz}/room/participant/{participant}/violations', [GuruQuizController::class, 'getViolationDetails'])->name('room.participant.violations');
        Route::post('/{quiz}/room/participant/{participant}/reset-violations', [GuruQuizController::class, 'resetViolations'])->name('room.participant.reset-violations');
        Route::post('/{quiz}/room/participant/{participant}/disqualify', [GuruQuizController::class, 'disqualifyParticipant'])->name('room.participant.disqualify');

        // Room Control Routes (dengan parameter quiz)
        Route::post('/{quiz}/room/open', [GuruQuizController::class, 'openRoom'])->name('room.open');
        Route::post('/{quiz}/room/close', [GuruQuizController::class, 'closeRoom'])->name('room.close');
        Route::post('/{quiz}/room/start', [GuruQuizController::class, 'startQuiz'])->name('room.start');
        Route::get('/{quiz}/room/status', [GuruQuizController::class, 'getRoomStatus'])->name('room.status');
        Route::post('/{quiz}/room/stop', [GuruQuizController::class, 'stopQuiz'])->name('room.stop');
        Route::post('/{quiz}/room/kick/{participant}', [GuruQuizController::class, 'kickParticipant'])->name('room.kick');
        Route::post('/{quiz}/room/mark-ready/{participant}', [GuruQuizController::class, 'markParticipantAsReady'])->name('room.mark-ready');
        Route::get('/{quiz}/leaderboard', [GuruQuizController::class, 'quizLeaderboard'])->name('leaderboard');

        // Alternative routes (kompatibilitas)
        Route::post('/{quiz}/close-room', [GuruQuizController::class, 'closeRoom'])->name('close-room');
        Route::post('/{quiz}/start-quiz', [GuruQuizController::class, 'startQuiz'])->name('start-quiz');
        Route::post('/{quiz}/open-room', [GuruQuizController::class, 'openRoom'])->name('open-room');
        Route::post('/{quiz}/stop-quiz', [GuruQuizController::class, 'stopQuiz'])->name('stop-quiz');

        // Quiz Filters (tanpa parameter quiz)
        Route::get('/draft', [GuruQuizController::class, 'draftQuizzes'])->name('draft');
        Route::get('/active', [GuruQuizController::class, 'activeQuizzes'])->name('active');
        Route::get('/completed', [GuruQuizController::class, 'completedQuizzes'])->name('completed');

        // Bulk operations (tanpa parameter quiz)
        Route::post('/bulk-delete', [GuruQuizController::class, 'bulkDelete'])->name('bulk-delete');
        Route::post('/bulk-publish', [GuruQuizController::class, 'bulkPublish'])->name('bulk-publish');

        // ========== SEMUA ROUTE YANG MEMERLUKAN PARAMETER {quiz} DILETAKKAN DI SINI ==========
        Route::prefix('{quiz}')->group(function () {
            // ✅ TAMBAH SOAL SINGLE (PG) – ROUTE YANG DIPERBAIKI
            Route::post('/question', [GuruQuizController::class, 'storeSingleQuestion'])->name('question.store');

            // Question Management
            Route::get('/questions', [GuruQuizController::class, 'showQuestionCreator'])->name('questions');
            Route::get('/questions/list', [GuruQuizController::class, 'getQuestions'])->name('questions.list');
            Route::post('/questions/store', [GuruQuizController::class, 'storeQuestions'])->name('questions.store');
            Route::post('/questions/import', [GuruQuizController::class, 'importQuestions'])->name('questions.import');

            // ✅ UPDATE SOAL – perbaiki method menjadi PUT & hapus /update
            Route::put('/questions/{question}', [GuruQuizController::class, 'updateQuestion'])->name('questions.update');

            // DELETE SOAL
            Route::delete('/questions/{question}', [GuruQuizController::class, 'deleteQuestion'])->name('questions.delete');

            // Reorder Questions
            Route::post('/questions/reorder', [GuruQuizController::class, 'reorderQuestions'])->name('questions.reorder');

            // Import Preview
            Route::get('/import-preview/{sourceExamId}', [GuruQuizController::class, 'importPreview'])->name('import.preview');

            // Quiz Operations
            Route::get('/preview', [GuruQuizController::class, 'previewQuiz'])->name('preview');
            Route::post('/finalize', [GuruQuizController::class, 'finalizeQuiz'])->name('finalize');
            Route::post('/publish', [GuruQuizController::class, 'publishQuiz'])->name('publish');
            Route::post('/unpublish', [GuruQuizController::class, 'unpublishQuiz'])->name('unpublish');
            Route::post('/duplicate', [GuruQuizController::class, 'duplicateQuiz'])->name('duplicate');

            // Quiz Results
            Route::get('/results', [GuruQuizController::class, 'quizResults'])->name('results');
            Route::get('/results/export/{format?}', [GuruQuizController::class, 'exportResults'])->name('results.export');
            Route::get('/results/student/{studentId}', [GuruQuizController::class, 'studentResults'])->name('results.student');
            // Route untuk detail attempt
            Route::get('/attempt/{attempt}/detail', [GuruQuizController::class, 'attemptDetail'])
                ->name('attempt.detail');
        });
    });
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

    // ==================== MURID REGULAR EXAM ROUTES ====================
    Route::prefix('soal')->name('soal.')->group(function () {
        // Exam Listing
        Route::get('/', [MuridExamController::class, 'index'])->name('index');
        Route::get('/list', [UserPageController::class, 'showSoal'])->name('list');
        Route::post('/{exam}/start', [MuridExamController::class, 'start'])->name('start');

        // Exam Detail & Attempt
        Route::get('/{exam}/detail', [MuridExamController::class, 'showDetail'])->name('detail');
        Route::get('/{exam}/kerjakan', [MuridExamController::class, 'attempt'])->name('kerjakan');
        Route::post('/{exam}/submit', [MuridExamController::class, 'submit'])->name('submit');
        Route::get('/{exam}/hasil/{attempt}', [MuridExamController::class, 'result'])->name('hasil');

        // Route untuk force submit karena pelanggaran
        Route::post('/{exam}/force-submit-violation', [MuridExamController::class, 'forceSubmitViolation'])->name('force-submit-violation');
    });

    // ==================== MURID QUIZ ROUTES ====================
    Route::prefix('quiz')->name('quiz.')->group(function () {
        // Quiz Listing
        Route::get('/', [MuridQuizController::class, 'index'])->name('index');
        Route::get('/active', [MuridQuizController::class, 'activeQuiz'])->name('active');
        Route::get('/upcoming', [MuridQuizController::class, 'upcomingQuiz'])->name('upcoming');
        Route::get('/completed', [MuridQuizController::class, 'completedQuiz'])->name('completed');

        // Quiz Room Management
        Route::get('/{quiz}/room', [MuridQuizController::class, 'joinQuizRoomPage'])->name('room');
        Route::post('/{quiz}/room/join', [MuridQuizController::class, 'joinQuizRoom'])->name('join-room');
        Route::get('/{quiz}/room/status', [MuridQuizController::class, 'getQuizRoomStatus'])->name('room.status');
        Route::post('/{quiz}/room/mark-ready', [MuridQuizController::class, 'markAsReady'])->name('room.mark-ready');

        // Quiz Violation Management
        Route::post('/{quiz}/log-violation', [MuridQuizController::class, 'logViolation'])->name('log-violation');
        // ✅ ROUTE BARU – report violation ke guru room + auto submit
        Route::post('/{quiz}/report-violation', [MuridQuizController::class, 'reportViolation'])->name('report-violation');
        Route::post('/{quiz}/check-proctoring', [MuridQuizController::class, 'checkProctoring'])->name('check-proctoring');
        Route::get('/{quiz}/leaderboard-top5', [MuridQuizController::class, 'leaderboardTop5'])->name('leaderboard-top5');
        Route::post('/{quiz}/powerup', [MuridQuizController::class, 'usePowerup'])->name('powerups');

        // Quiz Attempt
        Route::get('/{quiz}/play', [MuridQuizController::class, 'playQuiz'])->name('play');
        Route::post('/{quiz}/submit', [MuridQuizController::class, 'submitQuiz'])->name('submit');
        Route::post('/{quiz}/save-progress', [MuridQuizController::class, 'saveQuizProgress'])->name('save-progress');

        // Quiz Results
        Route::get('/{quiz}/result/{attempt}', [MuridQuizController::class, 'quizResult'])->name('result');
        Route::get('/{quiz}/leaderboard', [MuridQuizController::class, 'quizLeaderboard'])->name('leaderboard');

        // Quiz Features
        Route::post('/{quiz}/bonus', [MuridQuizController::class, 'claimBonus'])->name('claim-bonus');
        Route::post('/{quiz}/save-progress', [MuridQuizController::class, 'saveQuizProgress'])->name('save-progress');
    });
});


// ==================== PUBLIC ROUTES ====================
Route::get('/pilihkelasmateri', function () {
    return view('Siswa.pilihkelasmateri');
})->name('pilihkelasmateri');

// ==================== FALLBACK FOR LEGACY ROUTES ====================
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
