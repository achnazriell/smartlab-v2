<?php

use App\Http\Controllers\Guru\ExamController as GuruExamController;
use App\Http\Controllers\Guru\QuestionController as GuruQuestionController;
use App\Http\Controllers\Murid\ExamController as MuridExamController;
use App\Http\Controllers\Guru\ExamResultController;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\CariController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\TaskController;
use App\Http\Controllers\MateriController;
use App\Http\Controllers\SearchController;
use App\Http\Controllers\ClassesController;
use App\Http\Controllers\CommentController;
use App\Http\Controllers\StudentController;
use App\Http\Controllers\SubjectController;
use App\Http\Controllers\TeacherController;
use App\Http\Controllers\HomeguruController;
use App\Http\Controllers\AssessmentController;
use App\Http\Controllers\CollectionController;
use App\Http\Controllers\SelectClassController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\BerandaController;
use App\Http\Controllers\FeedbackController;
use App\Http\Controllers\Admin\FeedbackController as AdminFeedbackController;
use App\Http\Controllers\Guru\ClassController;
use App\Http\Controllers\LandingController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\UserPageController;
use Illuminate\Http\Request;

Auth::routes(['register' => false]);

Route::post('/logout', function (Request $request) {
    Auth::logout();

    $request->session()->invalidate();
    $request->session()->regenerateToken();

    return redirect('/Beranda');
})->name('logout');


// ==================== LANDING & AUTH ROUTES ====================
Route::get('/', function () {
    return redirect('/Beranda');
});

Route::get('/Beranda', [BerandaController::class, 'index']);
Route::get('/Fitur', [BerandaController::class, 'features']);
Route::get('/Tentang', [BerandaController::class, 'about']);
Route::get('/Kontak', [BerandaController::class, 'contact']);

// ==================== PROFILE ROUTES (UNTUK SEMUA USER) ====================
// Profile Routes
Route::prefix('profile')->name('profile.')->group(function () {
    Route::get('/', [ProfileController::class, 'index'])->name('index');
    Route::post('/update-photo', [ProfileController::class, 'updatePhoto'])->name('update-photo');
    Route::delete('/delete-photo', [ProfileController::class, 'deletePhoto'])->name('delete-photo');
});
Route::middleware(['auth'])->group(function () {
    // Feedback routes
    Route::get('/feedback', [FeedbackController::class, 'index'])->name('feedbacks.index');
    Route::get('/feedback/create', [FeedbackController::class, 'create'])->name('feedbacks.create');
    Route::post('/feedback', [FeedbackController::class, 'store'])->name('feedbacks.store');
    Route::delete('/feedback/{feedback}', [FeedbackController::class, 'destroy'])->name('feedbacks.destroy');
});

// ==================== ADMIN ROUTES ====================
Route::middleware(['auth', 'role:Admin'])->group(function () {
    // Dashboard
    Route::get('/admin/dashboard', [HomeController::class, 'index'])->name('home');

    // Resources
    Route::resource('subject', SubjectController::class);
    Route::resource('classes', ClassesController::class);
    Route::resource('comments', CommentController::class);
    Route::resource('teachers', TeacherController::class);
    Route::resource('students', StudentController::class);

    // Custom Routes
    Route::get('/search', [SearchController::class, 'index'])->name('search');
    Route::get('/Students', [StudentController::class, 'index'])->name('Students');
    Route::post('/teachers/import', [TeacherController::class, 'import'])->name('teachers.import');
    Route::post('/students/import', [StudentController::class, 'import'])->name('students.import');
    Route::get('/teachers/{teacher}/detail', [TeacherController::class, 'detail']);

    // Class Approval
    Route::post('/approve', [StudentController::class, 'store'])->name('class.approval.store');
    Route::put('/class-approvals/{id}/approve', [StudentController::class, 'approve'])->name('class-approvals.approve');
    Route::post('/class-approval/{id}/reject', [StudentController::class, 'reject'])->name('class.approval.reject');

    // Feedback routes
    Route::get('/admin/feedback', [AdminFeedbackController::class, 'index'])->name('feedback.index');
    Route::get('/admin/feedback/{feedback}', [AdminFeedbackController::class, 'show'])->name('feedback.show');
    Route::put('/admin/feedback/{feedback}/status', [AdminFeedbackController::class, 'updateStatus'])->name('feedback.update-status');
    Route::delete('/admin/feedback/{feedback}', [AdminFeedbackController::class, 'destroy'])->name('feedback.destroy');
    Route::post('/admin/feedback/mark-all-read', [AdminFeedbackController::class, 'markAllAsRead'])->name('feedback.mark-all-read');

    // Student Assignment
    Route::put('/student/{student}', [StudentController::class, 'assign'])->name('murid.assignMurid');
    Route::put('/update/{teacher}', [TeacherController::class, 'updateAssign'])->name('teacher.updateAssign');
});

