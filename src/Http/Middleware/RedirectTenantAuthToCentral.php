<?php

namespace Athka\Saas\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class RedirectTenantAuthToCentral
{
    public function handle(Request $request, Closure $next)
    {
        $host = strtolower($request->getHost());

        $base = strtolower(env('TENANT_BASE_DOMAIN', 'athkahr.com'));
        $central = strtolower(env('CENTRAL_DOMAIN', $base));

        // ✅ لو نحن على nip.io استخرج IP واصنع base/central ديناميكي
        if (preg_match('/\.(\d{1,3}(?:\.\d{1,3}){3})\.nip\.io$/', $host, $m)) {
            $ip = $m[1];
            $base = "athkahr.$ip.nip.io";
            $central = $base;
        }


        // إذا كنا على المركز خله يكمل
        if ($host === $central || $host === 'www.'.$central) {
            return $next($request);
        }

        // فقط لو كان subdomain تابع لنا
        if (! str_ends_with($host, '.'.$base)) {
            return $next($request);
        }

        $path = '/'.ltrim($request->path(), '/');

        $isAuth =
            $path === '/login' ||
            $path === '/forgot-password' ||
            str_starts_with($path, '/reset-password');

        if (! $isAuth) {
            return $next($request);
        }

        $scheme = $request->isSecure() ? 'https' : 'http';
        $port = $request->getPort();
        $portPart = in_array($port, [80, 443], true) ? '' : ':'.$port;

        return redirect()->away($scheme.'://'.$central.$portPart.$path);
    }
}
