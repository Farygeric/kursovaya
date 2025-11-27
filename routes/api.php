<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\VacancyController;
use App\Http\Controllers\ApplicationController;
use App\Http\Controllers\GameController;
use App\Http\Controllers\ProposalController;
use App\Http\Controllers\DepartmentController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\GameInfoController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
*/


Route::post('/auth/login', [AuthController::class, 'login']);

// ---- Vacancies_application ----
Route::post('/applications/{vacancyId}', [ApplicationController::class, 'store']);

// ---- Vacancies_info ----
Route::get('/vacancies', [VacancyController::class, 'index']);      
Route::get('/vacancies/count', [VacancyController::class, 'count']); 
Route::get('/vacancies/{id}', [VacancyController::class, 'show']);  
Route::apiResource('vacancies', VacancyController::class);

// ---- Games_info ----
Route::get('/games/data', [GameInfoController::class, 'gameDatas']);
Route::get('/games', [GameController::class, 'index']);
Route::get('/games/{id}', [GameController::class, 'show']);



Route::apiResource('departments', DepartmentController::class)->only(['index', 'store', 'update', 'destroy']);



// ---- Proposals ----
Route::post('/proposals', [ProposalController::class, 'store']);


Route::middleware('token.auth')->group(function () {

    // ---- User ----
    Route::get('/user', [UserController::class, 'me']);
    Route::get('/users', [UserController::class, 'index']);
    Route::post('/users', [UserController::class, 'store']);
    Route::patch('/users/{id}/role', [UserController::class, 'updateRole']);
    Route::post('/user/password', [UserController::class, 'updatePassword']);
    Route::post('/users/{id}/password', [UserController::class, 'resetPassword']);
    Route::patch('/users/{id}', [UserController::class, 'update']);
    Route::delete('/users/{id}', [UserController::class, 'destroy']);



    // ---- Auth ----
    Route::post('/auth/logout', [AuthController::class, 'logout']);

    // ---- Vacancies ----
    Route::apiResource('vacancies', VacancyController::class)
        ->only(['store', 'update', 'destroy']);

    // ---- Applications ----
    Route::get('/applications', [ApplicationController::class, 'index']);
    Route::get('/applications/{id}', [ApplicationController::class, 'show']);
    Route::get('/applications/download/{filename}', [ApplicationController::class, 'download']);
    Route::patch('/applications/{id}/status', [ApplicationController::class, 'updateStatus']);
    Route::delete('/applications/{id}', [ApplicationController::class, 'destroy']);

    // ---- Proposals ----
    Route::get('/proposals', [ProposalController::class, 'index']);
    Route::get('/proposals/{id}', [ProposalController::class, 'show']);
    Route::patch('/proposals/{id}/status', [ProposalController::class, 'updateStatus']);
    Route::get('/proposals/{id}/download', [ProposalController::class, 'downloadFile']);
    Route::delete('/proposals/{id}', [ProposalController::class, 'destroy']);

    // ---- Games ----
    Route::apiResource('games', GameController::class)->except(['index']);
});