// ==================== GURU ROUTES ====================
Route::middleware(['auth', 'role:Guru|Admin'])->group(function () {
    // Dashboard
    Route::get('/teacher/dashboard', [HomeguruController::class, 'index'])->name('homeguru');
    Route::get('/teacher/dashboard/class-details/{classId}', [HomeguruController::class, 'getClassDetails']);

    // Resources
    Route::resource('materis', MateriController::class);
    Route::resource('tasks', TaskController::class);
    Route::get('/cari', [CariController::class, 'index'])->name('cari');

    // Collections & Assessments
    Route::get('/assessment/{task}', [AssessmentController::class, 'index'])->name('assesments');
    Route::get('/collections/task/{task}', [CollectionController::class, 'byTask'])
        ->name('collections.byTask');
    Route::post('/assessments/store/{task}', [AssessmentController::class, 'store'])->name('assessments.store');

    // AJAX Routes
    Route::get('/guru/subjects/{subject}/materi', function ($subjectId) {
        return \App\Models\Materi::where('subject_id', $subjectId)
            ->where('user_id', auth()->id())
            ->get();
    });

    Route::get('/guru/subjects/{subject}/classes', function ($subjectId) {
        $teacher = auth()->user()->teacher;

        return $teacher
            ->teacherClasses()
            ->whereHas('subjects', function ($q) use ($subjectId) {
                $q->where('subjects.id', $subjectId);
            })
            ->with('classes')
            ->get()
            ->pluck('classes')
            ->unique('id')
            ->values();
    });

    Route::get('/guru/materi/{materi}/kelas', function ($materiId) {
        return \App\Models\Materi::where('id', $materiId)
            ->with('classes:id,name_class')
            ->firstOrFail()
            ->classes
            ->unique('id')
            ->values();
    });

    Route::get('/guru/kelas', [HomeguruController::class, 'kelasSaya'])
        ->name('guru.kelas');

    // Route untuk kelas
    Route::prefix('class')->name('class.')->group(function () {
        Route::get('/', [ClassController::class, 'index'])->name('index');
        Route::get('/{kelas}/students', [ClassController::class, 'showStudents'])->name('students');
        Route::get('/{kelas}/students/{siswa}', [ClassController::class, 'showStudentDetail'])->name('student-detail');
    });

    // ==================== EXAM ROUTES FOR TEACHER ====================
    Route::prefix('guru')->name('guru.')->group(function () {
        // Exams Resource
        Route::resource('exams', GuruExamController::class);

        Route::get('exams/{exam}/soal', [GuruExamController::class, 'soal'])
            ->name('exams.soal');
        Route::post('exams/{exam}/soal', [GuruExamController::class, 'storeQuestion'])
            ->name('exams.store-question');

        // Route untuk AJAX get classes by subject
        Route::get('exams/get-classes-by-subject/{subjectId}', [GuruExamController::class, 'getClassesBySubject'])
            ->name('exams.get-classes-by-subject');
        Route::put('exams/{exam}/update-status', [GuruExamController::class, 'updateStatus'])
            ->name('exams.update-status');

        // Questions Routes
        Route::prefix('exams/{exam}')->group(function () {
            Route::get('/questions/create', [GuruQuestionController::class, 'create'])
                ->name('exams.questions.create');
            Route::post('/questions', [GuruQuestionController::class, 'store'])
                ->name('exams.questions.store');
            Route::get('/questions/{question}', [GuruQuestionController::class, 'show'])
                ->name('exams.questions.show');
            Route::get('/questions/{question}/edit', [GuruQuestionController::class, 'edit'])
                ->name('exams.questions.edit');
            Route::put('/questions/{question}', [GuruQuestionController::class, 'update'])
                ->name('exams.questions.update');
            Route::delete('/questions/{question}', [GuruQuestionController::class, 'destroy'])
                ->name('exams.questions.destroy');
            Route::post('/finalize', [GuruExamController::class, 'finalize'])->name('exams.finalize');
        });

        // Additional Exam Routes
        Route::post('exams/{exam}/toggle-status', [GuruExamController::class, 'toggleStatus'])
            ->name('exams.toggle-status');
        Route::post('exams/{exam}/duplicate', [GuruExamController::class, 'duplicate'])
            ->name('exams.duplicate');
        Route::get('exams/{exam}/results', [GuruExamController::class, 'results'])
            ->name('exams.results');
        Route::get('exams/{exam}/results/export', [GuruExamController::class, 'exportResults'])
            ->name('exams.results.export');
        Route::get('exams/{exam}/preview', [GuruExamController::class, 'preview'])
            ->name('exams.preview');
        Route::post('exams/{exam}/publish', [GuruExamController::class, 'publish'])
            ->name('exams.publish');
        Route::post('exams/{exam}/unpublish', [GuruExamController::class, 'unpublish'])
            ->name('exams.unpublish');

        // Exam Results Management
        Route::prefix('exams/{exam}/results')->name('exams.results.')->group(function () {
            Route::get('/', [ExamResultController::class, 'index'])->name('index');
            Route::get('/{attempt}', [ExamResultController::class, 'show'])->name('show');
            Route::put('/{attempt}/score', [ExamResultController::class, 'updateScore'])->name('update-score');
            Route::post('/{attempt}/regrade', [ExamResultController::class, 'regrade'])->name('regrade');
            Route::post('/{attempt}/reset', [ExamResultController::class, 'resetAttempt'])->name('reset-attempt');
        });
    });
});

