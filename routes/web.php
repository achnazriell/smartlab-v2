<?php

use App\Http\Controllers\ExamController;
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
use App\Http\Controllers\UserPageController;

Auth::routes(['register' => false]);

// Proses
// Route::post('register-murid', [RegisterController::class, 'registerMurid'])->name('register_murid');
// Route::post('register-guru', [RegisterController::class, 'registerGuru'])->name('register_guru');
Route::put('/update/{teacher}', [TeacherController::class, 'updateAssign'])->name('teacher.updateAssign');
Route::put('/student/{student}', [StudentController::class, 'assign'])->name('murid.assignMurid');
Route::post('/approve', [StudentController::class, 'store'])->name('class.approval.store');
Route::put('/class-approvals/{id}/approve', [StudentController::class, 'approve'])->name('class-approvals.approve');
Route::post('/class-approval/{id}/reject', [StudentController::class, 'reject'])->name('class.approval.reject');


// Landing
Route::get('/landing', [
    function () {
        return view('Users.landing');
    }
]);

Route::get('/', [
    function () {
        return view('Users.beranda');
    }
]);

// Route Admin
Route::middleware(['auth', 'role:Admin'])->group(function () {
    Route::get('/admin/dashboard', [HomeController::class, 'index'])->name('home');
    Route::resource('subject', SubjectController::class);
    Route::resource('classes', ClassesController::class);
    Route::resource('materis', MateriController::class);
    Route::resource('tasks', TaskController::class);
    Route::resource('comments', CommentController::class);
    Route::get('/search', [SearchController::class, 'index'])->name('search');
    Route::resource('teachers', TeacherController::class);
    Route::get('/Students', [StudentController::class, 'index'])->name('Students');
    Route::resource('students', StudentController::class);
    Route::post('/teachers/import', [TeacherController::class, 'import'])->name('teachers.import');
    Route::post('/students/import', [StudentController::class, 'import'])->name('students.import');
});

// Route Guru
Route::middleware(['auth', 'role:Guru|Admin'])->group(function () {
    Route::get('/teacher/dashboard', [HomeguruController::class, 'index'])->name('homeguru');
    Route::get('/teacher/dashboard/class-details/{classId}', [HomeguruController::class, 'getClassDetails']);
    Route::get('/cari', [CariController::class, 'index'])->name('cari');
    Route::resource('materis', MateriController::class);
    Route::resource('tasks', TaskController::class);
    Route::get('/assessment/{task}', [AssessmentController::class, 'index'])->name('assesments');
    Route::resource('collections', CollectionController::class);
    Route::post('/assessments/store/{task}', [AssessmentController::class, 'store'])->name('assessments.store');
    Route::resource('exams', ExamController::class);
});

// Route Murid
Route::middleware('auth')->group(function () {
    Route::get('/PilihKelas', [SelectClassController::class, 'index'])->name('SelectClass');
    Route::get('/dashboard', [UserPageController::class, 'Dashboard'])->name('dashboard');
    Route::get('/mapel', [UserPageController::class, 'showSubject'])->name('mapel');
    Route::get('/materi/{materi_id}', [UserPageController::class, 'showMateri'])->name('Materi');
    Route::get('/tugas', [UserPageController::class, 'showTask'])->name('Tugas');
    Route::put('/tasks/{task_id}/collection', [CollectionController::class, 'updateCollection'])->name('updateCollection');
});

// route::get('/historimateri', [HomeController::class, 'historimateri'])->name('historimateri');
route::get('/historimateri', function () {
    return view('Siswa.historimateri');
})->name('historimateri');

route::get('/pilihkelasmateri', function () {
    return view('Siswa.pilihkelasmateri');
})->name('pilihkelasmateri');
