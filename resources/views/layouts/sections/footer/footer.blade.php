@php
  $containerFooter =
      isset($configData['contentLayout']) && $configData['contentLayout'] === 'compact'
          ? 'container-xxl'
          : 'container-fluid';
@endphp

<!-- Footer-->
<footer class="content-footer footer bg-footer-theme">
  <div class="{{ $containerFooter }}">
    <div class="footer-container d-flex align-items-center justify-content-between py-4 flex-md-row flex-column">
      <div class="mb-2 mb-md-0">
        &#169;
        <script>
          document.write(new Date().getFullYear());
        </script>
         Developed by the Utopians<a href="{{ !empty(config('variables.creatorUrl')) ? config('variables.creatorUrl') : '' }}"
          target="_blank"
          class="footer-link fw-medium">{{ !empty(config('variables.creatorName')) ? config('variables.creatorName') : '' }}</a>
      </div>
      <div class="d-none d-lg-inline-block">
        <a href="{{ config('variables.support') ? config('variables.support') : '#' }}" target="_blank"
          class="footer-link d-none d-sm-inline-block">Privacy and Terms of Service</a>
      </div>
    </div>
  </div>
</footer>
<!-- / Footer -->
