@isset($pageConfigs)
{!! Helper::updatePageConfig($pageConfigs) !!}
@endisset
@php
$configData = Helper::appClasses();
@endphp
<!DOCTYPE html>

<html lang="{{ session()->get('locale') ?? app()->getLocale() }}" class="light-style layout-navbar-fixed layout-menu-fixed {{ $configData['theme'] }}" dir="{{ $configData['textDirection'] }}" data-theme="{{ $configData['theme'] }}" data-assets-path="{{ asset('/assets/') . '/' }}" data-base-url="{{url('/')}}" data-framework="laravel" data-template="vertical-menu-laravel-template-starter">

<head>
  <!-- PWA Manifest -->
  <link rel="manifest" href="/manifest.webmanifest">
  <meta name="theme-color" content="#666cff" />
  <link rel="apple-touch-icon" href="/assets/img/pwa/apple-touch-icon.png">

  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=no, minimum-scale=1.0, maximum-scale=1.0" />

  <title>@yield('title') | {{ config('variables.templateName') ? config('variables.templateName') : 'TemplateName' }}</title>
  <meta name="description" content="{{ config('variables.templateDescription') ? config('variables.templateDescription') : '' }}" />
  <meta name="keywords" content="{{ config('variables.templateKeyword') ? config('variables.templateKeyword') : '' }}">
  <meta name="csrf-token" content="{{ csrf_token() }}">
  <link rel="canonical" href="{{ config('variables.productPage') ? config('variables.productPage') : '' }}">
  <link rel="icon" type="image/x-icon" href="{{ asset('assets/img/favicon/favicon.ico') }}" />

  {{-- Include Styles --}}
  @include('layouts/sections/styles')

  {{--! START: ADDED FOR ICONS --}}
  <link href="https://cdn.jsdelivr.net/npm/remixicon@4.2.0/fonts/remixicon.css" rel="stylesheet" />
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@tabler/icons-webfont@latest/tabler-icons.min.css">
  {{--! END: ADDED FOR ICONS --}}

  {{-- Include Scripts for head --}}
  @include('layouts/sections/scriptsIncludes')
</head>

<body>
  <!-- Layout wrapper -->
  <div class="layout-wrapper layout-content-navbar">
    <div class="layout-container">

      <!-- Menu -->
      <aside id="layout-menu" class="layout-menu menu-vertical menu bg-menu-theme">
        <div class="app-brand demo" style="padding: 1.5rem 1.5rem;">
          <a href="{{url('/')}}" class="app-brand-link d-flex align-items-center">
            <img src="{{ asset('assets/img/favicon/logo.png') }}" alt="Logo" style="max-height: 45px; width: auto;">
            <span class="app-brand-text demo menu-text fw-semibold ms-3">Civic Utopia</span>
          </a>
          <a href="javascript:void(0);" class="layout-menu-toggle menu-link text-large ms-auto">
            <i class="ri-close-fill align-middle ri-20px"></i>
          </a>
        </div>
        <div class="menu-inner-shadow"></div>

        {{-- This includes the sidebar menu --}}
        @include('layouts.sections.menu.vertical-menu')

      </aside>
      <!-- / Menu -->

      <!-- Layout container -->
      <div class="layout-page">
        <!-- Navbar -->
        @include('layouts.sections.navbar.navbar')
        <!-- / Navbar -->

        <!-- Content wrapper -->
        <div class="content-wrapper">
          <!-- Content -->
          <div class="container-xxl flex-grow-1 container-p-y">
            @yield('content')
          </div>
          <!-- / Content -->

          <!-- Footer -->
          @include('layouts.sections.footer.footer')
          <!-- / Footer -->

          <div class="content-backdrop fade"></div>
        </div>
        <!-- Content wrapper -->
      </div>
      <!-- / Layout page -->
    </div>

    <!-- Overlay -->
    <div class="layout-overlay layout-menu-toggle"></div>
    <!-- Drag Target Area To SlideIn Menu On Small Screens -->
    <div class="drag-target"></div>
  </div>
  <!-- / Layout wrapper -->

  @include('layouts/sections/scripts')
  @stack('scripts')
</body>
</html>
