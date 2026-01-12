<?php

use Athka\Saas\Http\Controllers\Auth\CompanyAdminSetPasswordController;
use Athka\Saas\Http\Middleware\EnsureCompanyAdmin;
use Athka\Saas\Http\Middleware\EnsureSaasSystemAdmin;
use Athka\Saas\Http\Middleware\ForceCompanyDomain;
use Athka\Saas\Livewire\Companies\Create as CompanyCreate;
use Athka\Saas\Livewire\Companies\Index as CompanyIndex;
use Athka\Saas\Livewire\Dashboard\Index as SaasDashboard;
use Athka\Saas\Livewire\Translations\Index as TranslationsIndex;
use Illuminate\Support\Facades\Route;

/**
 * ✅ SaaS (System Admin Only)
 */
Route::prefix('saas')
    ->name('saas.')
    ->middleware(['web', 'auth', EnsureSaasSystemAdmin::class])
    ->group(function () {

        Route::get('/', SaasDashboard::class)->name('dashboard');

        Route::get('/companies', CompanyIndex::class)->name('companies.index');

        Route::get('/companies/create', CompanyCreate::class)->name('companies.create');

        Route::get('/translations', TranslationsIndex::class)->name('translations.index');
    });

/**
 * ✅ Route لعرض صور الشركات من storage
 */
Route::get('/storage/company-logo/{path}', function (string $path) {
    $fullPath = storage_path('app/public/'.$path);

    if (! file_exists($fullPath)) {
        abort(404);
    }

    return response()->file($fullPath);
})->where('path', '.*')->name('storage.company-logo');

/**
 * ✅ Company Admin set-password:
 * - لا نستخدم guest حتى لا يعمل redirect لو أنت مسجل كسوبر أدمن.
 * - GET فقط signed
 */
Route::prefix('saas')
    ->name('saas.')
    ->middleware(['web'])
    ->group(function () {

        Route::get('/company-admin/set-password', [CompanyAdminSetPasswordController::class, 'create'])
            ->middleware('signed:relative')
            ->name('company-admin.password.create');

        Route::post('/company-admin/set-password', [CompanyAdminSetPasswordController::class, 'store'])
            ->name('company-admin.password.store');

        Route::get('/company-admin/set-password/done', [CompanyAdminSetPasswordController::class, 'done'])
            ->name('company-admin.password.done');
    });

/**
 * ✅ صفحة Company Admin (مؤقتة للاختبار)
 * URL: /company-admin/hello
 */
Route::prefix('company-admin')
    ->name('company-admin.')
    ->middleware([
        'web',
        'auth',
        EnsureCompanyAdmin::class,
        ForceCompanyDomain::class, // ✅ يحولك لـ athkahr.{domain}
        'company.domain',          // ✅ يحدد currentCompany من الـ host
        \Athka\Saas\Http\Middleware\SetCompanyTimezone::class, // ✅ يطبق timezone من الشركة
    ])
    ->group(function () {

        Route::get('/hello', function () {
            if (view()->exists('saas::company-admin.hello')) {
                return view('saas::company-admin.hello');
            }

            return view('company-admin.hello');
        })->name('hello');
    });
