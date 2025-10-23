import './bootstrap';

// Comic Book Theme Management
const ComicTheme = {
    init() {
        this.watchSystemTheme();
        this.setupThemeToggle();
        this.applyComicFonts();
    },

    watchSystemTheme() {
        const root = document.documentElement;
        const mediaQuery = window.matchMedia('(prefers-color-scheme: dark)');
        
        mediaQuery.addEventListener('change', (e) => {
            if (localStorage.getItem('comic-theme') === null) {
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
        localStorage.setItem('comic-theme', theme);
        root.classList.remove('light', 'dark');
        root.classList.add(theme);
        
        // Dispatch custom event for theme change
        document.dispatchEvent(new CustomEvent('comicThemeChange', { detail: { theme } }));
    },

    applyComicFonts() {
        // Dynamically load comic fonts
        if (!document.querySelector('#comic-fonts')) {
            const link = document.createElement('link');
            link.id = 'comic-fonts';
            link.rel = 'stylesheet';
            link.href = 'https://fonts.googleapis.com/css2?family=Bebas+Neue&family=Comic+Neue:wght@400;700&display=swap';
            document.head.appendChild(link);
        }
    }
};

// Comic Reader System
const ComicReader = {
    currentPage: 1,
    totalPages: 0,
    isFullscreen: false,
    readerMode: 'single', // 'single', 'double', 'strip'

    init() {
        this.setupReaderNavigation();
        this.setupKeyboardNavigation();
        this.setupTouchNavigation();
        this.setupReaderMode();
        this.setupProgressTracking();
    },

    setupReaderNavigation() {
        const prevBtn = document.querySelector('[data-reader-prev]');
        const nextBtn = document.querySelector('[data-reader-next]');
        const pageInput = document.querySelector('[data-reader-page]');

        if (prevBtn) {
            prevBtn.addEventListener('click', () => this.previousPage());
        }

        if (nextBtn) {
            nextBtn.addEventListener('click', () => this.nextPage());
        }

        if (pageInput) {
            pageInput.addEventListener('change', (e) => this.goToPage(parseInt(e.target.value)));
        }

        // Fullscreen toggle
        const fullscreenBtn = document.querySelector('[data-reader-fullscreen]');
        if (fullscreenBtn) {
            fullscreenBtn.addEventListener('click', () => this.toggleFullscreen());
        }
    },

    setupKeyboardNavigation() {
        document.addEventListener('keydown', (e) => {
            if (!this.isReaderActive()) return;

            switch(e.key) {
                case 'ArrowLeft':
                case 'a':
                    e.preventDefault();
                    this.previousPage();
                    break;
                case 'ArrowRight':
                case 'd':
                    e.preventDefault();
                    this.nextPage();
                    break;
                case 'f':
                    e.preventDefault();
                    this.toggleFullscreen();
                    break;
                case 'Escape':
                    if (this.isFullscreen) {
                        this.exitFullscreen();
                    }
                    break;
            }
        });
    },

    setupTouchNavigation() {
        let touchStartX = 0;
        let touchEndX = 0;

        document.addEventListener('touchstart', (e) => {
            if (!this.isReaderActive()) return;
            touchStartX = e.changedTouches[0].screenX;
        });

        document.addEventListener('touchend', (e) => {
            if (!this.isReaderActive()) return;
            touchEndX = e.changedTouches[0].screenX;
            this.handleSwipe(touchStartX, touchEndX);
        });
    },

    handleSwipe(startX, endX) {
        const swipeThreshold = 50;
        const diff = startX - endX;

        if (Math.abs(diff) > swipeThreshold) {
            if (diff > 0) {
                this.nextPage();
            } else {
                this.previousPage();
            }
        }
    },

    setupReaderMode() {
        const modeBtns = document.querySelectorAll('[data-reader-mode]');
        modeBtns.forEach(btn => {
            btn.addEventListener('click', (e) => {
                const mode = e.target.dataset.readerMode;
                this.setReaderMode(mode);
                modeBtns.forEach(b => b.classList.remove('active'));
                e.target.classList.add('active');
            });
        });
    },

    setReaderMode(mode) {
        this.readerMode = mode;
        document.documentElement.setAttribute('data-reader-mode', mode);
        localStorage.setItem('comic-reader-mode', mode);
    },

    setupProgressTracking() {
        // Load saved progress
        const comicId = document.querySelector('[data-comic-id]')?.dataset.comicId;
        if (comicId) {
            const savedProgress = localStorage.getItem(`comic-progress-${comicId}`);
            if (savedProgress) {
                this.currentPage = parseInt(savedProgress);
                this.updatePageDisplay();
            }
        }
    },

    previousPage() {
        if (this.currentPage > 1) {
            this.currentPage--;
            this.updatePageDisplay();
            this.saveProgress();
            this.animatePageTurn('prev');
        }
    },

    nextPage() {
        if (this.currentPage < this.totalPages) {
            this.currentPage++;
            this.updatePageDisplay();
            this.saveProgress();
            this.animatePageTurn('next');
        }
    },

    goToPage(page) {
        if (page >= 1 && page <= this.totalPages) {
            this.currentPage = page;
            this.updatePageDisplay();
            this.saveProgress();
        }
    },

    updatePageDisplay() {
        const pageInput = document.querySelector('[data-reader-page]');
        const pageDisplay = document.querySelector('[data-reader-page-display]');
        const progressBar = document.querySelector('[data-reader-progress]');

        if (pageInput) pageInput.value = this.currentPage;
        if (pageDisplay) pageDisplay.textContent = `${this.currentPage} / ${this.totalPages}`;
        if (progressBar) {
            const progress = (this.currentPage / this.totalPages) * 100;
            progressBar.style.width = `${progress}%`;
        }

        // Update image source if needed
        const readerImage = document.querySelector('[data-reader-image]');
        if (readerImage && readerImage.dataset.pagePrefix) {
            readerImage.src = `${readerImage.dataset.pagePrefix}${this.currentPage}${readerImage.dataset.pageSuffix || ''}`;
            readerImage.alt = `Page ${this.currentPage}`;
        }
    },

    animatePageTurn(direction) {
        const readerImage = document.querySelector('[data-reader-image]');
        if (readerImage) {
            readerImage.style.animation = 'none';
            setTimeout(() => {
                readerImage.style.animation = `pageTurn 0.6s ease-in-out`;
            }, 10);
        }
    },

    toggleFullscreen() {
        if (!this.isFullscreen) {
            this.enterFullscreen();
        } else {
            this.exitFullscreen();
        }
    },

    enterFullscreen() {
        const readerContainer = document.querySelector('[data-reader-container]');
        if (readerContainer.requestFullscreen) {
            readerContainer.requestFullscreen();
        } else if (readerContainer.webkitRequestFullscreen) {
            readerContainer.webkitRequestFullscreen();
        }
        this.isFullscreen = true;
        document.documentElement.setAttribute('data-fullscreen', 'true');
    },

    exitFullscreen() {
        if (document.exitFullscreen) {
            document.exitFullscreen();
        } else if (document.webkitExitFullscreen) {
            document.webkitExitFullscreen();
        }
        this.isFullscreen = false;
        document.documentElement.removeAttribute('data-fullscreen');
    },

    saveProgress() {
        const comicId = document.querySelector('[data-comic-id]')?.dataset.comicId;
        if (comicId) {
            localStorage.setItem(`comic-progress-${comicId}`, this.currentPage);
            
            // Auto-save to server if API available
            if (window.API) {
                API.post(`/api/comics/${comicId}/progress`, {
                    page: this.currentPage,
                    total_pages: this.totalPages
                }).catch(console.error);
            }
        }
    },

    isReaderActive() {
        return document.querySelector('[data-reader-container]') !== null;
    }
};

// Comic Rating System
const ComicRating = {
    init() {
        this.setupStarRating();
        this.setupRatingDisplay();
    },

    setupStarRating() {
        document.querySelectorAll('[data-rating-stars]').forEach(container => {
            const stars = container.querySelectorAll('[data-rating-star]');
            const input = container.querySelector('[data-rating-input]');
            const comicId = container.dataset.comicId;

            stars.forEach((star, index) => {
                star.addEventListener('click', () => {
                    const rating = index + 1;
                    this.setRating(stars, rating);
                    if (input) input.value = rating;
                    this.submitRating(comicId, rating);
                });

                star.addEventListener('mouseenter', () => {
                    this.previewRating(stars, index + 1);
                });

                star.addEventListener('mouseleave', () => {
                    this.resetPreview(stars, input ? parseInt(input.value) : 0);
                });
            });
        });
    },

    setRating(stars, rating) {
        stars.forEach((star, index) => {
            if (index < rating) {
                star.classList.add('active');
            } else {
                star.classList.remove('active');
            }
        });
    },

    previewRating(stars, rating) {
        stars.forEach((star, index) => {
            if (index < rating) {
                star.classList.add('hover');
            } else {
                star.classList.remove('hover');
            }
        });
    },

    resetPreview(stars, currentRating) {
        stars.forEach((star, index) => {
            star.classList.remove('hover');
            if (index < currentRating) {
                star.classList.add('active');
            }
        });
    },

    async submitRating(comicId, rating) {
        if (!comicId) return;

        try {
            const response = await API.post(`/api/comics/${comicId}/rate`, { rating });
            this.updateRatingDisplay(comicId, response);
            this.showRatingSuccess();
        } catch (error) {
            console.error('Rating submission failed:', error);
            this.showRatingError();
        }
    },

    updateRatingDisplay(comicId, data) {
        const container = document.querySelector(`[data-rating-stars][data-comic-id="${comicId}"]`);
        if (!container) return;

        const averageEl = container.querySelector('[data-rating-average]');
        const totalEl = container.querySelector('[data-rating-total]');

        if (averageEl) averageEl.textContent = data.average.toFixed(1);
        if (totalEl) totalEl.textContent = data.total_ratings;
    },

    showRatingSuccess() {
        this.showToast('Rating saved!', 'success');
    },

    showRatingError() {
        this.showToast('Failed to save rating. Please try again.', 'error');
    },

    setupRatingDisplay() {
        // Load initial rating data for comics
        document.querySelectorAll('[data-comic-id]').forEach(container => {
            const comicId = container.dataset.comicId;
            if (comicId) {
                this.loadRatingData(comicId);
            }
        });
    },

    async loadRatingData(comicId) {
        try {
            const data = await API.get(`/api/comics/${comicId}/rating`);
            this.updateRatingDisplay(comicId, data);
        } catch (error) {
            console.error('Failed to load rating data:', error);
        }
    }
};

// Enhanced UI Components for Comics
const ComicUI = {
    init() {
        this.setupComicGrid();
        this.setupChapterList();
        this.setupComicSearch();
        this.setupImageLazyLoad();
        this.setupComicFilters();
    },

    setupComicGrid() {
        // Add hover effects and lazy loading to comic grid
        const comicCards = document.querySelectorAll('.comic-card');
        comicCards.forEach(card => {
            card.addEventListener('mouseenter', () => {
                card.style.transform = 'translateY(-8px) scale(1.02)';
            });

            card.addEventListener('mouseleave', () => {
                card.style.transform = 'translateY(0) scale(1)';
            });
        });
    },

    setupChapterList() {
        // Add click effects to chapter items
        const chapterItems = document.querySelectorAll('.chapter-item');
        chapterItems.forEach(item => {
            item.addEventListener('click', (e) => {
                if (e.target.tagName !== 'A') {
                    const link = item.querySelector('a');
                    if (link) link.click();
                }
            });
        });
    },

    setupComicSearch() {
        const searchInput = document.querySelector('[data-comic-search]');
        if (searchInput) {
            let searchTimeout;
            searchInput.addEventListener('input', (e) => {
                clearTimeout(searchTimeout);
                searchTimeout = setTimeout(() => {
                    this.performSearch(e.target.value);
                }, 300);
            });
        }
    },

    async performSearch(query) {
        if (query.length < 2) {
            this.clearSearchResults();
            return;
        }

        try {
            const results = await API.get(`/api/comics/search?q=${encodeURIComponent(query)}`);
            this.displaySearchResults(results);
        } catch (error) {
            console.error('Search failed:', error);
        }
    },

    displaySearchResults(results) {
        const container = document.querySelector('[data-search-results]');
        if (!container) return;

        container.innerHTML = results.map(comic => `
            <div class="comic-card animate-fade-in">
                <div class="comic-card__cover">
                    <img src="${comic.cover_url}" alt="${comic.title}" loading="lazy">
                </div>
                <div class="comic-card__info">
                    <h3 class="comic-title">${comic.title}</h3>
                    <p class="text-sm text-gray-400">${comic.author}</p>
                </div>
            </div>
        `).join('');
    },

    clearSearchResults() {
        const container = document.querySelector('[data-search-results]');
        if (container) {
            container.innerHTML = '';
        }
    },

    setupImageLazyLoad() {
        if ('IntersectionObserver' in window) {
            const imageObserver = new IntersectionObserver((entries) => {
                entries.forEach(entry => {
                    if (entry.isIntersecting) {
                        const img = entry.target;
                        img.src = img.dataset.src;
                        img.classList.remove('lazy');
                        imageObserver.unobserve(img);
                    }
                });
            });

            document.querySelectorAll('img[data-src]').forEach(img => {
                imageObserver.observe(img);
            });
        }
    },

    setupComicFilters() {
        const filterButtons = document.querySelectorAll('[data-comic-filter]');
        filterButtons.forEach(button => {
            button.addEventListener('click', (e) => {
                const filter = e.target.dataset.comicFilter;
                this.applyComicFilter(filter);
                
                // Update active state
                filterButtons.forEach(btn => btn.classList.remove('active'));
                e.target.classList.add('active');
            });
        });
    },

    applyComicFilter(filter) {
        const comicCards = document.querySelectorAll('.comic-card');
        comicCards.forEach(card => {
            if (filter === 'all' || card.dataset.category === filter) {
                card.style.display = 'block';
                setTimeout(() => card.classList.add('animate-fade-in'), 10);
            } else {
                card.classList.remove('animate-fade-in');
                card.style.display = 'none';
            }
        });
    }
};

// Enhanced Forms with Comic Style
const ComicForms = {
    init() {
        this.setupValidation();
        this.setupFileUploads();
        this.setupComicSubmit();
    },

    setupValidation() {
        document.querySelectorAll('form[data-validate]').forEach(form => {
            form.addEventListener('submit', (e) => {
                if (!form.checkValidity()) {
                    e.preventDefault();
                    this.showComicValidationErrors(form);
                }
            });

            // Real-time validation
            form.querySelectorAll('input, select, textarea').forEach(input => {
                input.addEventListener('blur', () => {
                    this.validateField(input);
                });
            });
        });
    },

    validateField(field) {
        const errorElement = field.nextElementSibling;
        if (!field.validity.valid && errorElement?.dataset.error) {
            errorElement.textContent = field.validationMessage;
            errorElement.classList.remove('hidden');
            field.classList.add('error');
        } else {
            errorElement?.classList.add('hidden');
            field.classList.remove('error');
        }
    },

    showComicValidationErrors(form) {
        const inputs = form.querySelectorAll('input, select, textarea');
        let firstError = null;

        inputs.forEach(input => {
            this.validateField(input);
            if (!input.validity.valid && !firstError) {
                firstError = input;
            }
        });

        if (firstError) {
            firstError.focus();
            this.showToast('Please check the highlighted fields', 'warning');
        }
    },

    setupFileUploads() {
        document.querySelectorAll('[data-file-upload]').forEach(upload => {
            const input = upload.querySelector('input[type="file"]');
            const preview = upload.querySelector('[data-file-preview]');
            const label = upload.querySelector('[data-file-label]');

            if (input && preview) {
                input.addEventListener('change', () => {
                    if (input.files && input.files[0]) {
                        const reader = new FileReader();
                        reader.onload = (e) => {
                            preview.src = e.target.result;
                            preview.classList.remove('hidden');
                            if (label) {
                                label.textContent = input.files[0].name;
                            }
                        };
                        reader.readAsDataURL(input.files[0]);
                    }
                });
            }
        });
    },

    setupComicSubmit() {
        document.querySelectorAll('form[data-comic-submit]').forEach(form => {
            form.addEventListener('submit', async (e) => {
                e.preventDefault();
                
                const submitBtn = form.querySelector('[type="submit"]');
                const originalText = submitBtn.textContent;
                
                // Show loading state
                submitBtn.disabled = true;
                submitBtn.innerHTML = '<div class="comic-spinner"></div> Submitting...';
                
                try {
                    const formData = new FormData(form);
                    const response = await API.post(form.action, formData);
                    
                    this.showToast('Comic submitted successfully!', 'success');
                    setTimeout(() => {
                        window.location.href = response.redirect || '/';
                    }, 1500);
                    
                } catch (error) {
                    console.error('Form submission failed:', error);
                    this.showToast('Submission failed. Please try again.', 'error');
                    submitBtn.disabled = false;
                    submitBtn.textContent = originalText;
                }
            });
        });
    }
};

// Toast Notification System
const ComicToasts = {
    init() {
        this.createToastContainer();
    },

    createToastContainer() {
        if (!document.querySelector('#comic-toasts')) {
            const container = document.createElement('div');
            container.id = 'comic-toasts';
            container.className = 'fixed top-4 right-4 z-50 space-y-2';
            document.body.appendChild(container);
        }
    },

    showToast(message, type = 'info') {
        const container = document.querySelector('#comic-toasts');
        const toast = document.createElement('div');
        
        const typeConfig = {
            success: { bg: 'bg-green-500', icon: '✓' },
            error: { bg: 'bg-red-500', icon: '✕' },
            warning: { bg: 'bg-yellow-500', icon: '⚠' },
            info: { bg: 'bg-blue-500', icon: 'ℹ' }
        };

        const config = typeConfig[type] || typeConfig.info;

        toast.className = `animate-slide-left ${config.bg} text-white px-4 py-3 rounded-lg shadow-lg flex items-center gap-2 min-w-64`;
        toast.innerHTML = `
            <span class="font-bold">${config.icon}</span>
            <span>${message}</span>
        `;

        container.appendChild(toast);

        // Auto remove after 5 seconds
        setTimeout(() => {
            toast.classList.add('animate-fade-in');
            setTimeout(() => toast.remove(), 300);
        }, 5000);
    }
};

// Enhanced API interactions for comics
const ComicAPI = {
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
            
            if (!response.ok) throw new Error(`HTTP error! status: ${response.status}`);
            return await response.json();
            
        } catch (error) {
            console.error('Comic API Error:', error);
            ComicToasts.showToast('Failed to load data', 'error');
            throw error;
        }
    },

    async post(url, data, options = {}) {
        try {
            const isFormData = data instanceof FormData;
            const response = await fetch(url, {
                method: 'POST',
                body: isFormData ? data : JSON.stringify(data),
                headers: {
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                    ...(isFormData ? {} : { 'Content-Type': 'application/json' }),
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content,
                    ...options.headers
                },
                ...options
            });
            
            if (!response.ok) throw new Error(`HTTP error! status: ${response.status}`);
            return await response.json();
            
        } catch (error) {
            console.error('Comic API Error:', error);
            ComicToasts.showToast('Action failed', 'error');
            throw error;
        }
    }
};

// Initialize everything when DOM is loaded
document.addEventListener('DOMContentLoaded', () => {
    ComicTheme.init();
    ComicUI.init();
    ComicForms.init();
    ComicRating.init();
    ComicToasts.init();
    
    // Initialize reader only if on reader page
    if (document.querySelector('[data-reader-container]')) {
        ComicReader.init();
    }
});

// Fullscreen change handler
document.addEventListener('fullscreenchange', () => {
    ComicReader.isFullscreen = !!document.fullscreenElement;
});

// Make utilities available globally
window.ComicReader = ComicReader;
window.ComicRating = ComicRating;
window.ComicToasts = ComicToasts;
window.API = ComicAPI;

// Export for module usage
export { ComicTheme, ComicReader, ComicRating, ComicUI, ComicForms, ComicToasts };