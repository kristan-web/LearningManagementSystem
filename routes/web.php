<?php

use App\Http\Controllers\AssignmentCommentController;
use App\Http\Controllers\CalendarController;
use App\Http\Controllers\EnrollmentController;
use App\Http\Controllers\MaterialDownloadController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\PasswordChangeController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\StudentAssignmentController;
use App\Http\Controllers\StudentDashboardController;
use App\Http\Controllers\StudentMaterialController;
use App\Http\Controllers\StudentQuizController;
use App\Http\Controllers\StudentScheduleController;
use App\Http\Controllers\SubjectController;
use App\Http\Controllers\TeacherAssignmentController;
use App\Http\Controllers\TeacherDashboardController;
use App\Http\Controllers\TeacherMaterialController;
use App\Http\Controllers\TeacherQuizController;
use App\Http\Controllers\TeacherScheduleController;
use App\Http\Controllers\WebAuthController;
use Illuminate\Support\Facades\Route;

Route::view('/', 'landing')->name('landing');
Route::view('/login', 'auth.login')->name('login');
Route::post('/login', [WebAuthController::class, 'login'])->name('login.authenticate');
Route::view('/otp', 'components.otp.verify')->name('otp');
Route::view('/otp/reset', 'components.otp.reset')->name('otp.reset');
Route::view('/registrar/login', 'auth.staff-login', ['portal' => 'registrar'])->name('registrar.login');
Route::view('/cashier/login', 'auth.staff-login', ['portal' => 'cashier'])->name('cashier.login');

