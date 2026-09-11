<?php

use App\Http\Controllers\AdminController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\MentorController;
use App\Http\Controllers\ParticipantController;
use App\Http\Controllers\PublicController;
use Illuminate\Support\Facades\Route;

Route::get('/', [PublicController::class, 'home'])->name('home');
Route::get('/departments', [PublicController::class, 'departments'])->name('departments.index');
Route::get('/departments/{department:slug}', [PublicController::class, 'department'])->name('departments.show');
Route::get('/business-units/{businessUnit}', [PublicController::class, 'unit'])->name('units.show');
Route::get('/berita', [PublicController::class, 'news'])->name('news.index');
Route::get('/berita/{news:slug}', [PublicController::class, 'newsShow'])->name('news.show');
Route::get('/profil', [PublicController::class, 'profile'])->middleware('auth')->name('profile.public');
Route::get('/program-info', [PublicController::class, 'programInfo'])->name('program.info');

Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::get('/login/user', [AuthController::class, 'showUserLogin'])->name('login.user');
Route::post('/login/user', [AuthController::class, 'loginUser']);
Route::get('/login/mentor', [AuthController::class, 'showMentorLogin'])->name('login.mentor');
Route::post('/login/mentor', [AuthController::class, 'loginMentor']);
Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
Route::get('/register/user', [AuthController::class, 'showUserRegister'])->name('register.user');
Route::post('/register/user', [AuthController::class, 'registerUser']);
Route::get('/register/mentor', [AuthController::class, 'showMentorRegister'])->name('register.mentor');
Route::post('/register/mentor', [AuthController::class, 'registerMentor']);

Route::middleware('guest')->group(function () {
    Route::get('/forgot-password', [AuthController::class, 'showForgot'])->name('password.request');
    Route::post('/forgot-password', [AuthController::class, 'sendReset'])->name('password.email');
    Route::get('/reset-password/{token}', [AuthController::class, 'showReset'])->name('password.reset');
    Route::post('/reset-password', [AuthController::class, 'reset'])->name('password.update');
});

Route::post('/logout', [AuthController::class, 'logout'])->middleware('auth')->name('logout');

Route::middleware(['auth', 'role:participant'])->prefix('participant')->name('participant.')->group(function () {
    Route::get('/dashboard', [ParticipantController::class, 'dashboard'])->name('dashboard');
    Route::get('/profile', [ParticipantController::class, 'profile'])->name('profile');
    Route::post('/profile', [ParticipantController::class, 'updateProfile']);
    Route::get('/applications', [ParticipantController::class, 'applications'])->name('applications');
    Route::get('/applications/create', [ParticipantController::class, 'createApplication'])->name('applications.create');
    Route::post('/applications', [ParticipantController::class, 'storeApplication'])->name('applications.store');
    Route::get('/applications/{application}', [ParticipantController::class, 'showApplication'])->name('applications.show');
    Route::get('/applications/{application}/edit', [ParticipantController::class, 'editApplication'])->name('applications.edit');
    Route::put('/applications/{application}', [ParticipantController::class, 'updateApplication'])->name('applications.update');
    Route::get('/program', [ParticipantController::class, 'program'])->name('program');
    Route::get('/agreement', [ParticipantController::class, 'agreement'])->name('agreement');
    Route::post('/agreement', [ParticipantController::class, 'updateAgreement']);
    Route::get('/timeline', [ParticipantController::class, 'timeline'])->name('timeline');
    Route::get('/logbooks', [ParticipantController::class, 'logbooks'])->name('logbooks');
    Route::post('/logbooks', [ParticipantController::class, 'storeLogbook']);
    Route::get('/mentoring', [ParticipantController::class, 'mentoring'])->name('mentoring');
    Route::get('/outputs', [ParticipantController::class, 'outputs'])->name('outputs');
    Route::post('/outputs', [ParticipantController::class, 'storeOutput']);
    Route::get('/final-report', [ParticipantController::class, 'finalReport'])->name('final-report');
    Route::post('/final-report', [ParticipantController::class, 'storeOutput']);
    Route::get('/evaluation', [ParticipantController::class, 'evaluation'])->name('evaluation');
    Route::post('/evaluation', [ParticipantController::class, 'storeEvaluation']);
    Route::get('/collaboration', [ParticipantController::class, 'collaboration'])->name('collaboration');
    Route::get('/notifications', [ParticipantController::class, 'notifications'])->name('notifications');
    Route::get('/settings', [ParticipantController::class, 'settings'])->name('settings');
    Route::post('/settings', [ParticipantController::class, 'updateSettings']);
});

