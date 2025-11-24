<!-- BEGIN: Core JS-->
<!-- Core JS -->
@vite([
  'resources/assets/vendor/js/helpers.js'
])

{{-- Customizer scripts have been removed from this file. --}}
@vite([
    'resources/assets/js/config.js'
])

<!-- END: Core JS-->
<!-- BEGIN: Vendor JS-->
@vite([
  'resources/assets/vendor/libs/jquery/jquery.js',
  'resources/assets/vendor/libs/popper/popper.js',
  'resources/assets/vendor/js/bootstrap.js',
  'resources/assets/vendor/libs/node-waves/node-waves.js',
  'resources/assets/vendor/libs/perfect-scrollbar/perfect-scrollbar.js',
  'resources/assets/vendor/libs/hammer/hammer.js',
  'resources/assets/vendor/libs/typeahead-js/typeahead.js',
  'resources/assets/vendor/js/menu.js',
  'resources/assets/vendor/libs/pickr/pickr.js' {{-- We keep pickr for the profile page --}}
])

<!-- END: Page Vendor JS-->

<!-- BEGIN: Theme JS-->
@vite(['resources/assets/js/main.js'])
<!-- END: Theme JS-->

<!-- Pricing Modal JS-->
@stack('pricing-script')
<!-- END: Pricing Modal JS-->

<!-- BEGIN: Page JS-->
@yield('page-script')
<!-- END: Page JS-->

<!-- app JS -->
{{-- We include our app.js here, which contains Echo configuration and other custom scripts --}}
@vite(['resources/js/app.js'])
<!-- END: app JS-->
