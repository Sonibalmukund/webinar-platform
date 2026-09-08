<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\LocationController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\WebinarRegistrationController;
use App\Http\Controllers\WebinarController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\WebinarAttendanceController;
use App\Http\Controllers\NotificationController;

Route::get('/', fn () => response()->view('pages.shared.state',['state'=>'404'],404))->name('home');
Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'loginUser']);
    Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
    Route::post('/register', [AuthController::class, 'register']);
    Route::get('/forgot-password', [AuthController::class, 'showForgot'])->name('password.request');
    Route::post('/forgot-password', [AuthController::class, 'forgot']);
});
Route::post('/logout', [AuthController::class, 'logout'])->middleware('auth')->name('logout');
Route::get('/locations/states', [LocationController::class, 'states'])->name('locations.states');
Route::get('/locations/cities', [LocationController::class, 'cities'])->name('locations.cities');
Route::get('/webinars', [WebinarController::class, 'index'])->name('webinars.index');
Route::get('/webinars/{webinar:slug}', fn (\App\Models\Webinar $webinar) => redirect()->route('webinars.show', $webinar, 301));

Route::middleware(['auth', 'role:learner'])->group(function () {
Route::get('/dashboard', [DashboardController::class,'learner'])->name('dashboard');
Route::get('/{webinar:slug}/dashboard', [WebinarController::class,'dashboard'])->where('webinar','(?!(?:admin|sub-admin)/)[A-Za-z0-9-]+')->name('webinars.dashboard');
Route::post('/webinars/{webinar:slug}/chat', [WebinarController::class,'sendChat'])->name('webinars.chat.store');
Route::get('/webinars/{webinar:slug}/chat', [WebinarController::class,'chatMessages'])->name('webinars.chat.index');
Route::post('/webinars/{webinar:slug}/comments', [WebinarController::class,'storeComment'])->name('webinars.comments.store');
Route::post('/webinars/{webinar:slug}/feedback', [WebinarController::class,'storeFeedback'])->name('webinars.feedback.store');
Route::post('/webinars/{webinar:slug}/polls/{poll}/vote', [WebinarController::class,'vote'])->name('webinars.polls.vote');
Route::get('/webinars/{webinar:slug}/certificate', [WebinarController::class,'downloadCertificate'])->name('webinars.certificate.download');
Route::post('/webinars/{webinar:slug}/attendance/join', [WebinarAttendanceController::class,'join'])->name('webinars.attendance.join');
Route::post('/webinars/{webinar:slug}/attendance/heartbeat', [WebinarAttendanceController::class,'heartbeat'])->name('webinars.attendance.heartbeat');
Route::post('/webinars/{webinar:slug}/attendance/leave', [WebinarAttendanceController::class,'leave'])->name('webinars.attendance.leave');
Route::post('/webinars/{webinar:slug}/register', [WebinarRegistrationController::class, 'store'])->name('webinars.register');
Route::view('/live-webinar', 'pages.user.live')->name('webinars.live');

Route::get('/recordings', [DashboardController::class,'recordings'])->name('recordings.index');
Route::get('/certificates', [DashboardController::class,'certificates'])->name('certificates.index');
Route::get('/bookmarks', [DashboardController::class,'bookmarks'])->name('bookmarks.index');
Route::get('/my-webinars', [DashboardController::class,'myWebinars'])->name('webinars.mine');
Route::view('/past-webinars', 'pages.shared.resource', ['title' => 'Past Webinars', 'type' => 'webinars']);
Route::view('/upcoming-webinars', 'pages.shared.resource', ['title' => 'Upcoming Webinars', 'type' => 'webinars']);
Route::view('/feedback', 'pages.shared.resource', ['title' => 'Share Feedback', 'type' => 'form']);
Route::get('/notifications', [NotificationController::class,'index'])->name('notifications.index');
Route::patch('/notifications/{notification}/read', [NotificationController::class,'read'])->name('notifications.read');
Route::get('/profile', [ProfileController::class, 'show'])->name('profile.show');
Route::put('/profile', [ProfileController::class, 'update'])->name('profile.update');
});

Route::view('/ui/empty', 'pages.shared.state', ['state' => 'empty']);
Route::view('/ui/loading', 'pages.shared.state', ['state' => 'loading']);
Route::view('/403', 'pages.shared.state', ['state' => '403']);
Route::view('/500', 'pages.shared.state', ['state' => '500']);
Route::get('/{webinar:slug}', [WebinarController::class, 'show'])->name('webinars.show');
Route::fallback(fn () => response()->view('pages.shared.state', ['state' => '404'], 404));
