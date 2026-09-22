<?php

use App\Http\Controllers\AgreementController;
use App\Http\Controllers\Api\AdminApiController;
use App\Http\Controllers\ApiAuthController;
use App\Http\Controllers\ApplicationController;
use App\Http\Controllers\OpportunityController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ProgramController;
use Illuminate\Support\Facades\Route;

Route::get('/health-api', fn () => ['ok' => true]);

Route::post('/login', [ApiAuthController::class, 'login']);
Route::post('/register', [ApiAuthController::class, 'register']);

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/me', [ApiAuthController::class, 'me']);
    Route::post('/logout', [ApiAuthController::class, 'logout']);
    Route::put('/profile', [ProfileController::class, 'update']);

    Route::get('/opportunities', [OpportunityController::class, 'index']);
    Route::post('/opportunities', [OpportunityController::class, 'store']);
    Route::get('/opportunities/{opportunity}', [OpportunityController::class, 'show']);

    Route::get('/applications', [ApplicationController::class, 'index']);
    Route::post('/applications', [ApplicationController::class, 'store']);
    Route::post('/applications/{application}/review', [ApplicationController::class, 'review']);

    Route::get('/agreements/{agreement}', [AgreementController::class, 'show']);
    Route::put('/agreements/{agreement}', [AgreementController::class, 'update']);
    Route::post('/agreements/{agreement}/approve', [AgreementController::class, 'approve']);

    Route::get('/programs', [ProgramController::class, 'index']);
    Route::get('/programs/{program}', [ProgramController::class, 'show']);
    Route::get('/logbooks/history', [ProgramController::class, 'history']);
    Route::put('/programs/{program}/notes', [ProgramController::class, 'updatePhaseNotes']);
    Route::post('/programs/{program}/logbooks', [ProgramController::class, 'storeLogbook']);
    Route::post('/programs/{program}/logbooks/{logbook}/verify', [ProgramController::class, 'verifyLogbook'])->scopeBindings();
    Route::post('/programs/{program}/mentorings', [ProgramController::class, 'storeMentoring']);
    Route::post('/programs/{program}/checkpoints', [ProgramController::class, 'storeCheckpoint']);
    Route::post('/programs/{program}/evaluations', [ProgramController::class, 'storeEvaluation']);
    Route::post('/programs/{program}/report/generate', [ProgramController::class, 'generateReport']);
    Route::post('/programs/{program}/outputs', [ProgramController::class, 'storeOutput']);
    Route::post('/programs/{program}/connect', [ProgramController::class, 'connect']);

    Route::get('/admin/overview', [AdminApiController::class, 'overview']);
    Route::get('/admin/users', [AdminApiController::class, 'users']);
    Route::post('/admin/users/{user}/verify', [AdminApiController::class, 'verify']);
    Route::post('/admin/users/{user}/reject', [AdminApiController::class, 'reject']);
    Route::get('/admin/programs', [AdminApiController::class, 'programs']);
    Route::get('/admin/department-needs', [AdminApiController::class, 'needs']);
    Route::post('/admin/department-needs', [AdminApiController::class, 'storeNeed']);
});
