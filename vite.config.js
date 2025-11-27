import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import html from '@rollup/plugin-html';
import { glob } from 'glob';
import path from 'path';
import iconsPlugin from './vite.icons.plugin.js';
import { VitePWA } from 'vite-plugin-pwa'; // <--- Import is good

/**
 * Get Files from a directory
 * @param {string} query
 * @returns array
 */
function GetFilesArray(query) {
  return glob.sync(query, { windowsPathsNoEscape: true });
}

// Page JS Files
const pageJsFiles = GetFilesArray('resources/assets/js/*.js');
const vendorJsFiles = GetFilesArray('resources/assets/vendor/js/*.js');
const LibsJsFiles = GetFilesArray('resources/assets/vendor/libs/**/*.js');
const LibsScssFiles = GetFilesArray('resources/assets/vendor/libs/**/!(_)*.scss');
const LibsCssFiles = GetFilesArray('resources/assets/vendor/libs/**/*.css');
const CoreScssFiles = GetFilesArray('resources/assets/vendor/scss/**/!(_)*.scss');
const FontsScssFiles = GetFilesArray('resources/assets/vendor/fonts/!(_)*.scss');
const FontsJsFiles = GetFilesArray('resources/assets/vendor/fonts/**/!(_)*.js');
const FontsCssFiles = GetFilesArray('resources/assets/vendor/fonts/**/!(_)*.css');

function libsWindowAssignment() {
  return {
    name: 'libsWindowAssignment',
    transform(src, id) {
      if (id.includes('jkanban.js')) {
        return src.replace('this.jKanban', 'window.jKanban');
      } else if (id.includes('vfs_fonts')) {
        return src.replaceAll('this.pdfMake', 'window.pdfMake');
      }
    }
  };
}

export default defineConfig({
  plugins: [
    laravel({
      input: [
        'resources/assets/vendor/scss/core.scss',
        'resources/assets/css/demo.css',
        'resources/assets/vendor/libs/node-waves/node-waves.scss',
        'resources/assets/vendor/libs/perfect-scrollbar/perfect-scrollbar.scss',
        'resources/assets/vendor/libs/typeahead-js/typeahead.scss',
        'resources/css/app.css',
        'resources/js/app.js',
        ...pageJsFiles,
        ...vendorJsFiles,
        ...LibsJsFiles,
        'resources/js/laravel-user-management.js',
        ...CoreScssFiles,
        ...LibsScssFiles,
        ...LibsCssFiles,
        ...FontsScssFiles,
        ...FontsJsFiles,
        ...FontsCssFiles
      ],
      refresh: true
    }),
    html(),
    libsWindowAssignment(),
    iconsPlugin(),

    // --- YOU WERE MISSING THIS BLOCK BELOW ---
    VitePWA({
      registerType: 'autoUpdate',
      outDir: 'public',
      buildBase: '/',
      scope: '/',
      workbox: {
        cleanupOutdatedCaches: true,
        directoryIndex: null,
        maximumFileSizeToCacheInBytes: 4 * 1024 * 1024,
        navigateFallback: null
      },
      manifest: {
        name: 'Civic Utopia',
        short_name: 'CivicUtopia',
        description: 'A platform for civic engagement',
        theme_color: '#666cff',
        background_color: '#ffffff',
        display: 'standalone',
        start_url: '/',
        icons: [
          {
            src: '/assets/img/pwa/icon-192x192.png',
            sizes: '192x192',
            type: 'image/png'
          },
          {
            src: '/assets/img/pwa/icon-512x512.png',
            sizes: '512x512',
            type: 'image/png'
          }
        ]
      }
    })
    // ----------------------------------------
  ],
  resolve: {
    alias: {
      '@': path.resolve(__dirname, 'resources')
    }
  },
  json: {
    stringify: true
  },
  build: {
    commonjsOptions: {
      include: [/node_modules/]
    }
  }
});
