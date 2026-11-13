<?php

use App\Http\Controllers\Admin\ApplicationManagementController as AdminApplicationController;
use App\Http\Controllers\Admin\ClassController as AdminClassController;
use App\Http\Controllers\Admin\DashboardController as AdminDashboardController;
use App\Http\Controllers\Admin\SchoolSettingController as AdminSchoolSettingController;
use App\Http\Controllers\Admin\TeacherLeaveController as AdminTeacherLeaveController;
use App\Http\Controllers\ApplicationController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\PublicRegistrationController;
use App\Http\Controllers\SuperAdmin\PrincipalManagementController;
use App\Http\Controllers\Teacher\AssistantController as TeacherAssistantController;
use App\Http\Controllers\Teacher\AttendanceController as TeacherAttendanceController;
use App\Http\Controllers\Teacher\ClassController as TeacherClassController;
use App\Http\Controllers\Teacher\DashboardController as TeacherDashboardController;
use App\Http\Controllers\Teacher\FeeController as TeacherFeeController;
use App\Http\Controllers\Teacher\LeaveController as TeacherLeaveController;
use App\Http\Controllers\Teacher\UniformController as TeacherUniformController;
use App\Http\Controllers\UniversalAttendanceController;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Storage;

/*
|--------------------------------------------------------------------------
| Public Routes
|--------------------------------------------------------------------------
*/
Route::get('/', [HomeController::class, 'index'])->name('home');
Route::get('/about-us', [HomeController::class, 'about'])->name('public.about');

// Pre-School Online Admission Application (WhatsApp Shareable Form)
Route::get('/apply/status', [ApplicationController::class, 'status'])->name('public.apply.status');
Route::get('/apply/success/{application_number}', [ApplicationController::class, 'success'])->name('public.apply.success');
Route::get('/apply/{token?}', [ApplicationController::class, 'create'])->name('public.apply');
Route::post('/apply/{token?}', [ApplicationController::class, 'store'])->name('public.apply.store');

Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login'])->name('login.post');
Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
Route::post('/register', [AuthController::class, 'register'])->name('register.post');
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

// Public Self-Registration & ID Pass
Route::get('/join/{registration_token}', [PublicRegistrationController::class, 'showJoinForm'])->name('public.join');
Route::post('/join/{registration_token}', [PublicRegistrationController::class, 'processJoin'])->name('public.join.process');
Route::get('/student/pass/{qr_token}', [PublicRegistrationController::class, 'showStudentPass'])->name('public.pass');
Route::get('/teacher/pass/{qr_token}', [UniversalAttendanceController::class, 'showTeacherPass'])->name('public.teacher.pass');
Route::get('/student/lookup', [PublicRegistrationController::class, 'lookup'])->name('public.lookup');

// Central Universal QR Attendance Scanner (Single Kiosk for Students & Teachers)
Route::middleware(['auth'])->group(function () {
    Route::get('/attendance/scan', [UniversalAttendanceController::class, 'showScanner'])->name('universal.attendance.scan');
    Route::post('/attendance/scan', [UniversalAttendanceController::class, 'scan'])->name('universal.attendance.scan.post');
});

// Direct / Fallback route for uploaded student photos (ensures images always display even if server symlink is missing)
Route::get('/storage/students/{filename}', function (string $filename) {
    $path = 'students/'.$filename;
    if (Storage::disk('public')->exists($path)) {
        return Storage::disk('public')->response($path);
    }

    abort(404);
})->where('filename', '.*')->name('storage.students');

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
    Route::get('/classes/{class}/students/{student}/attendance', [TeacherClassController::class, 'studentAttendance'])->name('classes.students.attendance');

    // Attendance, QR Scanner & Tute Tracking
    Route::get('/classes/{class}/attendance', [TeacherAttendanceController::class, 'show'])->name('attendance.show');
    Route::post('/classes/{class}/attendance/scan', [TeacherAttendanceController::class, 'scan'])->name('attendance.scan');
    Route::post('/classes/{class}/attendance/manual', [TeacherAttendanceController::class, 'manualMark'])->name('attendance.manual');
    Route::post('/classes/{class}/attendance/mark-with-fee', [TeacherAttendanceController::class, 'markWithFee'])->name('attendance.mark-with-fee');
    Route::post('/classes/{class}/attendance/{attendance}/toggle-tute', [TeacherAttendanceController::class, 'toggleTute'])->name('attendance.toggle-tute');

    // Daily Attendance & Cash Collection Ledger Sheet
    Route::get('/daily-sheet', [TeacherAttendanceController::class, 'dailySheet'])->name('daily.sheet');

    // Fee Management & Cash Collections
    Route::get('/fees', [TeacherFeeController::class, 'index'])->name('fees.index');
    Route::post('/fees/collect', [TeacherFeeController::class, 'collect'])->name('fees.collect');
    Route::get('/fees/receipt/{payment}', [TeacherFeeController::class, 'receipt'])->name('fees.receipt');
    Route::delete('/fees/{payment}', [TeacherFeeController::class, 'destroy'])->name('fees.destroy');

    // Uniform Orders & Delivery Tracking (With Sinhala Messages)
    Route::get('/uniforms', [TeacherUniformController::class, 'index'])->name('uniforms.index');
    Route::post('/uniforms', [TeacherUniformController::class, 'store'])->name('uniforms.store');
    Route::post('/uniforms/{order}/pay', [TeacherUniformController::class, 'markPayment'])->name('uniforms.pay');
    Route::post('/uniforms/{order}/deliver', [TeacherUniformController::class, 'markDelivery'])->name('uniforms.deliver');
    Route::post('/uniforms/items', [TeacherUniformController::class, 'storeItem'])->name('uniforms.items.store');

    // Teaching Assistants Management
    Route::get('/assistants', [TeacherAssistantController::class, 'index'])->name('assistants.index');
    Route::post('/assistants', [TeacherAssistantController::class, 'store'])->name('assistants.store');
    Route::put('/assistants/{assistant}/classes', [TeacherAssistantController::class, 'updateClasses'])->name('assistants.update-classes');
    Route::post('/assistants/{assistant}/toggle-status', [TeacherAssistantController::class, 'toggleStatus'])->name('assistants.toggle-status');
    Route::delete('/assistants/{assistant}', [TeacherAssistantController::class, 'destroy'])->name('assistants.destroy');

    // Teacher Leave Management
    Route::get('/leaves', [TeacherLeaveController::class, 'index'])->name('leaves.index');
    Route::post('/leaves', [TeacherLeaveController::class, 'store'])->name('leaves.store');
});

