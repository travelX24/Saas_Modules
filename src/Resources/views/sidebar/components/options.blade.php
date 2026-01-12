@props([
  'locale' => 'ar',
])

<div class="sidebar-options">
    @props([
        'locale' => null,
    ])
    
    @php
        $locale = $locale ?: app()->getLocale();
        $isAr = substr($locale, 0, 2) === 'ar';
    @endphp
    
    <div class="sidebar-options">
    
        {{-- ✅ Expanded sidebar: Language title + switcher centered --}}
<div class="language-switcher expanded-only">
    <div class="language-switcher-title lang-title-center">
        <i class="fas fa-globe"></i>
        <span>{{ tr('Language') }}</span>
    </div>

    <div class="lang-center">
        <x-ui.language-switcher :compact="true" />
    </div>
</div>

{{-- ✅ Collapsed sidebar: mini buttons centered --}}
<div class="language-switcher collapsed-only lang-center-col">
    <div class="lang-title-mini" title="{{ tr('Language') }}">
        <i class="fas fa-globe"></i>
    </div>

    <a href="{{ route('lang.switch', 'ar') }}"
       class="lang-mini {{ $isAr ? 'active' : '' }}"
       title="AR">AR</a>

    <a href="{{ route('lang.switch', 'en') }}"
       class="lang-mini {{ !$isAr ? 'active' : '' }}"
       title="EN">EN</a>
</div>

    </div>


    {{-- Logout Button with Confirmation --}}
    <div x-data="{ 
        init() {
            // استمع للـ event عند التحميل
            window.addEventListener('logoutConfirmed', () => {
                console.log('Logout event received - submitting form...');
                this.logout();
            });
        },
        logout() { 
            // إرسال الفورم مباشرة - Laravel سيتولى تسجيل الخروج وإعادة التوجيه
            const form = document.getElementById('logout-form');
            if (form) {
                form.submit();
            } else {
                console.error('Logout form not found!');
                // Fallback: redirect manually
                @php
                    $loginRoute = \Illuminate\Support\Facades\Route::has('authkit.login')
                        ? route('authkit.login')
                        : (\Illuminate\Support\Facades\Route::has('login') ? route('login') : '/login');
                @endphp
                window.location.href = '{{ $loginRoute }}';
            }
        } 
    }">
        {{-- Logout Form (Hidden) --}}
        <form id="logout-form" method="POST" action="{{ route('authkit.logout') }}" style="display: none;">
            @csrf
        </form>

        <button 
            type="button" 
            class="logout-btn"
            @click="$dispatch('open-confirm-logout')"
        >
            <i class="fas fa-sign-out-alt"></i>
            <span class="logout-btn-text">@tr('Logout')</span>
        </button>
    </div>
</div>
