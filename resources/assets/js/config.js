/**
 * Config
 * -------------------------------------------------------------------------------------
 * ! IMPORTANT: Make sure you clear the browser local storage In order to see the config changes in the template.
 */

'use strict';

// Get layout name for dynamic storage keys
const layoutName = document.documentElement.getAttribute('data-template') || 'vertical-menu-template';

// --- 1. FORCE LTR (Left-to-Right) ---
document.documentElement.setAttribute('dir', 'ltr');
localStorage.setItem(`templateCustomizer-${layoutName}--Rtl`, 'false');
document.cookie = "direction=false; path=/; max-age=31536000";

// --- 2. FORCE THEME PERSISTENCE (Fix Partial Dark Mode) ---
let savedTheme = localStorage.getItem(`templateCustomizer-${layoutName}--Theme`) || 'light';

if(savedTheme === 'system') {
    savedTheme = window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light';
}

// Apply Attributes
document.documentElement.setAttribute('data-bs-theme', savedTheme);
document.documentElement.setAttribute('data-theme', savedTheme);

// Apply Template Classes (Crucial for full Dark Mode)
if (savedTheme === 'dark') {
    document.documentElement.classList.add('dark-style');
    document.documentElement.classList.remove('light-style');
} else {
    document.documentElement.classList.add('light-style');
    document.documentElement.classList.remove('dark-style');
}

/* JS global variables */
window.config = {
  colors: {
    primary: '#696cff',
    secondary: '#8592a3',
    success: '#71dd37',
    info: '#03c3ec',
    warning: '#ffab00',
    danger: '#ff3e1d',
    dark: '#233446',
    black: '#000',
    white: '#fff',
    cardColor: savedTheme === 'dark' ? '#2b2c40' : '#fff',
    bodyBg: savedTheme === 'dark' ? '#232333' : '#f5f5f9',
    bodyColor: savedTheme === 'dark' ? '#b6bee3' : '#697a8d',
    headingColor: savedTheme === 'dark' ? '#cfcfe4' : '#566a7f',
    textMuted: savedTheme === 'dark' ? '#7983bb' : '#a1acb8',
    borderColor: savedTheme === 'dark' ? '#444564' : '#d9dee3'
  },
  colors_label: {
    primary: '#666ee81a',
    secondary: '#8897aa1a',
    success: '#28d0941a',
    info: '#1e9ff21a',
    warning: '#ff91491a',
    danger: '#ff49611a',
    dark: '#181c211a'
  },
  fontFamily: "Public Sans",
  enableMenuLocalStorage: true
};

window.assetsPath = document.documentElement.getAttribute('data-assets-path');
window.baseUrl = document.documentElement.getAttribute('data-base-url') + '/';
window.templateName = document.documentElement.getAttribute('data-template');
