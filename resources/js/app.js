import './bootstrap';

// Theme management
const Theme = {
    init() {
        this.watchSystemTheme();
        this.setupThemeToggle();
    },

    watchSystemTheme() {
        const root = document.documentElement;
        const mediaQuery = window.matchMedia('(prefers-color-scheme: dark)');
        
        mediaQuery.addEventListener('change', (e) => {
            if (localStorage.getItem('theme') === null) {
                this.setTheme(e.matches ? 'dark' : 'light');
            }
        });
    },

    setupThemeToggle() {
        const themeToggle = document.querySelector('[data-theme-toggle]');
        if (themeToggle) {
            themeToggle.addEventListener('click', () => {
                const isDark = document.documentElement.classList.contains('dark');
                this.setTheme(isDark ? 'light' : 'dark');
            });
        }
    },

    setTheme(theme) {
        const root = document.documentElement;
        localStorage.setItem('theme', theme);
        root.classList.remove('light', 'dark');
        root.classList.add(theme);
    }
};

// UI Components
const UI = {
    init() {
        this.setupDropdowns();
        this.setupModals();
        this.setupAlerts();
    },

    setupDropdowns() {
        document.querySelectorAll('[data-dropdown]').forEach(dropdown => {
            const trigger = dropdown.querySelector('[data-dropdown-trigger]');
            const content = dropdown.querySelector('[data-dropdown-content]');

            if (trigger && content) {
                trigger.addEventListener('click', (e) => {
                    e.stopPropagation();
                    content.classList.toggle('hidden');
                });

                document.addEventListener('click', () => {
                    content.classList.add('hidden');
                });
            }
        });
    },

    setupModals() {
        document.querySelectorAll('[data-modal-trigger]').forEach(trigger => {
            const modalId = trigger.dataset.modalTrigger;
            const modal = document.querySelector(`[data-modal="${modalId}"]`);
            const closeBtn = modal?.querySelector('[data-modal-close]');

            if (modal) {
                trigger.addEventListener('click', () => {
                    modal.classList.remove('hidden');
                    document.body.style.overflow = 'hidden';
                });

                if (closeBtn) {
                    closeBtn.addEventListener('click', () => {
                        modal.classList.add('hidden');
                        document.body.style.overflow = '';
                    });
                }
            }
        });
    },

    setupAlerts() {
        document.querySelectorAll('[data-alert]').forEach(alert => {
            const closeBtn = alert.querySelector('[data-alert-close]');
            if (closeBtn) {
                closeBtn.addEventListener('click', () => {
                    alert.remove();
                });
            }
        });
    }
};

// Form handling
const Forms = {
    init() {
        this.setupValidation();
        this.setupFileUploads();
    },

    setupValidation() {
        document.querySelectorAll('form[data-validate]').forEach(form => {
            form.addEventListener('submit', (e) => {
                if (!form.checkValidity()) {
                    e.preventDefault();
                    this.showValidationErrors(form);
                }
            });
        });
    },

    showValidationErrors(form) {
        const inputs = form.querySelectorAll('input, select, textarea');
        inputs.forEach(input => {
            const errorElement = input.nextElementSibling;
            if (!input.validity.valid && errorElement?.dataset.error) {
                errorElement.textContent = input.validationMessage;
                errorElement.classList.remove('hidden');
            }
        });
    },

    setupFileUploads() {
        document.querySelectorAll('[data-file-upload]').forEach(upload => {
            const input = upload.querySelector('input[type="file"]');
            const preview = upload.querySelector('[data-file-preview]');

            if (input && preview) {
                input.addEventListener('change', () => {
                    if (input.files && input.files[0]) {
                        const reader = new FileReader();
                        reader.onload = (e) => {
                            preview.src = e.target.result;
                            preview.classList.remove('hidden');
                        };
                        reader.readAsDataURL(input.files[0]);
                    }
                });
            }
        });
    }
};

// API interactions
const API = {
    async get(url, options = {}) {
        try {
            const response = await fetch(url, {
                ...options,
                headers: {
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                    ...options.headers
                }
            });
            return await response.json();
        } catch (error) {
            console.error('API Error:', error);
            throw error;
        }
    },

    async post(url, data, options = {}) {
        try {
            const response = await fetch(url, {
                method: 'POST',
                body: JSON.stringify(data),
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content,
                    ...options.headers
                },
                ...options
            });
            return await response.json();
        } catch (error) {
            console.error('API Error:', error);
            throw error;
        }
    }
};

// Initialize everything when DOM is loaded
document.addEventListener('DOMContentLoaded', () => {
    Theme.init();
    UI.init();
    Forms.init();
});

// Make utilities available globally
window.API = API;
