@php
$configData = Helper::appClasses();
@endphp
<!-- Customizer -->
<aside id="customizer" class="internal-customizer customizer">
  <a href="javascript:void(0)" class="customizer-close">
    <i class="ri-close-line ri-22px"></i>
  </a>
  <div class="customizer-header px-4 pt-4">
    <h5 class="m-0">Theme Customizer</h5>
    <small class="text-muted">Customize & Preview in Real Time</small>
  </div>
  <div class="customizer-inner">
    <div class="customizer-t-panel">
      <!-- Theme -->
      <div class="p-4">
        <h6 class="mb-2">Theme</h6>
        <div class="row row-cols-2 g-3">
          <div class="col">
            <div class="form-check-label w-100">
              <input name="theme" class="form-check-input" type="radio" value="light" id="theme-light"
                {{ $configData['style'] === 'light' ? 'checked' : '' }}>
              <span class="form-check-label d-flex flex-column gap-2 align-items-center">
                <span
                  class="d-flex w-100 h-100 bg-label-secondary rounded p-3 justify-content-center align-items-center">
                  <i class="ri-sun-line ri-30px"></i>
                </span>
                <span class="fs-sm">Light</span>
              </span>
            </div>
          </div>
          <div class="col">
            <div class="form-check-label w-100">
              <input name="theme" class="form-check-input" type="radio" value="dark" id="theme-dark"
                {{ $configData['style'] === 'dark' ? 'checked' : '' }}>
              <span class="form-check-label d-flex flex-column gap-2 align-items-center">
                <span
                  class="d-flex w-100 h-100 bg-label-secondary rounded p-3 justify-content-center align-items-center">
                  <i class="ri-moon-clear-line ri-30px"></i>
                </span>
                <span class="fs-sm">Dark</span>
              </span>
            </div>
          </div>
        </div>
      </div>
      <!-- Direction -->
      <div class="p-4">
        <h6 class="mb-2">Direction</h6>
        <div class="row row-cols-2 g-3">
          <div class="col">
            <div class="form-check-label w-100">
              {{-- THIS IS THE FIX: Changed href to javascript:void(0); --}}
              <a href="javascript:void(0);" class="form-check-label d-flex flex-column gap-2 align-items-center"
                data-text-direction="ltr">
                <span
                  class="d-flex w-100 h-100 bg-label-secondary rounded p-3 justify-content-center align-items-center">
                  <i class="ri-text-direction-l ri-30px"></i>
                </span>
                <span class="fs-sm">LTR</span>
              </a>
            </div>
          </div>
          <div class="col">
            <div class="form-check-label w-100">
              <a href="javascript:void(0);" class="form-check-label d-flex flex-column gap-2 align-items-center"
                data-text-direction="rtl">
                <span
                  class="d-flex w-100 h-100 bg-label-secondary rounded p-3 justify-content-center align-items-center">
                  <i class="ri-text-direction-r ri-30px"></i>
                </span>
                <span class="fs-sm">RTL</span>
              </a>
            </div>
          </div>
        </div>
      </div>
      <!-- Skins -->
      <div class="p-4">
        <h6 class="mb-2">Skins</h6>
        <div class="row row-cols-2 g-3">
          <div class="col">
            <div class="form-check-label w-100">
              <input name="skins" class="form-check-input" type="radio" value="default" id="skins-default"
                {{ $configData['theme'] === 'theme-default' ? 'checked' : '' }}>
              <span class="form-check-label d-flex flex-column gap-2 align-items-center">
                <span
                  class="d-flex w-100 h-100 bg-label-secondary rounded p-3 justify-content-center align-items-center">
                  <i class="ri-checkbox-blank-line ri-30px"></i>
                </span>
                <span class="fs-sm">Default</span>
              </span>
            </div>
          </div>
          <div class="col">
            <div class="form-check-label w-100">
              <input name="skins" class="form-check-input" type="radio" value="bordered" id="skins-bordered"
                {{ $configData['theme'] === 'theme-bordered' ? 'checked' : '' }}>
              <span class="form-check-label d-flex flex-column gap-2 align-items-center">
                <span
                  class="d-flex w-100 h-100 bg-label-secondary rounded p-3 justify-content-center align-items-center">
                  <i class="ri-checkbox-blank-line ri-30px Dotted-border"></i>
                </span>
                <span class="fs-sm">Bordered</span>
              </span>
            </div>
          </div>
        </div>
      </div>
      <!-- Semidark -->
      <div class="p-4">
        <div class="form-check form-switch">
          <input class="form-check-input" type="checkbox" id="semi-dark"
            {{ $configData['style'] === 'dark' ? '' : 'checked' }}>
          <label class="form-check-label" for="semi-dark">Semi Dark</label>
        </div>
      </div>
    </div>
    <hr class="m-0">
    <div class="customizer-layout">
      <!-- Layout -->
      <div class="p-4">
        <h6 class="mb-2">Layout</h6>
        <div class="row row-cols-2 g-3">
          <div class="col">
            <div class="form-check-label w-100">
              <input name="layout" class="form-check-input" type="radio" value="vertical" id="layout-vertical"
                {{ $configData['layout'] === 'vertical' ? 'checked' : '' }}>
              <span class="form-check-label d-flex flex-column gap-2 align-items-center">
                <span class="d-flex w-100 h-100 bg-label-secondary rounded p-3 justify-content-center align-items-center">
                  <i class="ri-layout-4-line ri-30px"></i>
                </span>
                <span class="fs-sm">Vertical</span>
              </span>
            </div>
          </div>
          <div class="col">
            <div class="form-check-label w-100">
              <input name="layout" class="form-check-input" type="radio" value="horizontal" id="layout-horizontal"
                {{ $configData['layout'] === 'horizontal' ? 'checked' : '' }}>
              <span class="form-check-label d-flex flex-column gap-2 align-items-center">
                <span class="d-flex w-100 h-100 bg-label-secondary rounded p-3 justify-content-center align-items-center">
                  <i class="ri-layout-line ri-30px"></i>
                </span>
                <span class="fs-sm">Horizontal</span>
              </span>
            </div>
          </div>
        </div>
      </div>
      <!-- layout-navbar-options -->
      <div class="p-4">
        <h6 class="mb-2">Layout Navbar Options</h6>
        <div class="row row-cols-2 g-3">
          <div class="col">
            <div class="form-check-label w-100">
              <input name="layout-navbar-options" class="form-check-input" type="radio" value="sticky"
                id="layout-navbar-sticky" {{ $configData['navbarType'] === 'fixed' ? 'checked' : '' }}>
              <span class="form-check-label d-flex flex-column gap-2 align-items-center">
                <span
                  class="d-flex w-100 h-100 bg-label-secondary rounded p-3 justify-content-center align-items-center">
                  <i class="ri-upload-line ri-30px"></i>
                </span>
                <span class="fs-sm">Sticky</span>
              </span>
            </div>
          </div>
          <div class="col">
            <div class="form-check-label w-100">
              <input name="layout-navbar-options" class="form-check-input" type="radio" value="static"
                id="layout-navbar-static" {{ $configData['navbarType'] === 'static' ? 'checked' : '' }}>
              <span class="form-check-label d-flex flex-column gap-2 align-items-center">
                <span
                  class="d-flex w-100 h-100 bg-label-secondary rounded p-3 justify-content-center align-items-center">
                  <i class="ri-download-line ri-30px"></i>
                </span>
                <span class="fs-sm">Static</span>
              </span>
            </div>
          </div>
        </div>
      </div>
      <!-- content-layout -->
      <div class="p-4">
        <h6 class="mb-2">Content</h6>
        <div class="row row-cols-2 g-3">
          <div class="col">
            <div class="form-check-label w-100">
              <input name="content-layout" class="form-check-input" type="radio" value="compact"
                id="content-compact" {{ $configData['contentWidth'] === 'compact' ? 'checked' : '' }}>
              <span class="form-check-label d-flex flex-column gap-2 align-items-center">
                <span
                  class="d-flex w-100 h-100 bg-label-secondary rounded p-3 justify-content-center align-items-center">
                  <i class="ri-fullscreen-exit-line ri-30px"></i>
                </span>
                <span class="fs-sm">Compact</span>
              </span>
            </div>
          </div>
          <div class="col">
            <div class="form-check-label w-100">
              <input name="content-layout" class="form-check-input" type="radio" value="wide" id="content-wide"
                {{ $configData['contentWidth'] === 'fluid' ? 'checked' : '' }}>
              <span class="form-check-label d-flex flex-column gap-2 align-items-center">
                <span
                  class="d-flex w-100 h-100 bg-label-secondary rounded p-3 justify-content-center align-items-center">
                  <i class="ri-fullscreen-line ri-30px"></i>
                </span>
                <span class="fs-sm">Wide</span>
              </span>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
  <div class="offcanvas-footer border-top p-4">
    <a href="https://themeselection.com/item/materialize-html-laravel-admin-template/" target="_blank"
      class="btn btn-primary d-grid w-100">Get PRO</a>
  </div>
</aside>
<!--/ Customizer -->
