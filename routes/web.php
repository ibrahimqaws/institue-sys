<?php

use App\Http\Controllers\Admin\ExpenseController;
use App\Http\Controllers\Admin\FinancialReportController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\Admin\CourseController;
use App\Http\Controllers\Admin\ClassroomController;
use App\Http\Controllers\Admin\BatchController;
use App\Http\Controllers\Admin\PaymentController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Admin\DashboardController as AdminDashboard;
use App\Http\Controllers\Admin\EnrollmentController;
use App\Http\Controllers\Admin\PayrollController;

use App\Http\Controllers\Teacher\DashboardController;
use App\Http\Controllers\Teacher\AttendanceController;
use App\Http\Controllers\Teacher\GradeController;
use App\Http\Controllers\Teacher\MaterialController;
use App\Http\Controllers\Teacher\NoteController;

use App\Http\Controllers\Student\DashboardController as StudentDashboard;
use App\Http\Controllers\Student\CourseRegistrationController;
use App\Http\Controllers\Student\MaterialDownloadController;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;

Route::get('/', fn () => Auth::check() ? redirect()->route('dashboard') : redirect()->route('login'));

Route::get('/dashboard', function () {
    $user = Auth::user();

    return match ($user->role) {
        'admin' => redirect()->route('admin.dashboard'),
        'teacher' => redirect()->route('teacher.dashboard'),
        'student' => redirect()->route('student.dashboard'),
        default => abort(403, 'دور المستخدم غير معروف. يرجى مراجعة إدارة المعهد.'),
    };
})->middleware(['auth'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
});

Route::middleware(['auth', 'role:admin'])->prefix('admin')->name('admin.')->group(function () {

    Route::get('dashboard', [AdminDashboard::class, 'index'])->name('dashboard');

    Route::resource('courses', CourseController::class)->except(['create', 'edit', 'show']);
    Route::resource('classrooms', ClassroomController::class)->except(['create', 'edit', 'show']);
    Route::resource('batches', BatchController::class)->except(['create', 'edit', 'show']);

    Route::patch('users/{user}/toggle', [UserController::class, 'toggle'])->name('users.toggle');
    Route::resource('users', UserController::class)->except(['create', 'edit', 'show']);
    Route::resource('enrollments', EnrollmentController::class)->only(['index', 'store', 'update', 'destroy']);

    Route::get('payments/{payment}/receipt', [PaymentController::class, 'receipt'])->name('payments.receipt');
    Route::resource('payments', PaymentController::class)->only(['index', 'store', 'update', 'destroy']);

    Route::get('expenses/{expense}/voucher', [ExpenseController::class, 'voucher'])->name('expenses.voucher');
    Route::resource('expenses', ExpenseController::class)->only(['index', 'store', 'update', 'destroy']);

    Route::post('payrolls/generate', [PayrollController::class, 'generate'])->name('payrolls.generate');
    Route::post('payrolls/{payroll}/pay', [PayrollController::class, 'pay'])->name('payrolls.pay');
    Route::resource('payrolls', PayrollController::class)->only(['index', 'update', 'destroy']);

    Route::get('financial-report', [FinancialReportController::class, 'getReport'])->name('financial.report');

});

Route::middleware(['auth', 'role:teacher'])->prefix('teacher')->name('teacher.')->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    Route::get('attendance/batch/{batch}', [AttendanceController::class, 'showBatchStudents'])->name('attendance.batch');
    Route::post('attendance/store', [AttendanceController::class, 'store'])->name('attendance.store');

    Route::get('grades/batch/{batch}', [GradeController::class, 'showBatchStudents'])->name('grades.batch');
    Route::post('grades/store', [GradeController::class, 'store'])->name('grades.store');
    Route::put('grades/{grade}', [GradeController::class, 'update'])->name('grades.update');
    Route::delete('grades/{grade}', [GradeController::class, 'destroy'])->name('grades.destroy');

    Route::get('materials/batch/{batch}', [MaterialController::class, 'index'])->name('materials.batch');
    Route::post('materials/store', [MaterialController::class, 'store'])->name('materials.store');
    Route::post('materials/{material}', [MaterialController::class, 'update'])->name('materials.update');
    Route::delete('materials/{material}', [MaterialController::class, 'destroy'])->name('materials.destroy');

    Route::get('notes/batch/{batch}', [NoteController::class, 'index'])->name('notes.batch');
    Route::post('notes/store', [NoteController::class, 'store'])->name('notes.store');
    Route::put('notes/{note}', [NoteController::class, 'update'])->name('notes.update');
    Route::delete('notes/{note}', [NoteController::class, 'destroy'])->name('notes.destroy');
});

Route::middleware(['auth', 'role:student'])->prefix('student')->name('student.')->group(function () {
    Route::get('/dashboard', [StudentDashboard::class, 'index'])->name('dashboard');

    Route::get('available-courses', [CourseRegistrationController::class, 'availableCourses'])->name('courses.available');
    Route::post('register-course', [CourseRegistrationController::class, 'register'])->name('courses.register');

    Route::get('materials/{material}/download', MaterialDownloadController::class)->name('materials.download');

});

require __DIR__.'/auth.php';