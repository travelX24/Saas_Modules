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

    $thumbnailWidth = (int) request()->query('w', 0);
    $allowedThumbnailWidths = [72, 96, 128, 160];
    $extension = strtolower(pathinfo($fullPath, PATHINFO_EXTENSION));

    if (
        in_array($thumbnailWidth, $allowedThumbnailWidths, true)
        && extension_loaded('gd')
        && in_array($extension, ['jpg', 'jpeg', 'png', 'webp'], true)
    ) {
        try {
            [$sourceWidth, $sourceHeight] = getimagesize($fullPath) ?: [0, 0];

            if ($sourceWidth > $thumbnailWidth && $sourceHeight > 0) {
                $modifiedAt = filemtime($fullPath) ?: time();
                $thumbnailExtension = $extension === 'png' ? 'png' : ($extension === 'webp' && function_exists('imagewebp') ? 'webp' : 'jpg');
                $cacheDir = storage_path('framework/cache/company-logo-thumbnails/'.$thumbnailWidth);
                $cacheFile = $cacheDir.'/'.sha1($requestedPath.'|'.$thumbnailWidth.'|'.$modifiedAt).'.'.$thumbnailExtension;

                if (! is_file($cacheFile)) {
                    if (! is_dir($cacheDir)) {
                        mkdir($cacheDir, 0755, true);
                    }

                    $sourceImage = match ($extension) {
                        'jpg', 'jpeg' => imagecreatefromjpeg($fullPath),
                        'png' => imagecreatefrompng($fullPath),
                        'webp' => function_exists('imagecreatefromwebp') ? imagecreatefromwebp($fullPath) : false,
                        default => false,
                    };

                    if ($sourceImage !== false) {
                        $thumbnailHeight = max(1, (int) round($sourceHeight * ($thumbnailWidth / $sourceWidth)));
                        $thumbnailImage = imagecreatetruecolor($thumbnailWidth, $thumbnailHeight);

                        if (in_array($thumbnailExtension, ['png', 'webp'], true)) {
                            imagealphablending($thumbnailImage, false);
                            imagesavealpha($thumbnailImage, true);
                            $transparent = imagecolorallocatealpha($thumbnailImage, 0, 0, 0, 127);
                            imagefilledrectangle($thumbnailImage, 0, 0, $thumbnailWidth, $thumbnailHeight, $transparent);
                        }

                        imagecopyresampled($thumbnailImage, $sourceImage, 0, 0, 0, 0, $thumbnailWidth, $thumbnailHeight, $sourceWidth, $sourceHeight);

                        match ($thumbnailExtension) {
                            'png' => imagepng($thumbnailImage, $cacheFile, 6),
                            'webp' => imagewebp($thumbnailImage, $cacheFile, 82),
                            default => imagejpeg($thumbnailImage, $cacheFile, 82),
                        };

                        imagedestroy($thumbnailImage);
                        imagedestroy($sourceImage);
                    }
                }

                if (is_file($cacheFile)) {
                    $contentType = match ($thumbnailExtension) {
                        'png' => 'image/png',
                        'webp' => 'image/webp',
                        default => 'image/jpeg',
                    };

                    return response()->file($cacheFile, [
                        'Content-Type' => $contentType,
                        'Cache-Control' => 'public, max-age=31536000, immutable',
                        'Content-Security-Policy' => "default-src 'none'; style-src 'unsafe-inline'; sandbox",
                    ]);
                }
            }
        } catch (\Throwable $e) {
            // Fall back to the original logo; thumbnail generation must never block the UI.
        }
    }

    return response()->file($fullPath, [
        'Cache-Control' => 'public, max-age=86400',
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
            return view('company-admin.hello');
        })->name('hello');

        Route::get('/dashboard', function () {
            return view('company-admin.dashboard');
        })->name('dashboard');
    });
