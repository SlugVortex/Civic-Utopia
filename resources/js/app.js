// This file is essential for loading our Echo configuration
import './bootstrap';

// Your template's default JavaScript (e.g., Alpine) can remain
import Alpine from 'alpinejs';

import { registerSW } from 'virtual:pwa-register';

window.Alpine = Alpine;

Alpine.start();

// We have REMOVED the Vue-related code from here.
// No 'createApp' or 'app.mount' calls are needed.
if ('serviceWorker' in navigator) {
    registerSW({
        immediate: true,
        onNeedRefresh() {
            console.log('New content available, refresh to update.');
        },
        onOfflineReady() {
            console.log('App is ready for offline usage.');
        },
    });
}
