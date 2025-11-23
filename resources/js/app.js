// This file is essential for loading our Echo configuration
import './bootstrap';

// Your template's default JavaScript (e.g., Alpine) can remain
import Alpine from 'alpinejs';

window.Alpine = Alpine;

Alpine.start();

// We have REMOVED the Vue-related code from here.
// No 'createApp' or 'app.mount' calls are needed.
