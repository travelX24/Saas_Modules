<x-authkit::ui.auth-shell :showTabs="false" :title="tr('Set Password')">

    <div class="mb-4 sm:mb-6">
        <h2 class="text-xl sm:text-2xl font-extrabold text-slate-900">
            {{ tr('Set Password') }}
        </h2>
        <p class="text-sm sm:text-base text-slate-500 mt-1">
            {{ tr('Create a password to access your company admin account.') }}
        </p>
    </div>

    <form
        id="setPasswordForm"
        method="POST"
        action=""
        class="space-y-3 sm:space-y-4"
        x-data="{ isSubmitting: false }"
        @submit="if (isSubmitting) { $event.preventDefault(); return; } isSubmitting = true"
    >
        @csrf

        <input type="hidden" name="email" value="{{ $email }}">
        <input type="hidden" name="token" value="{{ $token }}">

        <div>
            <label class="block text-sm font-semibold text-slate-700 mb-2">{{ tr('Email') }}</label>
            <input value="{{ $email }}" disabled
                   class="w-full rounded-xl sm:rounded-2xl border border-slate-200 px-3 sm:px-4 py-2.5 sm:py-3 bg-slate-50 text-sm">
        </div>

        <div>
            <x-ui.password-input
                id="password"
                name="password"
                :label="tr('Password')"
                placeholder="••••••••"
                autocomplete="new-password" />
        </div>

        <div>
            <x-ui.password-input
                id="password_confirmation"
                name="password_confirmation"
                :label="tr('Confirm Password')"
                placeholder="••••••••"
                autocomplete="new-password" />

            <div id="password_match_error" class="text-xs text-[color:var(--error)] mt-1 hidden flex items-center gap-1">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor">
                    <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                </svg>
                <span>{{ function_exists('tr') ? tr('The passwords do not match.') : 'The passwords do not match.' }}</span>
            </div>
        </div>

        @error('email')
            <div class="text-xs text-[color:var(--error)]">{{ $message }}</div>
        @enderror

        @error('token')
            <div class="text-xs text-[color:var(--error)]">{{ $message }}</div>
        @enderror

        <x-ui.primary-button
            :arrow="false"
            :fullWidth="true"
            type="submit"
            alpine-loading="isSubmitting"
        >
            {{ tr('Set Password') }}
        </x-ui.primary-button>
    </form>

    <script>
        (function() {
            const form = document.getElementById('setPasswordForm');
            const passwordInput = document.getElementById('password');
            const passwordConfirmationInput = document.getElementById('password_confirmation');
            const errorDiv = document.getElementById('password_match_error');

            if (!form || !passwordInput || !passwordConfirmationInput || !errorDiv) {
                return;
            }

            function setSubmittingState(value) {
                if (form.__x && form.__x.$data) {
                    form.__x.$data.isSubmitting = value;
                }
            }

            function checkPasswordMatch() {
                const password = passwordInput.value;
                const passwordConfirmation = passwordConfirmationInput.value;

                if (passwordConfirmation.length === 0) {
                    errorDiv.classList.add('hidden');
                    passwordConfirmationInput.style.borderColor = '';
                    passwordConfirmationInput.classList.add('border-slate-200');
                    return true;
                }

                if (password !== passwordConfirmation) {
                    errorDiv.classList.remove('hidden');
                    passwordConfirmationInput.classList.remove('border-slate-200');
                    passwordConfirmationInput.style.borderColor = 'var(--error)';
                    return false;
                }

                errorDiv.classList.add('hidden');
                passwordConfirmationInput.style.borderColor = '';
                passwordConfirmationInput.classList.add('border-slate-200');
                return true;
            }

            passwordInput.addEventListener('input', checkPasswordMatch);
            passwordConfirmationInput.addEventListener('input', checkPasswordMatch);

            form.addEventListener('submit', function(e) {
                const isMatch = checkPasswordMatch();

                if (!isMatch) {
                    e.preventDefault();
                    setSubmittingState(false);
                    passwordConfirmationInput.focus();
                }
            });
        })();
    </script>

</x-authkit::ui.auth-shell>