Route::middleware(['auth', 'role:mentor'])->prefix('mentor')->name('mentor.')->group(function () {
    Route::get('/dashboard', [MentorController::class, 'dashboard'])->name('dashboard');
    Route::get('/applications', [MentorController::class, 'applications'])->name('applications');
    Route::post('/applications/{application}', [MentorController::class, 'reviewApplication'])->name('applications.review');
    Route::get('/participants', [MentorController::class, 'participants'])->name('participants');
    Route::get('/participants/{program}', [MentorController::class, 'showParticipant'])->name('participants.show');
    Route::get('/programs', [MentorController::class, 'programs'])->name('programs');
    Route::get('/agreements', [MentorController::class, 'agreements'])->name('agreements');
    Route::post('/agreements/{agreement}', [MentorController::class, 'reviewAgreement'])->name('agreements.review');
    Route::get('/timeline', [MentorController::class, 'timeline'])->name('timeline');
    Route::get('/logbooks', [MentorController::class, 'logbooks'])->name('logbooks');
    Route::post('/logbooks/{logbook}', [MentorController::class, 'reviewLogbook'])->name('logbooks.review');
    Route::get('/mentoring', [MentorController::class, 'mentoring'])->name('mentoring');
    Route::post('/mentoring', [MentorController::class, 'storeMentoring']);
    Route::get('/outputs', [MentorController::class, 'outputs'])->name('outputs');
    Route::post('/outputs/{output}', [MentorController::class, 'reviewOutput'])->name('outputs.review');
    Route::get('/evaluations', [MentorController::class, 'evaluations'])->name('evaluations');
    Route::post('/evaluations/{program}', [MentorController::class, 'storeEvaluation'])->name('evaluations.store');
    Route::get('/collaborations', [MentorController::class, 'collaborations'])->name('collaborations');
    Route::post('/collaborations/{program}', [MentorController::class, 'updateCollaboration'])->name('collaborations.update');
    Route::get('/notifications', [MentorController::class, 'notifications'])->name('notifications');
});

Route::middleware(['auth', 'role:admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/dashboard', [AdminController::class, 'dashboard'])->name('dashboard');
    Route::get('/users', [AdminController::class, 'users'])->name('users');
    Route::post('/users', [AdminController::class, 'storeUser']);
    Route::put('/users/{user}', [AdminController::class, 'updateUser'])->name('users.update');
    Route::get('/departments', [AdminController::class, 'departments'])->name('departments');
    Route::post('/departments', [AdminController::class, 'storeDepartment']);
    Route::put('/departments/{department}', [AdminController::class, 'updateDepartment'])->name('departments.update');
    Route::delete('/departments/{department}', [AdminController::class, 'destroyDepartment'])->name('departments.destroy');
    Route::get('/business-units', [AdminController::class, 'units'])->name('units');
    Route::get('/business-units/{department}', [AdminController::class, 'showDepartmentUnits'])->name('units.show');
    Route::get('/business-units/{businessUnit}/edit', [AdminController::class, 'editUnit'])->name('units.edit');
    Route::post('/business-units', [AdminController::class, 'storeUnit']);
    Route::put('/business-units/{businessUnit}', [AdminController::class, 'updateUnit'])->name('units.update');
    Route::delete('/business-units/{businessUnit}', [AdminController::class, 'destroyUnit'])->name('units.destroy');
    Route::get('/mentors', [AdminController::class, 'mentors'])->name('mentors');
    Route::get('/participants', [AdminController::class, 'participants'])->name('participants');
    Route::get('/programs', [AdminController::class, 'programs'])->name('programs');
    Route::post('/programs/{program}/complete', [AdminController::class, 'completeProgram'])->name('programs.complete');
    Route::get('/matching', [AdminController::class, 'matching'])->name('matching');
    Route::post('/matching/{application}', [AdminController::class, 'updateMatching'])->name('matching.update');
    Route::get('/agreements', [AdminController::class, 'agreements'])->name('agreements');
    Route::get('/monitoring', [AdminController::class, 'monitoring'])->name('monitoring');
    Route::get('/evaluations', [AdminController::class, 'evaluations'])->name('evaluations');
    Route::get('/collaborations', [AdminController::class, 'collaborations'])->name('collaborations');
    Route::get('/reports', [AdminController::class, 'reports'])->name('reports');
    Route::get('/news', [AdminController::class, 'news'])->name('news');
    Route::post('/news', [AdminController::class, 'storeNews']);
    Route::put('/news/{news}', [AdminController::class, 'updateNews'])->name('news.update');
    Route::delete('/news/{news}', [AdminController::class, 'destroyNews'])->name('news.destroy');
    Route::get('/department-hero', [AdminController::class, 'departmentHero'])->name('department-hero');
    Route::post('/department-hero/background', [AdminController::class, 'updateDepartmentHeroBackground'])->name('department-hero.background');
    Route::post('/department-hero/slides', [AdminController::class, 'storeDepartmentHeroSlide'])->name('department-hero.slides.store');
    Route::put('/department-hero/slides/{heroSlide}', [AdminController::class, 'updateDepartmentHeroSlide'])->name('department-hero.slides.update');
    Route::delete('/department-hero/slides/{heroSlide}', [AdminController::class, 'destroyDepartmentHeroSlide'])->name('department-hero.slides.destroy');
    Route::get('/settings', [AdminController::class, 'settings'])->name('settings');
});
