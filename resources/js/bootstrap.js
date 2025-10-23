// Comic Reading Website Bootstrap
class ComicBootstrap {
    constructor() {
        this.init();
    }

    init() {
        this.setupCSRFProtection();
        this.setupErrorHandling();
        this.setupOfflineDetection();
        this.setupPerformanceMonitoring();
        this.injectComicHelpers();
    }

    setupCSRFProtection() {
        // Get CSRF token from meta tag for form submissions
        const token = document.querySelector('meta[name="csrf-token"]');
        if (token) {
            window.csrfToken = token.content;
        }
    }

    setupErrorHandling() {
        // Global error handler for comic-specific errors
        window.addEventListener('error', (event) => {
            console.error('Comic App Error:', event.error);
            this.showComicError('Something went wrong. Please refresh the page.');
        });

        // Handle unhandled promise rejections
        window.addEventListener('unhandledrejection', (event) => {
            console.error('Unhandled Promise Rejection:', event.reason);
            this.showComicError('Action failed. Please try again.');
        });
    }

    setupOfflineDetection() {
        // Handle offline/online status for comic reading
        window.addEventListener('online', () => {
            this.showComicToast('Back online!', 'success');
            document.documentElement.classList.remove('offline');
        });

        window.addEventListener('offline', () => {
            this.showComicToast('You are offline. Some features may not work.', 'warning');
            document.documentElement.classList.add('offline');
        });
    }

    setupPerformanceMonitoring() {
        // Monitor page load performance for comic images
        if ('performance' in window) {
            window.addEventListener('load', () => {
                const perfData = performance.timing;
                const loadTime = perfData.loadEventEnd - perfData.navigationStart;
                
                if (loadTime > 3000) {
                    console.warn(`Comic page loaded in ${loadTime}ms - Consider optimizing images`);
                }
            });
        }
    }

    injectComicHelpers() {
        // Global comic helper functions
        window.ComicHelpers = {
            // Format file size for comic uploads
            formatFileSize(bytes) {
                if (bytes === 0) return '0 Bytes';
                const k = 1024;
                const sizes = ['Bytes', 'KB', 'MB', 'GB'];
                const i = Math.floor(Math.log(bytes) / Math.log(k));
                return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
            },

            // Generate comic placeholder
            generatePlaceholder(width, height, text = 'COMIC') {
                return `data:image/svg+xml;base64,${btoa(`
                    <svg width="${width}" height="${height}" xmlns="http://www.w3.org/2000/svg">
                        <rect width="100%" height="100%" fill="#1e293b"/>
                        <text x="50%" y="50%" font-family="Arial" font-size="14" fill="#64748b" 
                              text-anchor="middle" dy=".3em">${text}</text>
                    </svg>
                `)}`;
            },

            // Debounce function for search
            debounce(func, wait) {
                let timeout;
                return function executedFunction(...args) {
                    const later = () => {
                        clearTimeout(timeout);
                        func(...args);
                    };
                    clearTimeout(timeout);
                    timeout = setTimeout(later, wait);
                };
            },

            // Check if image exists
            async checkImageExists(url) {
                try {
                    const response = await fetch(url, { method: 'HEAD' });
                    return response.ok;
                } catch {
                    return false;
                }
            },

            // Get reading progress from localStorage
            getReadingProgress(comicId) {
                return localStorage.getItem(`comic-progress-${comicId}`);
            },

            // Save reading progress to localStorage
            saveReadingProgress(comicId, page) {
                localStorage.setItem(`comic-progress-${comicId}`, page);
            }
        };

        // Enhanced fetch with comic-specific headers
        window.comicFetch = async (url, options = {}) => {
            const defaultOptions = {
                headers: {
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                    ...(window.csrfToken && options.method && options.method !== 'GET' ? {
                        'X-CSRF-TOKEN': window.csrfToken
                    } : {}),
                    ...options.headers
                }
            };

            try {
                const response = await fetch(url, { ...defaultOptions, ...options });
                
                if (!response.ok) {
                    throw new Error(`HTTP error! status: ${response.status}`);
                }

                const contentType = response.headers.get('content-type');
                if (contentType && contentType.includes('application/json')) {
                    return await response.json();
                }

                return await response.text();
            } catch (error) {
                console.error('Comic Fetch Error:', error);
                this.showComicError('Network error. Please check your connection.');
                throw error;
            }
        };
    }

    showComicError(message) {
        // Create comic-style error notification
        const errorDiv = document.createElement('div');
        errorDiv.className = 'comic-error fixed top-4 left-1/2 transform -translate-x-1/2 z-50 bg-red-500 text-white px-6 py-3 rounded-lg shadow-lg border-2 border-red-700 font-comic animate-bounce';
        errorDiv.innerHTML = `
            <div class="flex items-center gap-2">
                <span class="text-xl">💥</span>
                <span class="font-bold">${message}</span>
            </div>
        `;

        document.body.appendChild(errorDiv);

        // Auto remove after 5 seconds
        setTimeout(() => {
            errorDiv.remove();
        }, 5000);
    }

    showComicToast(message, type = 'info') {
        const typeConfig = {
            success: { bg: 'bg-green-500', border: 'border-green-700', emoji: '🎉' },
            error: { bg: 'bg-red-500', border: 'border-red-700', emoji: '💥' },
            warning: { bg: 'bg-yellow-500', border: 'border-yellow-700', emoji: '⚠️' },
            info: { bg: 'bg-blue-500', border: 'border-blue-700', emoji: 'ℹ️' }
        };

        const config = typeConfig[type] || typeConfig.info;

        const toast = document.createElement('div');
        toast.className = `comic-toast fixed top-4 right-4 z-50 ${config.bg} text-white px-6 py-3 rounded-lg shadow-lg border-2 ${config.border} font-comic animate-slide-left`;
        toast.innerHTML = `
            <div class="flex items-center gap-2">
                <span class="text-xl">${config.emoji}</span>
                <span>${message}</span>
            </div>
        `;

        document.body.appendChild(toast);

        setTimeout(() => {
            toast.remove();
        }, 3000);
    }
}

// Initialize when DOM is ready
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', () => {
        window.ComicApp = new ComicBootstrap();
    });
} else {
    window.ComicApp = new ComicBootstrap();
}

// Export for module usage if needed
export default ComicBootstrap;

// Optional: Service Worker for offline comic reading (progressive web app feature)
if ('serviceWorker' in navigator) {
    window.addEventListener('load', () => {
        // You can register a service worker here for offline functionality
        // navigator.serviceWorker.register('/sw-comic.js');
    });
}