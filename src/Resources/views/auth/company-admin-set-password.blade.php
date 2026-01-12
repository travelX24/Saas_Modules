<x-authkit::ui.auth-shell :showTabs="false" :title="tr('Set Password')">

    <div class="mb-4 sm:mb-6">
        <h2 class="text-xl sm:text-2xl font-extrabold text-slate-900">
            {{ tr('Set Password') }}
        </h2>
        <p class="text-sm sm:text-base text-slate-500 mt-1">
            {{ tr('Create a password to access your company admin account.') }}
        </p>
    </div>

    <form method="POST" action="" class="space-y-3 sm:space-y-4">
        @csrf

        <input type="hidden" name="email" value="{{ $email }}">
        <input type="hidden" name="token" value="{{ $token }}">

        <div>
            <label class="block text-sm font-semibold text-slate-700 mb-2">{{ tr('Email') }}</label>
            <input value="{{ $email }}" disabled
                   class="w-full rounded-xl sm:rounded-2xl border border-slate-200 px-3 sm:px-4 py-2.5 sm:py-3 bg-slate-50 text-sm">
        </div>

        <div>
            <label class="block text-sm font-semibold text-slate-700 mb-2">{{ tr('Password') }}</label>
            <input type="password" name="password" id="password"
                   class="w-full rounded-xl sm:rounded-2xl border {{ $errors->has('password') ? 'border-red-400 focus:ring-red-500/10 focus:border-red-500' : 'border-slate-200 focus:ring-[color:var(--brand-via)]/10 focus:border-[color:var(--brand-via)]' }} px-3 sm:px-4 py-2.5 sm:py-3 text-sm
                          focus:outline-none focus:ring-4">
            @error('password') 
                <div class="text-xs text-red-600 mt-1 flex items-center gap-1">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                    </svg>
                    <span>{{ $message }}</span>
                </div>
            @enderror
        </div>

        <div>
            <label class="block text-sm font-semibold text-slate-700 mb-2">{{ tr('Confirm Password') }}</label>
            <input type="password" name="password_confirmation" id="password_confirmation"
                   class="w-full rounded-xl sm:rounded-2xl border {{ $errors->has('password_confirmation') ? 'border-red-400 focus:ring-red-500/10 focus:border-red-500' : 'border-slate-200 focus:ring-[color:var(--brand-via)]/10 focus:border-[color:var(--brand-via)]' }} px-3 sm:px-4 py-2.5 sm:py-3 text-sm
                          focus:outline-none focus:ring-4">
            <div id="password_match_error" class="text-xs text-red-600 mt-1 hidden flex items-center gap-1">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor">
                    <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                </svg>
                <span>{{ function_exists('tr') ? tr('The passwords do not match.') : 'The passwords do not match.' }}</span>
            </div>
            @error('password_confirmation') 
                <div class="text-xs text-red-600 mt-1 flex items-center gap-1">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                    </svg>
                    <span>{{ $message }}</span>
                </div>
            @enderror
        </div>

        @error('email') <div class="text-xs text-red-600">{{ $message }}</div> @enderror
        @error('token') <div class="text-xs text-red-600">{{ $message }}</div> @enderror

        <x-ui.primary-button :arrow="false" :fullWidth="true" type="submit">
            {{ tr('Set Password') }}
        </x-ui.primary-button>
    </form>

    <script>
        (function() {
            const passwordInput = document.getElementById('password');
            const passwordConfirmationInput = document.getElementById('password_confirmation');
            const errorDiv = document.getElementById('password_match_error');
            const form = document.querySelector('form');

            if (!passwordInput || !passwordConfirmationInput || !errorDiv) {
                return;
            }

            function checkPasswordMatch() {
                const password = passwordInput.value;
                const passwordConfirmation = passwordConfirmationInput.value;

                if (passwordConfirmation.length === 0) {
                    errorDiv.classList.add('hidden');
                    passwordConfirmationInput.classList.remove('border-red-400');
                    passwordConfirmationInput.classList.add('border-slate-200');
                    return;
                }

                if (password !== passwordConfirmation) {
                    errorDiv.classList.remove('hidden');
                    passwordConfirmationInput.classList.remove('border-slate-200');
                    passwordConfirmationInput.classList.add('border-red-400');
                } else {
                    errorDiv.classList.add('hidden');
                    passwordConfirmationInput.classList.remove('border-red-400');
                    passwordConfirmationInput.classList.add('border-slate-200');
                }
            }

            passwordInput.addEventListener('input', checkPasswordMatch);
            passwordConfirmationInput.addEventListener('input', checkPasswordMatch);

            form.addEventListener('submit', function(e) {
                const password = passwordInput.value;
                const passwordConfirmation = passwordConfirmationInput.value;

                if (password !== passwordConfirmation) {
                    e.preventDefault();
                    errorDiv.classList.remove('hidden');
                    passwordConfirmationInput.classList.remove('border-slate-200');
                    passwordConfirmationInput.classList.add('border-red-400');
                    passwordConfirmationInput.focus();
                }
            });
        })();
    </script>

</x-authkit::ui.auth-shell>
