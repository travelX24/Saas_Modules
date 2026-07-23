<?php

use Athka\Saas\Http\Controllers\Auth\CompanyAdminSetPasswordController;
use Athka\Saas\Http\Middleware\EnsureCompanyAdmin;
use Athka\Saas\Http\Middleware\EnsureSaasSystemAdmin;
use Athka\Saas\Http\Middleware\ForceCompanyDomain;
use Athka\Saas\Livewire\Companies\Create as CompanyCreate;
use Athka\Saas\Livewire\Companies\Index as CompanyIndex;
use Athka\Saas\Livewire\Dashboard\Index as SaasDashboard;
use Athka\Saas\Livewire\Translations\Index as TranslationsIndex;
use Athka\Saas\Livewire\EmailTemplates\Create as EmailTemplatesCreate;
use Athka\Saas\Livewire\EmailTemplates\Edit as EmailTemplatesEdit;
use Athka\Saas\Livewire\Emails\Index as EmailsIndex;
use Athka\Saas\Livewire\Emails\Send as EmailsSend;
use Athka\Saas\Livewire\Emails\Scheduled as EmailsScheduled;
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

        // Email Messages (Unified interface with tabs)
        Route::get('/emails', EmailsIndex::class)->name('emails.index');
        Route::get('/emails/send', EmailsSend::class)->name('emails.send');
        Route::get('/emails/scheduled', EmailsScheduled::class)->name('emails.scheduled');

        // Email Templates - Redirect old route to new unified interface
        Route::get('/email-templates', function () {
            return redirect()->route('saas.emails.index', ['tab' => 'templates']);
        })->name('email-templates.index');
        Route::get('/email-templates/create', EmailTemplatesCreate::class)->name('email-templates.create');
        Route::get('/email-templates/{id}/edit', EmailTemplatesEdit::class)->name('email-templates.edit');
    });

/**
 * ✅ Route لعرض صور الشركات من storage
 */
Route::get('/storage/company-logo/{path}', function (string $path) {
    $basePath = realpath(storage_path('app/public'));
    $requestedPath = str_replace(['\\', '//'], '/', ltrim($path, '/\\'));

    abort_unless(
        preg_match('#^saas/companies/\d+/logo/[^/]+\.(?:jpg|jpeg|png|gif|webp|svg)$#i', $requestedPath) === 1,
        404
    );

    $fullPath = $basePath ? realpath($basePath.DIRECTORY_SEPARATOR.$requestedPath) : false;

    if (! $basePath || ! $fullPath || ! is_file($fullPath)) {
        abort(404);
    }

    $basePrefix = rtrim($basePath, DIRECTORY_SEPARATOR).DIRECTORY_SEPARATOR;
    if (! str_starts_with($fullPath, $basePrefix)) {
        abort(404);
    }

    return response()->file($fullPath, [
        'Cache-Control' => 'public, max-age=3600',
        'Content-Security-Policy' => "default-src 'none'; style-src 'unsafe-inline'; sandbox",
    ]);
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

        Route::get('/set-password', [CompanyAdminSetPasswordController::class, 'create'])
            ->middleware('signed:relative')
            ->name('company-admin.password.create');

        Route::post('/set-password', [CompanyAdminSetPasswordController::class, 'store'])
            ->name('company-admin.password.store');

        Route::get('/set-password/done', [CompanyAdminSetPasswordController::class, 'done'])
            ->name('company-admin.password.done');
    });

/**
 * ✅ صفحة Company Admin (مؤقتة للاختبار)
 * URL: /company-admin/hello
 */
Route::prefix('')
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

        Route::get('/dashboard', function () {
            return view('company-admin.dashboard');
        })->name('dashboard');
    });
