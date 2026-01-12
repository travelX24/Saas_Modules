<x-authkit::ui.auth-shell :showTabs="false" :title="tr('Password set successfully')">

    <div class="text-center">
        <div class="mx-auto w-12 h-12 sm:w-14 sm:h-14 rounded-xl sm:rounded-2xl flex items-center justify-center text-white mb-3 sm:mb-4 text-lg sm:text-xl"
             style="background: var(--brand-via);">
            ✓
        </div>

        <h2 class="text-xl sm:text-2xl font-extrabold text-slate-900">
            {{ tr('Password set successfully') }}
        </h2>

        <p class="text-sm sm:text-base text-slate-500 mt-2">
            {{ tr('You can now login to your account.') }}
        </p>

        <div class="mt-6">
            <form method="GET" action="/login" class="w-full">
                <x-ui.primary-button :arrow="false" :fullWidth="true" type="submit">
                    {{ tr('Go to Login') }}
                </x-ui.primary-button>
            </form>
        </div>

    </div>

</x-authkit::ui.auth-shell>
