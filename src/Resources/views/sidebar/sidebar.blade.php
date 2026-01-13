@php
    $user = auth()->user();

    $name  = (string) ($user?->name ?? $user?->full_name ?? 'User');
    $photo = data_get($user, 'profile_photo_url')
          ?: data_get($user, 'avatar_url')
          ?: data_get($user, 'avatar')
          ?: null;

    $role  = @tr('System Admin'); // عدّلها لاحقاً حسب نظام الصلاحيات عندك

    $locale = app()->getLocale();

    $items = [
        [
            'href' => route('saas.dashboard'),
            'label' => tr('Dashboard'),
            'active' => request()->routeIs('saas.dashboard'),
            'disabled' => false,
            'icon' => '<i class="fas fa-home"></i>',
        ],
        [
            'href' => route('saas.companies.index'),
            'label' => tr('Companies'),
            'active' => request()->routeIs('saas.companies.*'),
            'disabled' => false,
            'icon' => '<i class="fas fa-building"></i>',
        ],
        [
            'href' => route('saas.translations.index'),
            'label' => tr('Translations'),
            'active' => request()->routeIs('saas.translations.*'),
            'disabled' => false,
            'icon' => '<i class="fas fa-language"></i>',
        ],
        [
            'href' => route('saas.emails.index'),
            'label' => tr('Email Messages'),
            'active' => request()->routeIs('saas.email-templates.*') || request()->routeIs('saas.emails.*'),
            'disabled' => false,
            'icon' => '<i class="fas fa-envelope"></i>',
        ],

    ];
@endphp
<nav class="sidebar" id="sidebar"
     x-data="{ collapsed: false, open: false }"
     :class="{ collapsed: collapsed, open: open }"
     @keydown.escape.window="open = false"
     @click.outside="if (window.innerWidth < 769) open = false">

    {{-- Toggle --}}
    @include('saas::sidebar.components.toggle')

    {{-- Header --}}
    @include('saas::sidebar.components.header', [
        'appName' => @tr('Athka HR'),
    ])

    {{-- Profile --}}
    @include('saas::sidebar.components.profile', [
        'name'  => $name,
        'role'  => $role,
        'photo' => $photo,
    ])

    {{-- Nav --}}
    @include('saas::sidebar.components.nav', [
        'items' => $items,
    ])

    {{-- Options --}}
    @include('saas::sidebar.components.options', [
        'locale' => $locale,
    ])
</nav>

{{-- Mobile Toggle --}}
@include('saas::sidebar.components.mobile-toggle')
