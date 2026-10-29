<?php

use App\Http\Controllers\Admin\DashboardController as AdminDashboardController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\PublicRegistrationController;
use App\Http\Controllers\Teacher\AssistantController as TeacherAssistantController;
use App\Http\Controllers\Teacher\AttendanceController as TeacherAttendanceController;
use App\Http\Controllers\Teacher\ClassController as TeacherClassController;
use App\Http\Controllers\Teacher\DashboardController as TeacherDashboardController;
use App\Http\Controllers\Teacher\FeeController as TeacherFeeController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Public Routes
|--------------------------------------------------------------------------
*/
Route::get('/', function () {
    return view('welcome');
})->name('home');

Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login'])->name('login.post');
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

// Public Self-Registration & ID Pass
Route::get('/join/{registration_token}', [PublicRegistrationController::class, 'showJoinForm'])->name('public.join');
Route::post('/join/{registration_token}', [PublicRegistrationController::class, 'processJoin'])->name('public.join.process');
Route::get('/student/pass/{qr_token}', [PublicRegistrationController::class, 'showStudentPass'])->name('public.pass');
Route::get('/student/lookup', [PublicRegistrationController::class, 'lookup'])->name('public.lookup');

/*
|--------------------------------------------------------------------------
| Teacher & Assistant Isolated Workspace Routes
|--------------------------------------------------------------------------
*/
Route::middleware(['auth', 'role:teacher'])->prefix('teacher')->name('teacher.')->group(function () {
    Route::get('/dashboard', [TeacherDashboardController::class, 'index'])->name('dashboard');

    // Classes Management
    Route::get('/classes', [TeacherClassController::class, 'index'])->name('classes.index');
    Route::post('/classes', [TeacherClassController::class, 'store'])->name('classes.store');
    Route::get('/classes/{class}', [TeacherClassController::class, 'show'])->name('classes.show');
    Route::put('/classes/{class}', [TeacherClassController::class, 'update'])->name('classes.update');
    Route::post('/classes/{class}/toggle-registration', [TeacherClassController::class, 'toggleRegistration'])->name('classes.toggle-registration');
    Route::post('/classes/{class}/students/manual', [TeacherClassController::class, 'enrollStudentManual'])->name('classes.students.manual');
    Route::delete('/classes/{class}/students/{student}', [TeacherClassController::class, 'removeStudent'])->name('classes.students.remove');

    // Attendance, QR Scanner & Tute Tracking
    Route::get('/classes/{class}/attendance', [TeacherAttendanceController::class, 'show'])->name('attendance.show');
    Route::post('/classes/{class}/attendance/scan', [TeacherAttendanceController::class, 'scan'])->name('attendance.scan');
    Route::post('/classes/{class}/attendance/manual', [TeacherAttendanceController::class, 'manualMark'])->name('attendance.manual');
    Route::post('/classes/{class}/attendance/{attendance}/toggle-tute', [TeacherAttendanceController::class, 'toggleTute'])->name('attendance.toggle-tute');

    // Fee Management & Cash Collections
    Route::get('/fees', [TeacherFeeController::class, 'index'])->name('fees.index');
    Route::post('/fees/collect', [TeacherFeeController::class, 'collect'])->name('fees.collect');
    Route::get('/fees/receipt/{payment}', [TeacherFeeController::class, 'receipt'])->name('fees.receipt');
    Route::delete('/fees/{payment}', [TeacherFeeController::class, 'destroy'])->name('fees.destroy');

    // Teaching Assistants Management
    Route::get('/assistants', [TeacherAssistantController::class, 'index'])->name('assistants.index');
    Route::post('/assistants', [TeacherAssistantController::class, 'store'])->name('assistants.store');
    Route::post('/assistants/{assistant}/toggle-status', [TeacherAssistantController::class, 'toggleStatus'])->name('assistants.toggle-status');
    Route::delete('/assistants/{assistant}', [TeacherAssistantController::class, 'destroy'])->name('assistants.destroy');
});

/*
|--------------------------------------------------------------------------
| Admin Global Oversight Routes
|--------------------------------------------------------------------------
*/
Route::middleware(['auth', 'role:admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/dashboard', [AdminDashboardController::class, 'index'])->name('dashboard');
    Route::get('/teachers', [AdminDashboardController::class, 'teachers'])->name('teachers.index');
    Route::post('/teachers', [AdminDashboardController::class, 'storeTeacher'])->name('teachers.store');
    Route::post('/users/{user}/status', [AdminDashboardController::class, 'updateUserStatus'])->name('users.status');
    Route::delete('/users/{user}', [AdminDashboardController::class, 'destroyUser'])->name('users.destroy');
    Route::get('/payments', [AdminDashboardController::class, 'payments'])->name('payments.index');
    Route::get('/students', [AdminDashboardController::class, 'students'])->name('students.index');
});
