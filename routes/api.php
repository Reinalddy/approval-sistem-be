<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\ClaimController;
use Illuminate\Support\Facades\Route;



Route::post('/login', [AuthController::class, 'login']);

Route::middleware(['auth:sanctum'])->group(function () {

    Route::post('/logout', [AuthController::class, 'logout']);
    // ROLE: USER
    Route::middleware('role:User')->group(function () {
        Route::post('/claims', [ClaimController::class, 'store']);
        Route::get('/claims/my', [ClaimController::class, 'myClaims']);
        Route::patch('/claims/{id}/submit', [ClaimController::class, 'changeStatus']);
    });

    // ROLE: VERIFIER
    Route::middleware('role:Verifier')->group(function () {
        Route::get('/claims/submitted', [ClaimController::class, 'getSubmitted']);
        Route::patch('/claims/{id}/verify', [ClaimController::class, 'changeStatus']);
    });

    // ROLE: APPROVER
    Route::middleware('role:Approver')->group(function () {
        Route::get('/claims/reviewed', [ClaimController::class, 'getReviewed']);
        // Approver punya 2 aksi (Approve/Reject), endpoint bisa disatukan atau dipisah
        // Keduanya tetap mengarah ke changeStatus
        Route::patch('/claims/{id}/approve', [ClaimController::class, 'changeStatus']);
        Route::patch('/claims/{id}/reject', [ClaimController::class, 'changeStatus']);
    });
});