<x-guest-layout>
    <!-- Session Status -->
    <x-auth-session-status class="mb-4" :status="session('status')" />

    <!-- Custom Logo Wrapper -->
    <div class="app-brand justify-content-center mb-4">
        <a href="{{ url('/') }}" class="app-brand-link gap-2">
            <span class="app-brand-logo demo">
                @include('_partials.macros', ["height" => 40, "width" => 76])
            </span>
            <span class="app-brand-text demo text-body fw-bold ms-1" style="font-size: 1.75rem;">
                {{ config('variables.templateName') }}
            </span>
        </a>
    </div>
    <!-- /Logo -->

    <h4 class="mb-1 pt-2">Welcome to {{ config('variables.templateName') }}! 👋</h4>
    <p class="mb-4">Please sign-in to your account and start the adventure</p>

    <form method="POST" action="{{ route('login') }}">
        @csrf

        <!-- Email Address -->
        <div class="mb-3">
            <label for="email" class="form-label">{{ __('Email') }}</label>
            <input id="email" class="form-control" type="email" name="email" :value="old('email')" required autofocus autocomplete="username" placeholder="Enter your email" />
            <x-input-error :messages="$errors->get('email')" class="mt-2" />
        </div>

        <!-- Password -->
        <div class="mb-3 form-password-toggle">
            <div class="d-flex justify-content-between">
                <label class="form-label" for="password">{{ __('Password') }}</label>
                @if (Route::has('password.request'))
                    <a href="{{ route('password.request') }}">
                        <small>{{ __('Forgot Password?') }}</small>
                    </a>
                @endif
            </div>
            <div class="input-group input-group-merge">
                <input type="password" id="password" class="form-control" name="password" required autocomplete="current-password" placeholder="&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;" />
                <span class="input-group-text cursor-pointer"><i class="ri-eye-off-line"></i></span>
            </div>
            <x-input-error :messages="$errors->get('password')" class="mt-2" />
        </div>

        <!-- Remember Me -->
        <div class="mb-3">
            <div class="form-check">
                <input class="form-check-input" type="checkbox" id="remember-me" name="remember">
                <label class="form-check-label" for="remember-me">
                    {{ __('Remember Me') }}
                </label>
            </div>
        </div>

        <button class="btn btn-primary d-grid w-100">
            {{ __('Log in') }}
        </button>
    </form>

    <p class="text-center mt-3">
        <span>New on our platform?</span>
        <a href="{{ route('register') }}">
            <span>Create an account</span>
        </a>
    </p>

    <!-- NOTE: Social Media Icons have been removed as requested -->

</x-guest-layout>
