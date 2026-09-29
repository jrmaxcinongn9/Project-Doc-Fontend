<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\RegisterController;
use App\Http\Controllers\ForgotPasswordController;
use App\Http\Controllers\ResetPasswordController;
use App\Http\Controllers\AdminUserController;
use App\Http\Controllers\ClassroomController;
use App\Http\Controllers\DocumentController;
use App\Http\Controllers\TeacherClassroomController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\StudentController;

/*
|--------------------------------------------------------------------------
| LOGIN PAGES (UI)
|--------------------------------------------------------------------------
*/

Route::get('/', fn() => redirect('/login/student'));
Route::get('/login/student', fn() => view('auth.student-login'))->name('login');
Route::get('/login/teacher', fn() => view('auth.teacher-login'));
Route::get('/login/admin',   fn() => view('auth.admin-login'));


/*
|--------------------------------------------------------------------------
| AUTH ACTION
|--------------------------------------------------------------------------
*/

Route::post('/login',  [AuthController::class, 'login'])->name('login');
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
Route::get('/register', fn() => view('auth.register'));
Route::post('/register', [RegisterController::class, 'register'])->name('register');
Route::get('/forgot-password', fn() => view('auth.forgot-password'));
Route::post('/forgot-password/send',       [ForgotPasswordController::class, 'sendOtp']);
Route::post('/forgot-password/verify-otp', [ForgotPasswordController::class, 'verifyOtp']);
Route::get('/reset-password', fn() => view('auth.reset-password'));
Route::post('/reset-password', [ResetPasswordController::class, 'resetPassword'])
    ->name('reset.password');


/*
|--------------------------------------------------------------------------
| STUDENT ONLY
|--------------------------------------------------------------------------
*/
Route::group(['middleware' => function ($request, $next) {

    $role = session('user.role');

    // ไม่ login → ไป Student Login
    if (!$role) return redirect('/login/student');

    // USER หรือ ADMIN เข้าได้
    if (in_array($role, ['USER', 'ADMIN'])) {
        return $next($request);
    }

    abort(403);

}], function () {

Route::get('/student/dashboard', [StudentController::class, 'dashboard']);
Route::get('/student/profile', fn() => view('student.profile'));
Route::post('/student/assignment/submit', [StudentController::class, 'submitAssignment'])->name('student.assignment.submit');
Route::get('/student/file/download/{id}', [StudentController::class, 'downloadFile'])->name('student.file.download');
});
// โหลดไฟล์
Route::get('/student/files/download/{id}', [StudentController::class, 'downloadFile'])->name('student.assignment.download');

// ลบไฟล์ (เพิ่มอันนี้เข้าไป)
Route::delete('/student/files/delete/{id}', [StudentController::class, 'deleteFile'])->name('student.assignment.deleteFile');

