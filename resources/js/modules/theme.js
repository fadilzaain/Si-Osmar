const STORAGE_KEY = 'siosmar-theme';

function systemPrefersDark() {
  return window.matchMedia('(prefers-color-scheme: dark)').matches;
}

function applyTheme(theme) {
  const resolved = theme === 'auto' ? (systemPrefersDark() ? 'dark' : 'light') : theme;
  document.documentElement.setAttribute('data-theme', resolved);
  document.documentElement.setAttribute('data-theme-mode', theme);
}

export function getStoredTheme() {
  return localStorage.getItem(STORAGE_KEY) || 'auto';
}

export function setTheme(theme) {
  localStorage.setItem(STORAGE_KEY, theme);
  applyTheme(theme);
}

export function initTheme() {
  applyTheme(getStoredTheme());

  window.matchMedia('(prefers-color-scheme: dark)').addEventListener('change', () => {
    if (getStoredTheme() === 'auto') applyTheme('auto');
  });

  document.querySelectorAll('[data-theme-toggle]').forEach((btn) => {
    btn.addEventListener('click', () => {
      const order = ['light', 'dark', 'auto'];
      const next = order[(order.indexOf(getStoredTheme()) + 1) % order.length];
      setTheme(next);
      updateThemeIcon(next);
    });
  });

  updateThemeIcon(getStoredTheme());
}

function updateThemeIcon(mode) {
  const icons = { light: 'fa-sun', dark: 'fa-moon', auto: 'fa-circle-half-stroke' };
  document.querySelectorAll('[data-theme-toggle] i').forEach((icon) => {
    icon.className = `fa-solid ${icons[mode]}`;
  });
}

//============================================================================
// note: ini di -initTheme() lewat app.js (setelah dom ready), agar tidak flash of wrong theme, tetap taruh inline
// script kecil di <head> layout
