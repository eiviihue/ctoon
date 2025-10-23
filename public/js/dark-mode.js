(function(){
  'use strict';

  document.addEventListener('DOMContentLoaded', function() {
    const darkModeToggle = document.querySelector('[data-theme-toggle]');
    const htmlElement = document.documentElement;

    // fallback icon element if toggle exists
    const getIcon = (btn) => {
      if (!btn) return null;
      return btn.querySelector('i') || null;
    };

    // Determine initial theme
    const savedTheme = (() => {
      try { return localStorage.getItem('theme'); } catch(e) { return null; }
    })();
    const systemPrefersDark = window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches;

    const applyTheme = (theme) => {
      htmlElement.setAttribute('data-bs-theme', theme);
      const btnIcon = getIcon(darkModeToggle);
      if (btnIcon) {
        if (theme === 'dark') {
          btnIcon.classList.remove('fa-sun');
          btnIcon.classList.add('fa-moon');
        } else {
          btnIcon.classList.remove('fa-moon');
          btnIcon.classList.add('fa-sun');
        }
      }
      if (darkModeToggle) {
        darkModeToggle.setAttribute('aria-pressed', theme === 'dark' ? 'true' : 'false');
      }
    };

    // apply initial
    if (savedTheme === 'dark' || savedTheme === 'light') {
      applyTheme(savedTheme);
    } else {
      applyTheme(systemPrefersDark ? 'dark' : 'light');
    }

    // respond to system changes (optional)
    if (window.matchMedia) {
      const mq = window.matchMedia('(prefers-color-scheme: dark)');
      if (typeof mq.addEventListener === 'function') {
        mq.addEventListener('change', (e) => {
          const stored = (() => { try { return localStorage.getItem('theme'); } catch(e){ return null; }})();
          // only follow system if user hasn't explicitly set a preference
          if (!stored) applyTheme(e.matches ? 'dark' : 'light');
        });
      }
    }

    // guard: if no toggle present, nothing more to do
    if (!darkModeToggle) return;

    // click handler
    darkModeToggle.addEventListener('click', function() {
      const current = htmlElement.getAttribute('data-bs-theme') || (systemPrefersDark ? 'dark' : 'light');
      const next = current === 'dark' ? 'light' : 'dark';
      try { localStorage.setItem('theme', next); } catch(e) {}
      applyTheme(next);
    });

  });
})();
