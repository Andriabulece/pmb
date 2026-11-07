<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\RegistrationPortalController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::middleware(['auth'])->group(function () {
    Route::get('/dashboard', function () {
        return redirect()->route('pmb.portal');
    })->name('dashboard');

    Route::get('/pmb', [RegistrationPortalController::class, 'index'])->name('pmb.portal');
    Route::post('/pmb/register', [RegistrationPortalController::class, 'store'])->name('pmb.register.store');
    Route::post('/pmb/documents/{registration}', [RegistrationPortalController::class, 'uploadDocuments'])->name('pmb.documents.upload');
    Route::post('/pmb/payment/{registration}', [RegistrationPortalController::class, 'uploadPayment'])->name('pmb.payment.upload');
    Route::get('/pmb/acceptance/{registration}/download', [RegistrationPortalController::class, 'printAcceptance'])->name('pmb.print.acceptance');

    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
