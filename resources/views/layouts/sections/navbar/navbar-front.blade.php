@php
use Illuminate\Support\Facades\Route;
$currentRouteName = Route::currentRouteName();
$activeRoutes = ['front-pages-pricing', 'front-pages-payment', 'front-pages-checkout', 'front-pages-help-center'];
$activeClass = in_array($currentRouteName, $activeRoutes) ? 'active' : '';
@endphp

@section('vendor-script')
@vite(['resources/assets/vendor/js/dropdown-hover.js', 'resources/assets/vendor/js/mega-dropdown.js'])
@endsection

<!-- Navbar: Start -->
<nav class="layout-navbar shadow-none py-0">
  <div class="container">
    <div class="navbar navbar-expand-lg landing-navbar px-3 px-md-8">
      <!-- Menu logo wrapper: Start -->
      <div class="navbar-brand app-brand demo d-flex py-0 me-4 me-xl-8">
        <!-- Mobile menu toggle: Start-->
        <button class="navbar-toggler border-0 px-0 me-4" type="button" data-bs-toggle="collapse" data-bs-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation">
          <i class="icon-base ri ri-menu-fill icon-lg align-middle text-heading fw-medium"></i>
        </button>
        <!-- Mobile menu toggle: End-->
        <a href="{{ url('front-pages/landing') }}" class="app-brand-link">
          <span class="app-brand-logo demo">@include('_partials.macros')</span>
          <span class="app-brand-text demo menu-text fw-semibold ms-2 ps-1">{{ config('variables.templateName') }}</span>
        </a>
      </div>
      <!-- Menu logo wrapper: End -->
      <!-- Menu wrapper: Start -->
      <div class="collapse navbar-collapse landing-nav-menu" id="navbarSupportedContent">
        <button class="navbar-toggler border-0 text-heading position-absolute end-0 top-0 p-2" type="button" data-bs-toggle="collapse" data-bs-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation">
          <i class="icon-base ri ri-close-fill"></i>
        </button>
        <ul class="navbar-nav me-auto">
          <li class="nav-item">
            <a class="nav-link fw-medium" aria-current="page" href="{{ url('front-pages/landing') }}#landingHero">Home</a>
          </li>
          <li class="nav-item">
            <a class="nav-link fw-medium" href="{{ url('front-pages/landing') }}#landingFeatures">Features</a>
          </li>
          <li class="nav-item">
            <a class="nav-link fw-medium" href="{{ url('front-pages/landing') }}#landingTeam">Team</a>
          </li>
          <li class="nav-item">
            <a class="nav-link fw-medium" href="{{ url('front-pages/landing') }}#landingFAQ">FAQ</a>
          </li>
          <li class="nav-item">
            <a class="nav-link fw-medium" href="{{ url('front-pages/landing') }}#landingContact">Contact us</a>
          </li>
          <li class="nav-item mega-dropdown {{ $activeClass }}">
            <a href="javascript:void(0);" class="nav-link dropdown-toggle navbar-ex-14-mega-dropdown mega-dropdown fw-medium" aria-expanded="false" data-bs-toggle="mega-dropdown" data-trigger="hover">
              <span>Pages</span>
            </a>
            <div class="dropdown-menu p-4 p-xl-8">
              <div class="row gy-4">
                <div class="col-12 col-lg">
                  <div class="h6 d-flex align-items-center mb-2 mb-lg-4">
                    <div class="avatar avatar-sm flex-shrink-0 me-2">
                      <span class="avatar-initial rounded bg-label-primary"><i class="icon-base ri ri-layout-grid-line"></i></span>
                    </div>
                    <span class="ps-1">Other</span>
                  </div>
                  <ul class="nav flex-column">
                    <li class="nav-item {{ $currentRouteName === 'front-pages-pricing' ? 'active' : '' }}">
                      <a class="nav-link mega-dropdown-link" href="{{ url('front-pages/pricing') }}">
                        <i class="icon-base ri ri-circle-line icon-12px me-2"></i>
                        <span>Pricing</span>
                      </a>
                    </li>
                    <li class="nav-item {{ $currentRouteName === 'front-pages-payment' ? 'active' : '' }}">
                      <a class="nav-link mega-dropdown-link" href="{{ url('front-pages/payment') }}">
                        <i class="icon-base ri ri-circle-line icon-12px me-2"></i>
                        <span>Payment</span>
                      </a>
                    </li>
                    <li class="nav-item {{ $currentRouteName === 'front-pages-checkout' ? 'active' : '' }}">
                      <a class="nav-link mega-dropdown-link" href="{{ url('front-pages/checkout') }}">
                        <i class="icon-base ri ri-circle-line icon-12px me-2"></i>
                        <span>Checkout</span>
                      </a>
                    </li>
                    <li class="nav-item {{ $currentRouteName === 'front-pages-help-center' ? 'active' : '' }}">
                      <a class="nav-link mega-dropdown-link" href="{{ url('front-pages/help-center') }}">
                        <i class="icon-base ri ri-circle-line icon-12px me-2"></i>
                        <span>Help Center</span>
                      </a>
                    </li>
                  </ul>
                </div>
                <div class="col-12 col-lg">
                  <div class="h6 d-flex align-items-center mb-2 mb-lg-4">
                    <div class="avatar avatar-sm flex-shrink-0 me-2">
                      <span class="avatar-initial rounded bg-label-primary"><i class="icon-base ri ri-lock-unlock-line"></i></span>
                    </div>
                    <span class="ps-1">Auth Demo</span>
                  </div>
                  <ul class="nav flex-column">
                    <li class="nav-item">
                      <a class="nav-link mega-dropdown-link" href="{{ url('/auth/login-basic') }}" target="_blank">
                        <i class="icon-base ri ri-circle-line icon-12px me-2"></i>
                        Login (Basic)
                      </a>
                    </li>
                    <li class="nav-item">
                      <a class="nav-link mega-dropdown-link" href="{{ url('/auth/login-cover') }}" target="_blank">
                        <i class="icon-base ri ri-circle-line icon-12px me-2"></i>
                        Login (Cover)
                      </a>
                    </li>
                    <li class="nav-item">
                      <a class="nav-link mega-dropdown-link" href="{{ url('/auth/register-basic') }}" target="_blank">
                        <i class="icon-base ri ri-circle-line icon-12px me-2"></i>
                        Register (Basic)
                      </a>
                    </li>
                    <li class="nav-item">
                      <a class="nav-link mega-dropdown-link" href="{{ url('/auth/register-cover') }}" target="_blank">
                        <i class="icon-base ri ri-circle-line icon-12px me-2"></i>
                        Register (Cover)
                      </a>
                    </li>
                    <li class="nav-item">
                      <a class="nav-link mega-dropdown-link" href="{{ url('/auth/register-multisteps') }}" target="_blank">
                        <i class="icon-base ri ri-circle-line icon-12px me-2"></i>
                        Register (Multi-steps)
                      </a>
                    </li>
                    <li class="nav-item">
                      <a class="nav-link mega-dropdown-link" href="{{ url('/auth/forgot-password-basic') }}" target="_blank">
                        <i class="icon-base ri ri-circle-line icon-12px me-2"></i>
                        Forgot Password (Basic)
                      </a>
                    </li>
                    <li class="nav-item">
                      <a class="nav-link mega-dropdown-link" href="{{ url('/auth/forgot-password-cover') }}" target="_blank">
                        <i class="icon-base ri ri-circle-line icon-12px me-2"></i>
                        Forgot Password (Cover)
                      </a>
                    </li>
                    <li class="nav-item">
                      <a class="nav-link mega-dropdown-link" href="{{ url('/auth/reset-password-basic') }}" target="_blank">
                        <i class="icon-base ri ri-circle-line icon-12px me-2"></i>
                        Reset Password (Basic)
                      </a>
                    </li>
                    <li class="nav-item">
                      <a class="nav-link mega-dropdown-link" href="{{ url('/auth/reset-password-cover') }}" target="_blank">
                        <i class="icon-base ri ri-circle-line icon-12px me-2"></i>
                        Reset Password (Cover)
                      </a>
                    </li>
                  </ul>
                </div>
                <div class="col-12 col-lg">
                  <div class="h6 d-flex align-items-center mb-2 mb-lg-4">
                    <div class="avatar avatar-sm flex-shrink-0 me-2">
                      <span class="avatar-initial rounded bg-label-primary"><i class="icon-base ri ri-image-fill"></i></span>
                    </div>
                    <span class="ps-1">Other</span>
                  </div>
                  <ul class="nav flex-column">
                    <li class="nav-item">
                      <a class="nav-link mega-dropdown-link" href="{{ url('/pages/misc-error') }}" target="_blank">
                        <i class="icon-base ri ri-circle-line icon-12px me-2"></i>
                        Error
                      </a>
                    </li>
                    <li class="nav-item">
                      <a class="nav-link mega-dropdown-link" href="{{ url('/pages/misc-under-maintenance') }}" target="_blank">
                        <i class="icon-base ri ri-circle-line icon-12px me-2"></i>
                        Under Maintenance
                      </a>
                    </li>
                    <li class="nav-item">
                      <a class="nav-link mega-dropdown-link" href="{{ url('/pages/misc-comingsoon') }}" target="_blank">
                        <i class="icon-base ri ri-circle-line icon-12px me-2"></i>
                        Coming Soon
                      </a>
                    </li>
                    <li class="nav-item">
                      <a class="nav-link mega-dropdown-link" href="{{ url('/pages/misc-not-authorized') }}" target="_blank">
                        <i class="icon-base ri ri-circle-line icon-12px me-2"></i>
                        Not Authorized
                      </a>
                    </li>
                    <li class="nav-item">
                      <a class="nav-link mega-dropdown-link" href="{{ url('/auth/verify-email-basic') }}" target="_blank">
                        <i class="icon-base ri ri-circle-line icon-12px me-2"></i>
                        Verify Email (Basic)
                      </a>
                    </li>
                    <li class="nav-item">
                      <a class="nav-link mega-dropdown-link" href="{{ url('/auth/verify-email-cover') }}" target="_blank">
                        <i class="icon-base ri ri-circle-line icon-12px me-2"></i>
                        Verify Email (Cover)
                      </a>
                    </li>
                    <li class="nav-item">
                      <a class="nav-link mega-dropdown-link" href="{{ url('/auth/two-steps-basic') }}" target="_blank">
                        <i class="icon-base ri ri-circle-line icon-12px me-2"></i>
                        Two Steps (Basic)
                      </a>
                    </li>
                    <li class="nav-item">
                      <a class="nav-link mega-dropdown-link" href="{{ url('/auth/two-steps-cover') }}" target="_blank">
                        <i class="icon-base ri ri-circle-line icon-12px me-2"></i>
                        Two Steps (Cover)
                      </a>
                    </li>
                  </ul>
                </div>
                <div class="col-lg-4 d-none d-lg-block">
                  <div class="bg-body nav-img-col p-2">
                    <img src="{{ asset('assets/img/front-pages/misc/nav-item-col-img-'.$configData['theme'].'.png') }}" class="img-fluid scaleX-n1-rtl w-100" alt="nav item col image" data-app-light-img="front-pages/misc/nav-item-col-img-light.png" data-app-dark-img="front-pages/misc/nav-item-col-img-dark.png" />
                  </div>
                </div>
              </div>
            </div>
          </li>
          <li class="nav-item">
            <a class="nav-link fw-medium" href="{{ url('/') }}" target="_blank">Admin</a>
          </li>
        </ul>
      </div>
      <div class="landing-menu-overlay d-lg-none"></div>
      <!-- Menu wrapper: End -->
      <!-- Toolbar: Start -->
      <ul class="navbar-nav flex-row align-items-center ms-auto">
        @if ($configData['hasCustomizer'] == true)
          <!-- Style Switcher -->
          <li class="nav-item dropdown-style-switcher dropdown me-2 me-xl-0">
            <a class="nav-link dropdown-toggle hide-arrow me-sm-2" id="nav-theme" href="javascript:void(0);" data-bs-toggle="dropdown">
              <i class="icon-base ri ri-sun-line icon-24px theme-icon-active"></i>
              <span class="d-none ms-2" id="nav-theme-text">Toggle theme</span>
            </a>
            <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="nav-theme-text">
              <li>
                <button type="button" class="dropdown-item align-items-center active" data-bs-theme-value="light" aria-pressed="false">
                  <span><i class="icon-base ri ri-sun-line icon-24px me-3" data-icon="sun-line"></i>Light</span>
                </button>
              </li>
              <li>
                <button type="button" class="dropdown-item align-items-center" data-bs-theme-value="dark" aria-pressed="true">
                  <span><i class="icon-base ri ri-moon-clear-line icon-24px me-3" data-icon="moon-clear-line"></i>Dark</span>
                </button>
              </li>
              <li>
                <button type="button" class="dropdown-item align-items-center" data-bs-theme-value="system" aria-pressed="false">
                  <span><i class="icon-base ri ri-computer-line icon-24px me-3" data-icon="computer-line"></i>System</span>
                </button>
              </li>
            </ul>
          </li>
          <!-- / Style Switcher-->
        @endif

        <!-- navbar button: Start -->
        <li>
          <a href="{{ url('/auth/login-cover') }}" class="btn btn-primary px-2 px-sm-4 px-lg-2 px-xl-4" target="_blank"><span class="icon-base ri ri-user-line me-md-1"></span><span class="d-none d-md-block">Login/Register</span></a>
        </li>
        <!-- navbar button: End -->
      </ul>
      <!-- Toolbar: End -->
    </div>
  </div>
</nav>
<!-- Navbar: End -->