/*
|--------------------------------------------------------------------------
| Admin Global Oversight Routes
|--------------------------------------------------------------------------
*/
Route::middleware(['auth', 'role:admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/dashboard', [AdminDashboardController::class, 'index'])->name('dashboard');

    // Preschool Classes Management (LKG, UKG, Nursery, etc.)
    Route::get('/classes', [AdminClassController::class, 'index'])->name('classes.index');
    Route::post('/classes', [AdminClassController::class, 'store'])->name('classes.store');
    Route::put('/classes/{class}', [AdminClassController::class, 'update'])->name('classes.update');
    Route::delete('/classes/{class}', [AdminClassController::class, 'destroy'])->name('classes.destroy');

    Route::get('/teachers', [AdminDashboardController::class, 'teachers'])->name('teachers.index');
    Route::post('/teachers', [AdminDashboardController::class, 'storeTeacher'])->name('teachers.store');
    Route::post('/users/{user}/status', [AdminDashboardController::class, 'updateUserStatus'])->name('users.status');
    Route::post('/users/{user}/approve', [AdminDashboardController::class, 'approveUser'])->name('users.approve');
    Route::delete('/users/{user}', [AdminDashboardController::class, 'destroyUser'])->name('users.destroy');

    // Faculty & Staff Leaves Review & Attendance
    Route::get('/leaves', [AdminTeacherLeaveController::class, 'index'])->name('leaves.index');
    Route::post('/leaves/{leave}/approve', [AdminTeacherLeaveController::class, 'approve'])->name('leaves.approve');
    Route::post('/leaves/{leave}/reject', [AdminTeacherLeaveController::class, 'reject'])->name('leaves.reject');

    Route::get('/payments', [AdminDashboardController::class, 'payments'])->name('payments.index');
    Route::get('/students', [AdminDashboardController::class, 'students'])->name('students.index');

    // Pre-School Settings & Online Admission Applications
    Route::get('/settings/school', [AdminSchoolSettingController::class, 'edit'])->name('settings.school');
    Route::put('/settings/school', [AdminSchoolSettingController::class, 'update'])->name('settings.school.update');

    // Dedicated Apply Online Management Tab & Links
    Route::get('/apply-online', [AdminApplicationController::class, 'onlineLink'])->name('applications.link');
    Route::post('/apply-online/regenerate', [AdminApplicationController::class, 'regenerateLink'])->name('applications.regenerate-link');
    Route::post('/apply-online/settings', [AdminApplicationController::class, 'updateLinkSettings'])->name('applications.update-link-settings');

    Route::get('/applications', [AdminApplicationController::class, 'index'])->name('applications.index');
    Route::get('/applications/{application}', [AdminApplicationController::class, 'show'])->name('applications.show');
    Route::post('/applications/{application}/status', [AdminApplicationController::class, 'updateStatus'])->name('applications.status');
    Route::post('/applications/{application}/enroll', [AdminApplicationController::class, 'enroll'])->name('applications.enroll');
});

/*
|--------------------------------------------------------------------------
| Super Admin Central Oversight Routes
|--------------------------------------------------------------------------
*/
Route::middleware(['auth', 'role:super_admin'])->prefix('super-admin')->name('super_admin.')->group(function () {
    Route::get('/principals', [PrincipalManagementController::class, 'index'])->name('principals.index');
    Route::post('/principals', [PrincipalManagementController::class, 'store'])->name('principals.store');
    Route::post('/principals/{user}/approve', [PrincipalManagementController::class, 'approve'])->name('principals.approve');
    Route::post('/principals/{user}/block', [PrincipalManagementController::class, 'block'])->name('principals.block');
    Route::post('/principals/{user}/activate', [PrincipalManagementController::class, 'activate'])->name('principals.activate');
    Route::delete('/principals/{user}', [PrincipalManagementController::class, 'destroy'])->name('principals.destroy');
});