// ==================== MURID ROUTES ====================
Route::middleware('auth')->group(function () {
    // Dashboard & Subjects
    Route::get('/dashboard', [UserPageController::class, 'Dashboard'])->name('dashboard');
    Route::get('/mapel', [UserPageController::class, 'showSubject'])->name('mapel');

    // Materi
    Route::get('/materi/{materi_id}', [UserPageController::class, 'showMateriBySubject'])->name('Materi');
    Route::get('/semuamateri', [UserPageController::class, 'showAllMateri'])->name('semuamateri');
    Route::get('/siswa/materi/{id}', [UserPageController::class, 'showMateri'])->name('materi.show');

    // Tasks
    Route::get('/tugas', [UserPageController::class, 'showTask'])->name('Tugas');
    Route::put('/tasks/{task_id}/collection', [CollectionController::class, 'updateCollection'])
        ->name('updateCollection');

    // ==================== SOAL/KUIS ROUTES FOR STUDENT ====================
    Route::prefix('soal')->name('soal.')->group(function () {
        // Halaman daftar soal/kuis
        Route::get('/', [UserPageController::class, 'showSoal'])->name('index');

        // Halaman detail soal sebelum mulai
        Route::get('/{exam}/detail', [UserPageController::class, 'showSoalDetail'])->name('detail');

        // Halaman mengerjakan soal
        Route::get('/{exam}/kerjakan', [MuridExamController::class, 'attemptFromSession'])->name('kerjakan');

        // Submit jawaban
        Route::post('/{exam}/submit', [MuridExamController::class, 'submit'])->name('submit');
        // Tambahkan route khusus untuk violation submit
        Route::post('/exams/{exam}/violation-submit', [ExamController::class, 'handleViolationSubmit'])
            ->name('violation-submit');
        // Lihat hasil
        Route::get('/{exam}/hasil/{attempt}', [MuridExamController::class, 'result'])->name('hasil');
        Route::post('/{exam}/enable-fullscreen', [MuridExamController::class, 'enableFullscreen'])
            ->name('enable-fullscreen');

        // Route untuk memulai ujian
        Route::post('/{exam}/start', [MuridExamController::class, 'start'])->name('start');

        // ... route lainnya yang sudah ada ...
        Route::get('/exams', [MuridExamController::class, 'index'])->name('exams.index');
        Route::get('/exams/active', [MuridExamController::class, 'active'])->name('exams.active');
        Route::get('/exams/upcoming', [MuridExamController::class, 'upcoming'])->name('exams.upcoming');
        Route::get('/exams/completed', [MuridExamController::class, 'completed'])->name('exams.completed');
        Route::get('/exams/{exam}', [MuridExamController::class, 'show'])->name('exams.show');
        // Route untuk direct attempt
        Route::get('/soal/{id}/direct', [MuridExamController::class, 'directAttempt'])
            ->name('direct');
        // Exam Attempt Management
        Route::post('/exams/{exam}/start', [MuridExamController::class, 'start'])->name('exams.start');
        Route::post('/exams/{exam}/continue', [MuridExamController::class, 'continueAttempt'])
            ->name('exams.continue');

        // PERBAIKAN: Pastikan route submit ada
        Route::post('/exams/{exam}/submit', [MuridExamController::class, 'submit'])->name('exams.submit');

        Route::post('/exams/{exam}/save-answer', [MuridExamController::class, 'saveAnswer'])
            ->name('exams.save-answer');
        Route::post('/exams/{exam}/auto-save', [MuridExamController::class, 'autoSave'])
            ->name('exams.auto-save');
        Route::get('/exams/{exam}/attempt/{attempt}', [MuridExamController::class, 'attempt'])
            ->name('exams.attempt');
        Route::get('/exams/{exam}/review/{attempt}', [MuridExamController::class, 'review'])
            ->name('exams.review');

        // Exam Results
        Route::get('/exams/{exam}/result/{attempt}', [MuridExamController::class, 'result'])
            ->name('exams.result');
        Route::get('/exams/{exam}/answers/{attempt}', [MuridExamController::class, 'answers'])
            ->name('exams.answers');

        // Security & Monitoring
        Route::post('/exams/{exam}/heartbeat', [MuridExamController::class, 'heartbeat'])
            ->name('exams.heartbeat');
        Route::post('/exams/{exam}/violation', [MuridExamController::class, 'logViolation'])
            ->name('violation');
    });

    // Hapus route profile yang lama karena sudah dipindahkan ke luar
    // Route::get('/profile', [UserPageController::class, 'profile'])->name('profile');
    // Route::put('/profile/update', [UserPageController::class, 'updateProfile'])->name('profile.update');
});

// ==================== PUBLIC ROUTES ====================
Route::get('/pilihkelasmateri', function () {
    return view('Siswa.pilihkelasmateri');
})->name('pilihkelasmateri');
