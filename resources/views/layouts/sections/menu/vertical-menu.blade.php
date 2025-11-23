@php
$configData = Helper::appClasses();
// Get the menu data from the JSON file
$menuData = file_get_contents(resource_path('menu/verticalMenu.json'));
$menuData = json_decode($menuData);
$verticalMenuData = $menuData->menu;
@endphp

<ul class="menu-inner py-1">
  @foreach ($verticalMenuData as $menu)

  {{-- Check if the menu item is a header --}}
  @if (isset($menu->menuHeader))
  <li class="menu-header small text-uppercase">
    <span class="menu-header-text">{{ __($menu->menuHeader) }}</span>
  </li>

  @else

  {{-- Handle regular menu items --}}
  @php
  $activeClass = null;
  $currentRouteName = Route::currentRouteName();

  if ($currentRouteName === $menu->slug) {
  $activeClass = 'active';
  }
  elseif (isset($menu->submenu)) {
  if (gettype($menu->slug) === 'array') {
  foreach($menu->slug as $slug){
  if (str_contains($currentRouteName,$slug) and strpos($currentRouteName,$slug) === 0) {
  $activeClass = 'active open';
  }
  }
  }
  else{
  if (str_contains($currentRouteName,$menu->slug) and strpos($currentRouteName,$menu->slug) === 0) {
  $activeClass = 'active open';
  }
  }
  }
  @endphp

  <li class="menu-item {{ $activeClass }}">
    <a href="{{ isset($menu->url) ? url($menu->url) : 'javascript:void(0);' }}" class="{{ isset($menu->submenu) ? 'menu-link menu-toggle' : 'menu-link' }}" @if (isset($menu->target) and !empty($menu->target)) target="_blank" @endif>
      @if (isset($menu->icon))
      <i class="{{ $menu->icon }}"></i>
      @endif
      <div>{{ isset($menu->name) ? __($menu->name) : '' }}</div>
      @if (isset($menu->badge))
      <div class="badge bg-{{ $menu->badge[0] }} rounded-pill ms-auto">{{ $menu->badge[1] }}</div>
      @endif
    </a>

    {{-- Handle submenu --}}
    @if (isset($menu->submenu))
    @include('layouts.sections.menu.submenu', ['menu' => $menu->submenu])
    @endif
  </li>
  @endif
  @endforeach
</ul>
