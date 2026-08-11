<?php

use App\Http\Controllers\Admin\BookingController;
use App\Http\Controllers\Admin\CustomerController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\FeedbackController;
use App\Http\Controllers\Admin\JobPhotoController;
use App\Http\Controllers\Admin\LeaveRequestController;
use App\Http\Controllers\Admin\NotificationController;
use App\Http\Controllers\Admin\PayoutController;
use App\Http\Controllers\Admin\ReportController;
use App\Http\Controllers\Admin\ServiceController;
use App\Http\Controllers\Admin\ServiceProviderController;
use App\Http\Controllers\Admin\SettingController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Admin\ZoneController;
use App\Http\Controllers\ProfileController;
use App\Models\PricingSlab;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect()->route('login');
});

Route::middleware(['auth', 'admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    Route::get('bookings/import', [BookingController::class, 'importForm'])->name('bookings.import');
    Route::get('bookings/import/template', [BookingController::class, 'downloadTemplate'])->name('bookings.import.template');
    Route::post('bookings/import', [BookingController::class, 'import'])->name('bookings.import.store');
    Route::resource('bookings', BookingController::class);
    Route::post('bookings/{booking}/assign', [BookingController::class, 'assign'])->name('bookings.assign');
    Route::post('bookings/{booking}/cancel', [BookingController::class, 'cancel'])->name('bookings.cancel');

    Route::resource('customers', CustomerController::class)->only(['index', 'show', 'edit', 'update']);

    Route::resource('services', ServiceController::class);
    Route::post('services/{service}/slabs', [ServiceController::class, 'storeSlab'])->name('services.slabs.store');
    Route::delete('slabs/{slab}', [ServiceController::class, 'destroySlab'])->name('services.slabs.destroy');

    Route::resource('providers', ServiceProviderController::class);
    Route::post('providers/{provider}/toggle-status', [ServiceProviderController::class, 'toggleStatus'])->name('providers.toggle-status');
    Route::post('providers/{provider}/resend-credentials', [ServiceProviderController::class, 'resendCredentials'])->name('providers.resend-credentials');

    Route::resource('zones', ZoneController::class);

    Route::resource('payouts', PayoutController::class)->only(['index', 'create', 'store']);
    Route::post('payouts/{payout}/mark-paid', [PayoutController::class, 'markPaid'])->name('payouts.mark-paid');

    Route::get('feedback', [FeedbackController::class, 'index'])->name('feedback.index');
    Route::post('feedback/{feedback}/respond', [FeedbackController::class, 'respond'])->name('feedback.respond');
    Route::post('feedback/{feedback}/toggle-flag', [FeedbackController::class, 'toggleFlag'])->name('feedback.toggle-flag');

    Route::get('leaves', [LeaveRequestController::class, 'index'])->name('leaves.index');
    Route::post('leaves/{leave}/approve', [LeaveRequestController::class, 'approve'])->name('leaves.approve');
    Route::post('leaves/{leave}/reject', [LeaveRequestController::class, 'reject'])->name('leaves.reject');

    Route::get('photos', [JobPhotoController::class, 'index'])->name('photos.index');

    Route::get('reports', [ReportController::class, 'index'])->name('reports.index');

    Route::get('settings', [SettingController::class, 'index'])->name('settings.index');
    Route::put('settings', [SettingController::class, 'update'])->name('settings.update');

    Route::resource('notifications', NotificationController::class)->only(['index', 'create', 'store']);

    Route::middleware('super_admin')->group(function () {
        Route::resource('users', UserController::class);
    });

    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
});

require __DIR__.'/auth.php';
