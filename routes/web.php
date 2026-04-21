<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\WalletController;
use App\Http\Controllers\InvestmentController;
use App\Http\Controllers\KycController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\Admin\InvestmentPlanController as AdminPlanController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/dashboard', [DashboardController::class, 'index'])->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // Wallet
    Route::get('/wallet/deposit', [WalletController::class, 'deposit'])->name('wallet.deposit');
    Route::post('/wallet/deposit', [WalletController::class, 'depositPost'])->name('wallet.deposit.post');
    Route::get('/wallet/withdraw', [WalletController::class, 'withdraw'])->name('wallet.withdraw');
    Route::post('/wallet/withdraw', [WalletController::class, 'withdrawPost'])->name('wallet.withdraw.post');
    Route::get('/wallet/transfer', [WalletController::class, 'transfer'])->name('wallet.transfer');
    Route::post('/wallet/transfer', [WalletController::class, 'transferPost'])->name('wallet.transfer.post');

    // KYC
    Route::get('/kyc', [KycController::class, 'index'])->name('kyc.index');
    Route::post('/kyc', [KycController::class, 'store'])->name('kyc.store');

    // Investment
    Route::get('/investment', [InvestmentController::class, 'index'])->name('investment.index');
    Route::post('/investment', [InvestmentController::class, 'invest'])->name('investment.invest');

    // Admin Group
    Route::middleware(['auth'])->prefix('admin')->group(function () {
        Route::get('/', [AdminController::class, 'index'])->name('admin.index');
        Route::get('/users', [AdminController::class, 'users'])->name('admin.users');
        Route::get('/kyc', [AdminController::class, 'kyc'])->name('admin.kyc');
        Route::post('/kyc/{id}/approve', [AdminController::class, 'approveKyc'])->name('admin.kyc.approve');
        Route::post('/kyc/{id}/reject', [AdminController::class, 'rejectKyc'])->name('admin.kyc.reject');
        Route::get('/wallet', [AdminController::class, 'wallet'])->name('admin.wallet');
        Route::post('/wallet/{id}/approve', [AdminController::class, 'approveTx'])->name('admin.wallet.approve');
        Route::post('/wallet/{id}/reject', [AdminController::class, 'rejectTx'])->name('admin.wallet.reject');

        // User Management
        Route::get('/users/{id}/edit', [AdminController::class, 'editUser'])->name('admin.users.edit');
        Route::post('/users/{id}/update', [AdminController::class, 'updateUser'])->name('admin.users.update');
        Route::post('/users/{id}/balance', [AdminController::class, 'adjustBalance'])->name('admin.users.balance');
        Route::post('/users/{id}/reset-password', [AdminController::class, 'resetPassword'])->name('admin.users.reset_password');

        // Impersonate
        Route::get('/users/{id}/impersonate', [AdminController::class, 'impersonate'])->name('admin.users.impersonate');
        Route::get('/leave-impersonate', [AdminController::class, 'leaveImpersonate'])->name('admin.leave_impersonate');

        // Toggle Withdraw Unlock
        Route::post('/users/{id}/toggle-withdraw', [AdminController::class, 'toggleWithdraw'])->name('admin.users.toggle_withdraw');

        // Investment Plans
        Route::resource('plans', AdminPlanController::class)->names('admin.plans')->except(['show']);
    });
});

require __DIR__.'/auth.php';
