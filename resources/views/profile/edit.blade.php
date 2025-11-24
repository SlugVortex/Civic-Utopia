<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Profile & Settings') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">

            {{-- Tab Navigation --}}
            <ul class="nav nav-pills mb-3" id="pills-tab" role="tablist">
                <li class="nav-item" role="presentation">
                    <button class="nav-link active" id="pills-profile-tab" data-bs-toggle="pill" data-bs-target="#pills-profile" type="button" role="tab" aria-controls="pills-profile" aria-selected="true">Profile Information</button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="pills-appearance-tab" data-bs-toggle="pill" data-bs-target="#pills-appearance" type="button" role="tab" aria-controls="pills-appearance" aria-selected="false">Appearance</button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="pills-security-tab" data-bs-toggle="pill" data-bs-target="#pills-security" type="button" role="tab" aria-controls="pills-security" aria-selected="false">Security</button>
                </li>
            </ul>

            <div class="tab-content" id="pills-tabContent">
                {{-- Profile Information Tab --}}
                <div class="tab-pane fade show active" id="pills-profile" role="tabpanel" aria-labelledby="pills-profile-tab">
                    <div class="p-4 sm:p-8 bg-white dark:bg-gray-800 shadow sm:rounded-lg">
                        <div class="max-w-xl">
                            @include('profile.partials.update-profile-information-form')
                        </div>
                    </div>
                </div>

                {{-- Appearance Tab --}}
                <div class="tab-pane fade" id="pills-appearance" role="tabpanel" aria-labelledby="pills-appearance-tab">
                    <div class="p-4 sm:p-8 bg-white dark:bg-gray-800 shadow sm:rounded-lg">
                        <div class="max-w-xl">
                             <section>
                                <header>
                                    <h2 class="text-lg font-medium text-gray-900 dark:text-gray-100">
                                        {{ __('Appearance Settings') }}
                                    </h2>
                                    <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
                                        {{ __("Customize the look and feel of the application.") }}
                                    </p>
                                </header>

                                <div class="mt-6 space-y-6" id="appearance-settings-container">
                                    {{-- Theme (Light/Dark) --}}
                                    <div>
                                        <x-input-label value="{{ __('Theme') }}" />
                                        <div class="mt-2 space-y-2">
                                            <div class="form-check form-check-inline">
                                                <input class="form-check-input" type="radio" name="theme-radio" id="theme-light" value="light">
                                                <label class="form-check-label" for="theme-light">{{ __('Light') }}</label>
                                            </div>
                                            <div class="form-check form-check-inline">
                                                <input class="form-check-input" type="radio" name="theme-radio" id="theme-dark" value="dark">
                                                <label class="form-check-label" for="theme-dark">{{ __('Dark') }}</label>
                                            </div>
                                            <div class="form-check form-check-inline">
                                                <input class="form-check-input" type="radio" name="theme-radio" id="theme-system" value="system">
                                                <label class="form-check-label" for="theme-system">{{ __('System') }}</label>
                                            </div>
                                        </div>
                                    </div>

                                    {{-- Layout (Vertical/Horizontal) --}}
                                    <div>
                                        <x-input-label value="{{ __('Layout') }}" />
                                        <div class="mt-2 space-y-2">
                                            <div class="form-check form-check-inline">
                                                <input class="form-check-input" type="radio" name="layout-radio" id="layout-vertical" value="vertical">
                                                <label class="form-check-label" for="layout-vertical">{{ __('Vertical') }}</label>
                                            </div>
                                            <div class="form-check form-check-inline">
                                                <input class="form-check-input" type="radio" name="layout-radio" id="layout-horizontal" value="horizontal">
                                                <label class="form-check-label" for="layout-horizontal">{{ __('Horizontal') }}</label>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </section>
                        </div>
                    </div>
                </div>

                {{-- Security Tab --}}
                <div class="tab-pane fade" id="pills-security" role="tabpanel" aria-labelledby="pills-security-tab">
                     <div class="p-4 sm:p-8 bg-white dark:bg-gray-800 shadow sm:rounded-lg">
                        <div class="max-w-xl">
                            @include('profile.partials.update-password-form')
                        </div>
                    </div>
                    <div class="p-4 sm:p-8 bg-white dark:bg-gray-800 shadow sm:rounded-lg mt-6">
                        <div class="max-w-xl">
                            @include('profile.partials.delete-user-form')
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
    {{-- This script hooks into the template's existing customizer logic --}}
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Need to ensure the customizer script is loaded and ready
            setTimeout(() => {
                if (typeof templateCustomizer !== 'undefined') {
                    const settingsContainer = document.getElementById('appearance-settings-container');
                    if (!settingsContainer) return;

                    // --- Theme Handling ---
                    const currentTheme = templateCustomizer.settings.theme;
                    const themeRadio = settingsContainer.querySelector(`input[name="theme-radio"][value="${currentTheme}"]`);
                    if(themeRadio) themeRadio.checked = true;

                    settingsContainer.querySelectorAll('input[name="theme-radio"]').forEach(radio => {
                        radio.addEventListener('change', function() {
                            templateCustomizer.setStyle(this.value);
                        });
                    });

                    // --- Layout Handling ---
                    const currentLayout = templateCustomizer.settings.myLayout;
                    const layoutRadio = settingsContainer.querySelector(`input[name="layout-radio"][value="${currentLayout}"]`);
                    if (layoutRadio) {
                        layoutRadio.checked = true;
                    } else {
                        // Default to vertical if not set
                        const verticalRadio = settingsContainer.querySelector(`input[name="layout-radio"][value="vertical"]`);
                        if(verticalRadio) verticalRadio.checked = true;
                    }


                    settingsContainer.querySelectorAll('input[name="layout-radio"]').forEach(radio => {
                        radio.addEventListener('change', function() {
                            if (window.Helpers) {
                                window.Helpers.setlayout(this.value);
                            }
                        });
                    });

                } else {
                    console.error('templateCustomizer not found. Theme settings on profile page will not work.');
                }
            }, 500); // Delay to ensure template scripts are fully initialized
        });
    </script>
    @endpush
</x-app-layout>
