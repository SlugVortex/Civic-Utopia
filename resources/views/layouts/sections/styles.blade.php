<!-- BEGIN: Theme CSS-->
<!-- Fonts -->
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&ampdisplay=swap" rel="stylesheet">

@vite([
    // Hardcode the path to the base core and theme files.
    // Dark mode is handled by a data attribute on the <html> tag, not a different file.
    'resources/assets/vendor/scss/core.scss',
    'resources/assets/vendor/scss/theme-default.scss',

    // Other essential files
    'resources/assets/css/demo.css',
    'resources/assets/vendor/libs/node-waves/node-waves.scss',
    'resources/assets/vendor/libs/perfect-scrollbar/perfect-scrollbar.scss',
    'resources/assets/vendor/libs/typeahead-js/typeahead.scss',
    'resources/css/app.css'
])

@if ($configData['hasCustomizer'])
  @vite(['resources/assets/vendor/libs/pickr/pickr-themes.scss'])
@endif

<!-- Vendor Styles -->
@yield('vendor-style')

<!-- Page Styles -->
@yield('page-style')yield('page-style')