Route::middleware('auth')->group(function () {
	Route::get('/admin/', [WebAuthController::class, 'adminDashboard'])->name('admin.dashboard');
	Route::get('/admin/users', [WebAuthController::class, 'adminUsers'])->name('admin.users.index');
	Route::get('/admin/users/create', [WebAuthController::class, 'adminUsersCreate'])->name('admin.users.create');
	Route::post('/admin/users', [WebAuthController::class, 'adminUsersStore'])->name('admin.users.store');
	Route::get('/admin/users/{user}/edit', [WebAuthController::class, 'adminUsersEdit'])->name('admin.users.edit');
	Route::put('/admin/users/{user}', [WebAuthController::class, 'adminUsersUpdate'])->name('admin.users.update');
	Route::post('/admin/users/delete/{user}/otp', [WebAuthController::class, 'adminUsersDeleteOtp'])->middleware('throttle:5,1,delete-otp')->name('admin.users.delete.otp');
	Route::post('/admin/users/delete/{user}', [WebAuthController::class, 'adminUsersDelete'])->name('admin.users.delete');
	Route::get('/admin/users/restore/{user}', [WebAuthController::class, 'adminUsersRestore'])->name('admin.users.restore');
	Route::get('/admin/users/{user}', [WebAuthController::class, 'adminUsersShow'])->name('admin.users.show');
	Route::get('/admin/curriculum', [SubjectController::class, 'index'])->name('admin.curriculum.index');
	Route::post('/admin/curriculum/subjects', [SubjectController::class, 'store'])->name('admin.subjects.store');
	Route::put('/admin/curriculum/subjects/{subject}', [SubjectController::class, 'update'])->name('admin.subjects.update');
	Route::delete('/admin/curriculum/subjects/{subject}', [SubjectController::class, 'destroy'])->name('admin.subjects.destroy');
	Route::get('/admin/enrollment', [EnrollmentController::class, 'index'])->name('admin.enrollment.index');
	Route::post('/admin/enrollment', [EnrollmentController::class, 'store'])->name('admin.enrollment.store');
	Route::put('/admin/enrollment/{enrollment}', [EnrollmentController::class, 'update'])->name('admin.enrollment.update');
	Route::delete('/admin/enrollment/{enrollment}', [EnrollmentController::class, 'destroy'])->name('admin.enrollment.destroy');
	Route::get('/admin/reports', [ReportController::class, 'index'])->name('admin.reports.index');
	Route::post('/admin/notifications/read-all', [NotificationController::class, 'markAllRead'])->name('admin.notifications.read-all');
	Route::post('/admin/notifications/{notification}/read', [NotificationController::class, 'markRead'])->name('admin.notifications.read');
	Route::view('/admin/settings', 'admin.settings.index')->name('admin.settings');
	Route::view('/admin/documentation', 'admin.help.documentation')->name('admin.documentation');
	Route::view('/admin/support', 'admin.help.support')->name('admin.support');
	Route::post('/logout', [WebAuthController::class, 'logout'])->name('logout');

	Route::get('/password/change', [PasswordChangeController::class, 'edit'])->name('password.change');
	Route::put('/password/change', [PasswordChangeController::class, 'update'])->name('password.change.update');

	Route::middleware('password.changed')->group(function () {
		Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
		Route::put('/profile', [ProfileController::class, 'update'])->name('profile.update');

		Route::get('/teacher/', [TeacherDashboardController::class, 'index'])->name('teacher.dashboard');
		Route::get('/teacher/materials', [TeacherMaterialController::class, 'index'])->name('teacher.materials.index');
		Route::post('/teacher/materials', [TeacherMaterialController::class, 'store'])->name('teacher.materials.store');
		Route::put('/teacher/materials/{material}', [TeacherMaterialController::class, 'update'])->name('teacher.materials.update');
		Route::delete('/teacher/materials/{material}', [TeacherMaterialController::class, 'destroy'])->name('teacher.materials.destroy');

		Route::get('/teacher/assignments', [TeacherAssignmentController::class, 'index'])->name('teacher.assignments.index');
		Route::get('/teacher/assignments/create', [TeacherAssignmentController::class, 'create'])->name('teacher.assignments.create');
		Route::post('/teacher/assignments', [TeacherAssignmentController::class, 'store'])->name('teacher.assignments.store');
		Route::get('/teacher/assignments/{assignment}/edit', [TeacherAssignmentController::class, 'edit'])->name('teacher.assignments.edit');
		Route::put('/teacher/assignments/{assignment}', [TeacherAssignmentController::class, 'update'])->name('teacher.assignments.update');
		Route::delete('/teacher/assignments/{assignment}', [TeacherAssignmentController::class, 'destroy'])->name('teacher.assignments.destroy');
		Route::get('/teacher/assignments/{assignment}/submissions', [TeacherAssignmentController::class, 'submissions'])->name('teacher.assignments.submissions');
		Route::get('/teacher/assignments/{assignment}/view', [TeacherAssignmentController::class, 'show'])->name('teacher.assignments.show');
		Route::put('/teacher/submissions/{submission}', [TeacherAssignmentController::class, 'grade'])->name('teacher.submissions.grade');
		Route::get('/teacher/submissions/{submission}/download', [TeacherAssignmentController::class, 'download'])->name('teacher.submissions.download');

		Route::get('/teacher/quizzes', [TeacherQuizController::class, 'index'])->name('teacher.quizzes.index');
		Route::get('/teacher/quizzes/create', [TeacherQuizController::class, 'create'])->name('teacher.quizzes.create');
		Route::post('/teacher/quizzes', [TeacherQuizController::class, 'store'])->name('teacher.quizzes.store');
		Route::delete('/teacher/quizzes/{quiz}', [TeacherQuizController::class, 'destroy'])->name('teacher.quizzes.destroy');

		Route::get('/teacher/schedule', [TeacherScheduleController::class, 'index'])->name('teacher.schedule.index');

		Route::get('/student/', [StudentDashboardController::class, 'index'])->name('student.dashboard');
		Route::get('/student/materials', [StudentMaterialController::class, 'index'])->name('student.materials.index');
		Route::get('/student/materials/{subject}', [StudentMaterialController::class, 'show'])->name('student.materials.show');
		Route::get('/student/assignments', [StudentAssignmentController::class, 'index'])->name('student.assignments.index');
		Route::get('/student/assignments/{assignment}', [StudentAssignmentController::class, 'show'])->name('student.assignments.show');
		Route::post('/student/assignments/{assignment}/submit', [StudentAssignmentController::class, 'submit'])->name('student.assignments.submit');
		Route::get('/student/schedule', [StudentScheduleController::class, 'index'])->name('student.schedule.index');

		Route::post('/assignments/{assignment}/comments', [AssignmentCommentController::class, 'store'])->name('assignments.comments.store');
		Route::delete('/assignments/{assignment}/comments/{comment}', [AssignmentCommentController::class, 'destroy'])->name('assignments.comments.destroy');
		Route::get('/student/quizzes', [StudentQuizController::class, 'index'])->name('student.quizzes.index');
		Route::get('/student/quizzes/{quiz}/take', [StudentQuizController::class, 'take'])->name('student.quizzes.take');
		Route::post('/student/quizzes/{quiz}/attempts/{attempt}/submit', [StudentQuizController::class, 'submit'])->name('student.quizzes.submit');
		Route::get('/student/quizzes/{quiz}/attempts/{attempt}/results', [StudentQuizController::class, 'results'])->name('student.quizzes.results');
		Route::get('/materials/{material}/download', [MaterialDownloadController::class, 'show'])->name('materials.download');
		Route::get('/materials/{material}/preview', [MaterialDownloadController::class, 'preview'])->name('materials.preview');
		Route::get('/calendar', [CalendarController::class, 'index'])->name('calendar.index');
		Route::get('/calendar/events', [CalendarController::class, 'events'])->name('calendar.events');
		Route::post('/calendar/events', [CalendarController::class, 'store'])->name('calendar.events.store');
		Route::put('/calendar/events/{event}', [CalendarController::class, 'update'])->name('calendar.events.update');
		Route::delete('/calendar/events/{event}', [CalendarController::class, 'destroy'])->name('calendar.events.destroy');
		Route::view('/announcements', 'shared.announcements.index')->name('announcements.index');
	});
});