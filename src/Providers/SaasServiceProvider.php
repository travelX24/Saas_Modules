<?php

namespace Athka\Saas\Providers;

use Illuminate\Support\Facades\Blade;
use Illuminate\Support\ServiceProvider;
use Livewire\Livewire;

class SaasServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        // Routes / Views / Migrations
        $this->loadRoutesFrom(__DIR__.'/../Routes/web.php');
        $this->loadViewsFrom(__DIR__.'/../Resources/views', 'saas');
        $this->loadMigrationsFrom(__DIR__.'/../Database/Migrations');

        // Sidebar anonymous components
        Blade::component('saas::sidebar.components.toggle', 'saas.sidebar.toggle');
        Blade::component('saas::sidebar.components.header', 'saas.sidebar.header');
        Blade::component('saas::sidebar.components.profile', 'saas.sidebar.profile');
        Blade::component('saas::sidebar.components.nav', 'saas.sidebar.nav');
        Blade::component('saas::sidebar.components.options', 'saas.sidebar.options');
        Blade::component('saas::sidebar.components.mobile-toggle', 'saas.sidebar.mobile-toggle');

        // Company components
        Blade::component('saas::components.view-company-modal', 'saas.components.view-company-modal');

        // ✅ FIX: Register Livewire components inside Modules
        // هذا اللي يمنع ComponentNotFoundException عند التنقّل بين التبويبات
        if (class_exists(Livewire::class)) {
            Livewire::component('saas.dashboard', \Athka\Saas\Livewire\Dashboard\Index::class);
            Livewire::component('saas.companies.index', \Athka\Saas\Livewire\Companies\Index::class);
            Livewire::component('saas.companies.create', \Athka\Saas\Livewire\Companies\Create::class);
            Livewire::component('saas.companies.edit', \Athka\Saas\Livewire\Companies\Edit::class);
            Livewire::component('saas.translations.index', \Athka\Saas\Livewire\Translations\Index::class);
        }
    }

    public function register(): void
    {
        //
    }
}
