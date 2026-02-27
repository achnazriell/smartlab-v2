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
use App\Http\Controllers\DepartmentController;
use App\Http\Controllers\AcademicYearController;

// Guru Controllers
use App\Http\Controllers\Guru\ExamController as GuruExamController;
use App\Http\Controllers\Guru\QuestionController as GuruQuestionController;
use App\Http\Controllers\Guru\ClassController as GuruClassController;
use App\Http\Controllers\Guru\ExamResultController;
use App\Http\Controllers\Guru\QuizController as GuruQuizController;
use App\Http\Controllers\Guru\ImportQuestionsController;

// Murid Controllers
use App\Http\Controllers\Murid\ExamController as MuridExamController;
use App\Http\Controllers\Murid\QuizController as MuridQuizController;

// Admin Controllers
use App\Http\Controllers\Admin\FeedbackController as AdminFeedbackController;

use App\Models\AcademicYear;
use App\Models\Materi;

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

    // Department Routes
    Route::resource('departments', DepartmentController::class);
    Route::get('departments/{id}/detail', [DepartmentController::class, 'detail'])->name('departments.detail');
    Route::get('departments/export', [DepartmentController::class, 'export'])->name('departments.export');

    // Academic Year Routes
    Route::resource('academic-years', AcademicYearController::class);
    Route::post('academic-years/{id}/set-active', [AcademicYearController::class, 'setActive'])->name('academic-years.set-active');

    // Teacher Management
    Route::post('teachers/import', [TeacherController::class, 'import'])->name('teachers.import');
    Route::get('teachers/export', [TeacherController::class, 'export'])->name('teachers.export');
    Route::get('teachers/{teacher}/detail', [TeacherController::class, 'detail'])->name('teachers.detail');
    Route::get('teachers/download-template', [TeacherController::class, 'downloadTemplate'])->name('teachers.download-template');
    Route::get('teachers/export-filtered', [TeacherController::class, 'exportFiltered'])->name('teachers.exportFiltered');
    Route::resource('teachers', TeacherController::class);

    // Student Management
    Route::get('/students/print-attendance', [StudentController::class, 'printAttendance'])->name('students.print-attendance');
    Route::post('students/import', [StudentController::class, 'import'])->name('students.import');
    Route::get('students/export', [StudentController::class, 'export'])->name('students.export');
    Route::get('students/{id}/detail', [StudentController::class, 'detail'])->name('students.detail');
    Route::post('students/{id}/update-class', [StudentController::class, 'updateClass'])->name('students.update-class');
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
                ->get(['id', 'title_materi']);
        });

        Route::get('/subjects/{subject}/classes', function ($subjectId) {
            $teacher = auth()->user()->teacher;
            if (!$teacher) {
                return response()->json([]);
            }

            $activeYear = AcademicYear::active()->first();
            $yearId = $activeYear?->id;

            $classes = $teacher->classesTaughtInAcademicYear($yearId)
                ->wherePivot('subject_id', $subjectId)
                ->get(['classes.id', 'classes.name_class']);

            return $classes;
        });

        Route::get('/materi/{materi}/kelas', function ($materiId) {
            return Materi::where('id', $materiId)
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

    // ==================== GURU EXAM ROUTES (Regular Exams) ====================
    Route::prefix('guru')->name('guru.')->group(function () {
        // Exams Resource
        Route::resource('exams', GuruExamController::class);

        // Exam Questions
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

        // Import Questions
        Route::post('exams/{exam}/import-questions', [ImportQuestionsController::class, 'import'])->name('exams.import');
        Route::get('exams/{exam}/import-template', [ImportQuestionsController::class, 'downloadTemplate'])->name('exams.import.template');

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

    // ==================== GURU QUIZ ROUTES (FIXED & COMPLETE) ====================
    Route::prefix('guru/quiz')->name('guru.quiz.')->group(function () {

        // ========== BASIC CRUD (No Parameters) ==========
        Route::get('/', [GuruQuizController::class, 'index'])->name('index');
        Route::get('/create', [GuruQuizController::class, 'create'])->name('create');
        Route::post('/', [GuruQuizController::class, 'store'])->name('store');

        // ========== FILTERS (No Parameters) ==========
        Route::get('/draft', [GuruQuizController::class, 'draftQuizzes'])->name('draft');
        Route::get('/active', [GuruQuizController::class, 'activeQuizzes'])->name('active');
        Route::get('/completed', [GuruQuizController::class, 'completedQuizzes'])->name('completed');

        // ========== BULK OPERATIONS (No Parameters) ==========
        Route::post('/bulk-delete', [GuruQuizController::class, 'bulkDelete'])->name('bulk-delete');
        Route::post('/bulk-publish', [GuruQuizController::class, 'bulkPublish'])->name('bulk-publish');

        // ========== QUIZ-SPECIFIC ROUTES (With {quiz} Parameter) ==========
        Route::prefix('{quiz}')->group(function () {

            // === BASIC OPERATIONS ===
            Route::get('/', [GuruQuizController::class, 'show'])->name('show');
            Route::get('/edit', [GuruQuizController::class, 'edit'])->name('edit');
            Route::put('/', [GuruQuizController::class, 'update'])->name('update');
            Route::delete('/', [GuruQuizController::class, 'destroy'])->name('destroy');

            // === QUESTION MANAGEMENT ===
            Route::get('/questions', [GuruQuizController::class, 'showQuestionCreator'])->name('questions');
            Route::get('/questions/list', [GuruQuizController::class, 'getQuestions'])->name('questions.list');
            Route::post('/questions', [GuruQuizController::class, 'storeQuestions'])->name('questions.store');
            Route::post('/question', [GuruQuizController::class, 'storeSingleQuestion'])->name('question.store');
            Route::put('/questions/{question}', [GuruQuizController::class, 'updateQuestion'])->name('questions.update');
            Route::delete('/questions/{question}', [GuruQuizController::class, 'deleteQuestion'])->name('questions.delete');
            Route::post('/questions/reorder', [GuruQuizController::class, 'reorderQuestions'])->name('questions.reorder');
            Route::post('/questions/import', [GuruQuizController::class, 'importQuestions'])->name('questions.import');
            Route::get('/import-preview/{sourceExamId}', [GuruQuizController::class, 'importPreview'])->name('import.preview');

            // === QUIZ OPERATIONS ===
            Route::get('/preview', [GuruQuizController::class, 'previewQuiz'])->name('preview');
            Route::post('/finalize', [GuruQuizController::class, 'finalizeQuiz'])->name('finalize');
            Route::post('/publish', [GuruQuizController::class, 'publishQuiz'])->name('publish');
            Route::post('/unpublish', [GuruQuizController::class, 'unpublishQuiz'])->name('unpublish');
            Route::post('/duplicate', [GuruQuizController::class, 'duplicateQuiz'])->name('duplicate');

            // === ROOM MANAGEMENT (CRITICAL - NEW ROUTES) ===
            Route::get('/room', [GuruQuizController::class, 'showRoom'])->name('room');
            Route::post('/room/open', [GuruQuizController::class, 'openRoom'])->name('room.open');
            Route::post('/room/close', [GuruQuizController::class, 'closeRoom'])->name('room.close');
            Route::post('/room/start', [GuruQuizController::class, 'startQuiz'])->name('room.start');
            Route::post('/room/stop', [GuruQuizController::class, 'stopQuiz'])->name('room.stop');
            Route::get('/room/status', [GuruQuizController::class, 'getRoomStatus'])->name('room.status');
            Route::get('/room/participants', [GuruQuizController::class, 'getRoomParticipants'])->name('room.participants');

            // === PARTICIPANT MANAGEMENT ===
            Route::post('/room/kick/{participant}', [GuruQuizController::class, 'kickParticipant'])->name('room.kick');
            Route::post('/room/mark-ready/{participant}', [GuruQuizController::class, 'markParticipantAsReady'])->name('room.mark-ready');
            Route::post('/room/participant/{participant}/rejoin', [GuruQuizController::class, 'rejoinParticipant'])->name('room.participant.rejoin');
            Route::post('/room/participant/{participant}/disqualify', [GuruQuizController::class, 'disqualifyParticipant'])->name('room.participant.disqualify');

            // === VIOLATION MANAGEMENT ===
            Route::get('/room/participant/{participant}/violations', [GuruQuizController::class, 'getViolationDetails'])->name('room.participant.violations');
            Route::post('/room/participant/{participant}/reset-violations', [GuruQuizController::class, 'resetViolations'])->name('room.participant.reset-violations');

            // === RESULTS & ANALYTICS ===
            Route::get('/results', [GuruQuizController::class, 'quizResults'])->name('results');
            Route::get('/results/export/{format?}', [GuruQuizController::class, 'exportResults'])->name('results.export');
            Route::get('/results/student/{studentId}', [GuruQuizController::class, 'studentResults'])->name('results.student');
            Route::get('/attempt/{attempt}/detail', [GuruQuizController::class, 'attemptDetail'])->name('attempt.detail');
            Route::get('/leaderboard', [GuruQuizController::class, 'quizLeaderboard'])->name('leaderboard');

            // === ALTERNATIVE ROUTES (Compatibility) ===
            Route::post('/open-room', [GuruQuizController::class, 'openRoom'])->name('open-room');
            Route::post('/close-room', [GuruQuizController::class, 'closeRoom'])->name('close-room');
            Route::post('/start-quiz', [GuruQuizController::class, 'startQuiz'])->name('start-quiz');
            Route::post('/stop-quiz', [GuruQuizController::class, 'stopQuiz'])->name('stop-quiz');
            Route::post('/start', [GuruQuizController::class, 'startQuiz'])->name('start');
            Route::post('/stop', [GuruQuizController::class, 'stopQuiz'])->name('stop');
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
    Route::get('/siswa/materi/{id}', [UserPageController::class, 'showMateri'])->name('materi.show');
    Route::get('/materi/{materi_id}', [UserPageController::class, 'showMateriBySubject'])->name('Materi');

    // Tasks
    Route::put('/tasks/{task_id}/collection', [CollectionController::class, 'updateCollection'])->name('updateCollection');

    // ==================== MURID REGULAR EXAM ROUTES ====================
    Route::prefix('soal')->name('soal.')->group(function () {
        Route::get('/', [MuridExamController::class, 'index'])->name('index');
        Route::get('/list', [UserPageController::class, 'showSoal'])->name('list');

        // Static routes FIRST (before wildcard)
        Route::get('/active', [MuridExamController::class, 'active'])->name('active');
        Route::get('/upcoming', [MuridExamController::class, 'upcoming'])->name('upcoming');
        Route::get('/completed', [MuridExamController::class, 'completed'])->name('completed');
        Route::get('/result/{attempt}', [MuridExamController::class, 'result'])->name('result');

        // Wildcard routes LAST
        Route::get('/{exam}', [MuridExamController::class, 'showDetail'])->name('detail');
        Route::post('/{exam}/start', [MuridExamController::class, 'start'])->name('start');
        Route::get('/{exam}/attempt', [MuridExamController::class, 'attempt'])->name('attempt');
        Route::post('/{exam}/answer/{question}', [MuridExamController::class, 'saveAnswer'])->name('answer.save');
        Route::post('/{exam}/submit', [MuridExamController::class, 'submit'])->name('submit');
        Route::post('/{exam}/violation', [MuridExamController::class, 'logViolation'])->name('violation.log');
        Route::post('/{exam}/force-submit-violation', [MuridExamController::class, 'forceSubmitViolation'])->name('force-submit-violation');
        Route::get('/{exam}/time-remaining', [MuridExamController::class, 'getTimeRemaining'])->name('time-remaining');
    });

    // ==================== MURID QUIZ ROUTES (FIXED & COMPLETE) ====================
    Route::prefix('quiz')->name('quiz.')->group(function () {

        // === QUIZ LISTING (No Parameters) ===
        Route::get('/', [MuridQuizController::class, 'index'])->name('index');

        // === QUIZ-SPECIFIC ROUTES (With {quiz} Parameter) ===
        Route::prefix('{quiz}')->group(function () {

            // === ROOM FEATURES (CRITICAL - NEW ROUTES) ===
            Route::get('/room', [MuridQuizController::class, 'joinQuizRoomPage'])->name('room');
            Route::post('/room/join', [MuridQuizController::class, 'joinQuizRoom'])->name('join-room');
            Route::get('/room/status', [MuridQuizController::class, 'getQuizRoomStatus'])->name('room.status');
            Route::post('/room/mark-ready', [MuridQuizController::class, 'markAsReady'])->name('room.mark-ready');

            // === PLAY QUIZ (CRITICAL - NEW ROUTES) ===
            Route::get('/play', [MuridQuizController::class, 'playQuiz'])->name('play');
            Route::post('/save-progress', [MuridQuizController::class, 'saveQuizProgress'])->name('save-progress');
            Route::post('/submit', [MuridQuizController::class, 'submitQuiz'])->name('submit');

            // === RESULTS ===
            Route::get('/result/{attempt}', [MuridQuizController::class, 'quizResult'])->name('result');
            Route::get('/leaderboard', [MuridQuizController::class, 'quizLeaderboard'])->name('leaderboard');
            Route::get('/leaderboard-top5', [MuridQuizController::class, 'leaderboardTop5'])->name('leaderboard-top5');

            // === SECURITY & FEATURES ===
            Route::post('/log-violation', [MuridQuizController::class, 'logViolation'])->name('log-violation');
            Route::post('/report-violation', [MuridQuizController::class, 'reportViolation'])->name('report-violation');
            Route::post('/check-proctoring', [MuridQuizController::class, 'checkProctoring'])->name('check-proctoring');
            Route::post('/powerup', [MuridQuizController::class, 'usePowerup'])->name('powerups');
            Route::post('/bonus', [MuridQuizController::class, 'claimBonus'])->name('claim-bonus');
        });
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
