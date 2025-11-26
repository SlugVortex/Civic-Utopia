@php
  $configData = Helper::appClasses();
  $customizerHidden = 'customizer-hide';
@endphp

@extends('layouts/layoutMaster')

@section('title', 'Register - CivicUtopia')

@section('vendor-style')
  @vite(['resources/assets/vendor/libs/@form-validation/form-validation.scss'])
@endsection

@section('page-style')
  @vite(['resources/assets/vendor/scss/pages/page-auth.scss'])
@endsection

@section('vendor-script')
  @vite(['resources/assets/vendor/libs/@form-validation/popular.js', 'resources/assets/vendor/libs/@form-validation/bootstrap5.js', 'resources/assets/vendor/libs/@form-validation/auto-focus.js'])
@endsection

@section('page-script')
  {{-- Custom template script disabled to prevent conflicts --}}
@endsection

@section('content')
<div class="position-relative">
  <div class="authentication-wrapper authentication-basic container-p-y p-4 p-sm-0">
    <div class="authentication-inner py-6">

      <!-- Register Card -->
      <div class="card p-md-7 p-1">
        <!-- Logo -->
        <div class="app-brand justify-content-center mt-5">
          <a href="{{ url('/') }}" class="app-brand-link gap-2">
            <span class="app-brand-logo demo">
                 <img src="{{ asset('assets/img/favicon/logo.png') }}" alt="Logo" style="width: 50px; height: auto;">
            </span>
            <span class="app-brand-text demo text-heading fw-semibold">{{ config('variables.templateName') }}</span>
          </a>
        </div>
        <!-- /Logo -->
        <div class="card-body mt-1">
          <h4 class="mb-1">Adventure starts here 🚀</h4>
          <p class="mb-5">Make your app management easy and fun!</p>

          <form id="formAuthentication" class="mb-5" action="{{ route('register') }}" method="POST">
            @csrf

            <div class="form-floating form-floating-outline mb-5">
              <input type="text" class="form-control @error('name') is-invalid @enderror" id="name" name="name" placeholder="Enter your username" value="{{ old('name') }}" autofocus>
              <label for="name">Username</label>
              @error('name')
                <div class="invalid-feedback">{{ $message }}</div>
              @enderror
            </div>

            <div class="form-floating form-floating-outline mb-5">
              <input type="text" class="form-control @error('email') is-invalid @enderror" id="email" name="email" placeholder="Enter your email" value="{{ old('email') }}">
              <label for="email">Email</label>
               @error('email')
                <div class="invalid-feedback">{{ $message }}</div>
              @enderror
            </div>

            <!-- Password Field (FIXED) -->
            <div class="mb-5">
              <div class="input-group input-group-merge">
                <div class="form-floating form-floating-outline @error('password') is-invalid @enderror">
                  <input type="password" id="password" class="form-control" name="password" placeholder="&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;" />
                  <label for="password">Password</label>
                </div>
                {{-- Replaced span with explicit button and high z-index --}}
                <button type="button" class="input-group-text px-3" id="btn-toggle-password" style="cursor: pointer; z-index: 100; background: transparent; border-left: none;">
                    <i class="icon-base ri ri-eye-off-line icon-20px text-muted"></i>
                </button>
              </div>
               @error('password')
                <div class="invalid-feedback d-block">{{ $message }}</div>
              @enderror
            </div>

            <!-- Confirm Password Field (FIXED) -->
            <div class="mb-5">
              <div class="input-group input-group-merge">
                <div class="form-floating form-floating-outline">
                  <input type="password" id="password_confirmation" class="form-control" name="password_confirmation" placeholder="&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;" />
                  <label for="password_confirmation">Confirm Password</label>
                </div>
                {{-- Replaced span with explicit button and high z-index --}}
                <button type="button" class="input-group-text px-3" id="btn-toggle-confirm" style="cursor: pointer; z-index: 100; background: transparent; border-left: none;">
                    <i class="icon-base ri ri-eye-off-line icon-20px text-muted"></i>
                </button>
              </div>
            </div>

            <div class="mb-5">
              <div class="form-check @error('terms') is-invalid @enderror">
                <input class="form-check-input" type="checkbox" id="terms" name="terms" />
                <label class="form-check-label" for="terms">
                  I agree to the
                  <a href="{{ route('pages.privacy') }}" target="_blank">privacy policy</a> &
                  <a href="{{ route('pages.terms') }}" target="_blank">terms</a>
                </label>
              </div>
              @error('terms')
                <div class="invalid-feedback d-block">{{ $message }}</div>
              @enderror
            </div>
            <button class="btn btn-primary d-grid w-100" type="submit">
              Sign up
            </button>
          </form>

          <p class="text-center">
            <span>Already have an account?</span>
            <a href="{{ route('login') }}">
              <span>Sign in instead</span>
            </a>
          </p>
        </div>
      </div>
      <!-- /Register Card -->
        <img alt="mask" src="{{asset('assets/img/illustrations/auth-basic-register-mask-'.$configData['theme'].'.png')}}" class="authentication-image d-none d-lg-block" data-app-light-img="illustrations/auth-basic-register-mask-light.png" data-app-dark-img="illustrations/auth-basic-register-mask-dark.png" />
    </div>
  </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    // Function to toggle password visibility
    function setupToggle(btnId, inputId) {
        const btn = document.getElementById(btnId);
        const input = document.getElementById(inputId);

        if(btn && input) {
            btn.addEventListener('click', function(e) {
                // Prevent form submission just in case
                e.preventDefault();
                e.stopPropagation();

                const icon = this.querySelector('i');

                if (input.type === 'password') {
                    input.type = 'text';
                    icon.classList.remove('ri-eye-off-line');
                    icon.classList.add('ri-eye-line');
                    icon.classList.remove('text-muted');
                    icon.classList.add('text-primary');
                } else {
                    input.type = 'password';
                    icon.classList.remove('ri-eye-line');
                    icon.classList.add('ri-eye-off-line');
                    icon.classList.remove('text-primary');
                    icon.classList.add('text-muted');
                }
            });
        }
    }

    // Setup listeners
    setupToggle('btn-toggle-password', 'password');
    setupToggle('btn-toggle-confirm', 'password_confirmation');
});
</script>
@endsection