/*
|--------------------------------------------------------------------------
| TEACHER ONLY
|--------------------------------------------------------------------------
*/
Route::group(['middleware' => function ($request, $next) {

    $role = session('user.role');

    if (!$role) return redirect('/login/teacher');

    if (in_array($role, ['TEACHER', 'ADMIN'])) {
        return $next($request);
    }

    abort(403);

}], function () {

    // 1. เปลี่ยนบรรทัดนี้ ให้วิ่งไปหา ClassroomController
    Route::get('/teacher/classroom', [TeacherClassroomController::class, 'index']);
    
    // 2. เพิ่ม Route สำหรับดูรายละเอียด (Show) และสร้าง (Store)
    Route::post('/teacher/classroom', [TeacherClassroomController::class, 'store']);
    Route::get('/teacher/classroom/{id}', [TeacherClassroomController::class, 'show']);

    // --- (Route อื่นๆ ของคุณคงไว้เหมือนเดิม) ---
    Route::get('/teacher/review', fn() => view('teacher.review-documents'));
    Route::get('/teacher/students', fn() => view('teacher.students'));
    Route::get('/teacher/detail', fn() => view('teacher.student-detail'));
    Route::get('/teacher/work', fn() => view('teacher.work-files'));
    // ดึงหน้าดูรายละเอียดงาน (Assignment View)

Route::post('/teacher/classroom/{id}/assignment',
    [TeacherClassroomController::class, 'storeAssignment']);
    // ตรงนี้คือหน้าฟอร์มสร้างห้องเรียนที่เราเพิ่งทำไป
Route::get('/teacher/create', fn() => view('teacher.classroom-create'));
    // Route สำหรับลบห้องเรียน (ใช้ {id} เพื่อรับค่า ID จาก URL)
Route::patch('/teacher/classroom/{id}', [TeacherClassroomController::class, 'update']);
Route::get('/teacher/classroom/{id}/delete', [TeacherClassroomController::class, 'destroy']);
    // Assignment
Route::put('/teacher/assignment/{id}', [TeacherClassroomController::class, 'updateAssignment']);
Route::delete('/teacher/assignment/{id}', [TeacherClassroomController::class, 'destroyAssignment']);
    
    Route::get('/teacher/profile', fn() => view('teacher.profile'));
    Route::get('/teacher/edit', fn() => view('teacher.edit'));

    // Route สำหรับอาจารย์ดูรายละเอียดงาน
Route::get('/teacher/assignment-view/{id}', [DocumentController::class, 'byAssignment']);

// Route สำหรับอาจารย์ดาวน์โหลดไฟล์ (ใช้ฟังก์ชันเดิมใน DocumentController ได้เลย)
Route::get('/teacher/download/{id}', [DocumentController::class, 'downloadFile']);
});
/*
|--------------------------------------------------------------------------
| ADMIN ONLY
|--------------------------------------------------------------------------
*/
Route::group(['middleware' => function ($request, $next) {

    // 🔥 FIX สำคัญ
    $role = data_get(session('user'), 'role');

    if (!$role) {
        return redirect('/login/admin')->withErrors('กรุณาเข้าสู่ระบบ');
    }

    if ($role === 'ADMIN') {
        return $next($request);
    }

    abort(403);

}], function () {

    Route::get('/admin/dashboard', [AdminUserController::class, 'index'])->name('admin.dashboard');
    Route::get('/admin/internship', fn() => view('admin.manage-internship'));

    // =========================
    // CLASSROOM
    // =========================
    Route::get('/admin/classroom', [ClassroomController::class, 'index'])->name('classroom.index');
    Route::post('/admin/classroom', [ClassroomController::class, 'store'])->name('classroom.store');
    Route::get('/admin/classroom/{id}', [ClassroomController::class, 'show'])->name('classroom.show');
    Route::patch('/admin/classroom/{id}', [ClassroomController::class, 'update'])->name('classroom.update');
    Route::delete('/admin/classroom/{id}', [ClassroomController::class, 'destroy'])->name('classroom.destroy');
    

    // =========================
    // ASSIGNMENT
    // =========================
    Route::post('/admin/classroom/assignment', [ClassroomController::class, 'storeAssignment'])->name('assignment.store');

    Route::get('/admin/assignment/{id}/edit', function ($id) {
        return view('admin.edit-assignment', ['id' => $id]);
    })->name('assignment.edit');

    // 🔥 แนะนำใช้ PATCH แทน PUT
    Route::put('/admin/assignment/{id}', [ClassroomController::class, 'updateAssignment'])
        ->name('assignment.update');

    Route::get('/admin/assignment/{id}', [ClassroomController::class, 'showAssignment'])
        ->name('assignment.show');

    Route::delete('/admin/assignment/{id}', [ClassroomController::class, 'destroyAssignment'])
        ->name('assignment.destroy');


    Route::get('/admin/assignment/{id}', [ClassroomController::class, 'showAssignment'])
    ->name('assignment.show');
    // =========================
    // USERS
    // =========================
    Route::get('/admin/users', [AdminUserController::class, 'index'])->name('users.index');
    Route::post('/admin/users', [AdminUserController::class, 'store'])->name('users.store');
    Route::patch('/admin/users/{id}', [AdminUserController::class, 'update'])->name('users.update');
    Route::delete('/admin/users/{id}', [AdminUserController::class, 'destroy'])->name('users.destroy');

    // =========================
    // OTHER
    // =========================
    Route::get('/admin/create', fn() => view('admin.create-assignment'));
    Route::get('/admin/profile', fn() => view('admin.profile'));
    Route::get('/admin/view', fn() => view('admin.view'));
    Route::get('/admin/work', fn() => view('admin.work-files'));

    // =========================
    // DOCUMENT 🔥
    // =========================
    Route::get('/admin/documents', [DocumentController::class, 'index'])
        ->name('documents.index');
     // 🟢 ของใหม่ (ตาม assignment)
Route::get('/admin/assignment/{id}/documents', [DocumentController::class, 'byAssignment'])
    ->name('admin.assignment.documents');
    Route::delete('/document/{id}', [DocumentController::class, 'destroy']);
});
Route::post('/admin/upload', [DocumentController::class, 'upload']);
Route::get('/admin/files/download/{id}', [DocumentController::class, 'downloadFile'])->name('file.download');
Route::get('/assignments/download/{id}', [DocumentController::class, 'downloadFile'])->name('assignments.download');