<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\ChargebeeController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/
// test section
Route::view('/plans', 'plans');
Route::get('/subscribe', [ChargebeeController::class, 'subscribe']);
Route::post('/subscription/cancel', [ChargebeeController::class, 'cancelSubscription'])->name('subscription.cancel');
Route::post('/payment/refund', [ChargebeeController::class, 'refundPayment'])->name('payment.refund');
Route::get('/subscription/success', [ChargebeeController::class, 'subscriptionSuccess'])->name('subscription.success');
Route::get('/subscription/cancel', [ChargebeeController::class, 'subscriptionCancel'])->name('subscription.cancel');
// test section end


// Note: Authentication Routes
Route::get('/', [AuthController::class, 'showLoginForm'])->name('login');
Route::post('/login', [AuthController::class, 'login'])->name('doLogin');
Route::get('/logout', [AuthController::class, 'logout'])->name('logout');

Route::get('/test-admin', [AuthController::class, 'testAdmin'])->name('test.admin');
Route::get('/register', [AuthController::class, 'showRegisterForm'])->name('register');
Route::post('/register', [AuthController::class, 'register']);

// // Note: Password Reset Routes
Route::get('/forgot-password', [AuthController::class, 'showForgotPasswordForm'])->name('password.request');
Route::post('/forgot-password', [AuthController::class, 'sendResetLink'])->name('password.email');
Route::get('/reset-password/{token}', [AuthController::class, 'showResetPasswordForm'])->name('password.reset');
Route::post('/reset-password', [AuthController::class, 'resetPassword'])->name('password.update');
Route::post('/change-password', [AuthController::class, 'changePassword'])->name('change.password')->middleware('auth');

// Note: Role-based Dashboard Routes
// Info: Admin Access
Route::middleware(['role:1,2,5'])->prefix('admin')->name('admin.')->group(function () {
    // Add view-only middleware for role 5
    Route::middleware('view.only')->group(function () {
        Route::get('/dashboard', [App\Http\Controllers\Admin\AdminController::class, 'dashboard'])->name('dashboard');
        Route::get('/profile', [App\Http\Controllers\Admin\AdminController::class, 'profile'])->name('profile');
        Route::get('/settings', [App\Http\Controllers\Admin\AdminController::class, 'settings'])->name('settings');
        Route::get('/pricing', [App\Http\Controllers\Admin\PlanController::class, 'index'])->name('pricing');

        // Plans routes
        Route::resource('plans', \App\Http\Controllers\Admin\PlanController::class);
        Route::get('plans-with-features', [\App\Http\Controllers\Admin\PlanController::class, 'getPlansWithFeatures'])->name('plans.with.features');
        
        // Features routes
        Route::get('features/list', [\App\Http\Controllers\Admin\FeatureController::class, 'list'])->name('features.list');
        Route::post('features/store', [\App\Http\Controllers\Admin\FeatureController::class, 'store'])->name('features.store');
        Route::put('features/{feature}', [\App\Http\Controllers\Admin\FeatureController::class, 'update'])->name('features.update');
        Route::delete('features/{feature}', [\App\Http\Controllers\Admin\FeatureController::class, 'destroy'])->name('features.destroy');
    });

    // Routes that don't need view-only middleware
    // Route::post('/profile/update', [App\Http\Controllers\Admin\ProfileController::class, 'update'])->name('profile.update');
});
Route::post('admin/profile/update', [App\Http\Controllers\Admin\ProfileController::class, 'update'])->name('admin.profile.update');


// Info: Customer Access
Route::middleware(['role:3'])->prefix('customer')->name('customer.')->group(function () {
   
    Route::get('/dashboard', function () {
        // return view('customer.dashboard');
        return view('customer.dashboard');
    })->name('dashboard');
    Route::get('/orders', function () {
        return view('customer.orders.orders');
    })->name('orders');
    Route::get('/support', function () {
        return view('customer.support.support');
    })->name('support');
    Route::get('/profile', function () {
        return view('customer.profile.profile');
    })->name('profile');
    Route::get('/settings', function () {
        return view('customer.settings.settings');
    })->name('settings');
});

// Info: Contractor Access
Route::middleware(['role:4'])->prefix('contractor')->name('contractor.')->group(function () {
    // Route::post('/profile/update', [App\Http\Controllers\Admin\ProfileController::class, 'update'])->name('profile.update');
    Route::get('/dashboard', function () {
        // return view('contractor.dashboard');
        return view('contractor.dashboard');
    })->name('dashboard');
    Route::get('/orders', function () {
        return view('contractor.orders.orders');
    })->name('orders');
    Route::get('/pricing', function () {
        return view('contractor.pricing.pricing');
    })->name('pricing');
    Route::get('/payments', function () {
        return view('contractor.payments.payments');
    })->name('payments');
    Route::get('/support', function () {
        return view('contractor.support.support');
    })->name('support');
    Route::get('/profile', function () {
        return view('contractor.profile.profile');
    })->name('profile');
    Route::get('/settings', function () {
        return view('contractor.settings.settings');
    })->name('settings');
});
// Route::get('/', function () {
//     return view('admin/auth/login');
// });

// Route::get('/signup', function () {
//     return view('admin/auth/signup');
// });


Route::get('/forget_password', function () {
    return view('admin/auth/forget_password');
});

Route::get('/reset_password', function () {
    return view('admin/auth/reset_password');
});

// Route::get('/dashboard', function () {
//     return view('admin/dashboard/dashboard');
// });

Route::get('/admins', function () {
    return view('admin/admins/admins');
});

Route::get('/customers', function () {
    return view('admin/customers/customers');
});

Route::get('/contractor', function () {
    return view('admin/contractor/contractor');
});

Route::get('/roles', function () {
    return view('admin/roles/roles');
});

Route::get('/permissions', function () {
    return view('admin/permissions/permissions');
});

Route::get('/payments', function () {
    return view('admin/payments/payments');
});



Route::get('/orders', function () {
    return view('admin/orders/orders');
});

Route::get('/contact_us', function () {
    return view('admin/contact_us/contact_us');
});

Route::get('/support', function () {
    return view('admin/support/support');
});

Route::get('/profile', function () {
    return view('admin/profile/profile');
})->name('profile');

Route::get('/settings', function () {
    return view('admin/settings/settings');
});
    
Route::get('/notification', function () {
    return view('admin/notification/notification');
});

