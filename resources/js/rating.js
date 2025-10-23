export class ComicRating {
    constructor(comicId, options = {}) {
        this.comicId = comicId;
        this.options = {
            showBreakdown: true,
            showUserRating: true,
            animateStars: true,
            onRatingChange: null,
            ...options
        };
        
        this.userRating = 0;
        this.averageRating = 0;
        this.totalRatings = 0;
        this.ratingBreakdown = {};
        
        this.init();
    }

    async init() {
        this.ratingContainer = document.querySelector(`[data-comic-rating="${this.comicId}"]`) || document.querySelector('.rating-container');
        if (!this.ratingContainer) return;

        this.stars = this.ratingContainer.querySelectorAll('.star, [data-rating-star]');
        this.averageDisplay = this.ratingContainer.querySelector('.average-rating, [data-rating-average]');
        this.totalDisplay = this.ratingContainer.querySelector('.total-ratings, [data-rating-total]');
        this.ratingInput = this.ratingContainer.querySelector('input[name="rating"], [data-rating-input]');

        await this.loadRatingData();
        this.setupEventListeners();
        this.createRatingBreakdown();
        this.updateDisplay();
    }

    async loadRatingData() {
        try {
            const response = await this.fetchRatingData();
            this.averageRating = response.average || 0;
            this.totalRatings = response.total_ratings || 0;
            this.userRating = response.user_rating || 0;
            this.ratingBreakdown = response.breakdown || {};
            
            if (this.ratingInput) {
                this.ratingInput.value = this.userRating;
            }
        } catch (error) {
            console.error('Failed to load rating data:', error);
            this.showRatingMessage('Failed to load ratings', 'error');
        }
    }

    async fetchRatingData() {
        // Use comicFetch from bootstrap if available, otherwise fallback to fetch
        const fetchMethod = window.comicFetch || fetch;
        const response = await fetchMethod(`/api/comics/${this.comicId}/rating`);
        return response;
    }

    setupEventListeners() {
        this.stars.forEach((star, index) => {
            const rating = index + 1;

            // Click to rate
            star.addEventListener('click', (e) => {
                e.preventDefault();
                this.submitRating(rating);
            });

            // Hover effects
            if (this.options.animateStars) {
                star.addEventListener('mouseenter', () => {
                    this.previewRating(rating);
                    this.animateStar(star);
                });

                star.addEventListener('mouseleave', () => {
                    this.resetPreview();
                });
            }

            // Touch support for mobile
            star.addEventListener('touchstart', (e) => {
                e.preventDefault();
                this.previewRating(rating);
            });

            star.addEventListener('touchend', (e) => {
                e.preventDefault();
                this.submitRating(rating);
            });
        });

        // Keyboard navigation
        this.ratingContainer.addEventListener('keydown', (e) => {
            if (e.key === 'Enter' || e.key === ' ') {
                e.preventDefault();
                const focusedStar = document.activeElement;
                if (focusedStar.classList.contains('star')) {
                    const rating = Array.from(this.stars).indexOf(focusedStar) + 1;
                    this.submitRating(rating);
                }
            }
        });
    }

    previewRating(rating) {
        this.stars.forEach((star, index) => {
            const isActive = index < rating;
            star.classList.toggle('hover', isActive);
            star.classList.toggle('preview-active', isActive);
            
            if (this.options.animateStars) {
                star.style.transform = isActive ? 'scale(1.2)' : 'scale(1)';
            }
        });
    }

    resetPreview() {
        this.stars.forEach((star, index) => {
            const isActive = index < this.userRating;
            star.classList.remove('hover', 'preview-active');
            star.classList.toggle('active', isActive);
            
            if (this.options.animateStars) {
                star.style.transform = isActive ? 'scale(1.1)' : 'scale(1)';
            }
        });
    }

    animateStar(star) {
        if (!this.options.animateStars) return;

        star.style.animation = 'none';
        setTimeout(() => {
            star.style.animation = 'comicPulse 0.6s ease-in-out';
        }, 10);
    }

    async submitRating(rating) {
        // Don't resubmit same rating unless explicitly allowed
        if (rating === this.userRating && !this.options.allowResubmit) {
            this.showRatingMessage('You already gave this rating!', 'info');
            return;
        }

        const previousRating = this.userRating;
        this.userRating = rating;
        this.updateStars();

        try {
            const response = await this.sendRatingToServer(rating);
            
            this.averageRating = response.average;
            this.totalRatings = response.total_ratings;
            this.ratingBreakdown = response.breakdown || {};
            
            this.updateDisplay();
            this.updateBreakdown();
            
            this.showRatingMessage('Rating saved!', 'success');
            
            // Call callback if provided
            if (typeof this.options.onRatingChange === 'function') {
                this.options.onRatingChange(rating, previousRating, response);
            }

        } catch (error) {
            // Revert on error
            this.userRating = previousRating;
            this.updateStars();
            this.showRatingMessage('Failed to save rating', 'error');
            console.error('Rating submission failed:', error);
        }
    }

    async sendRatingToServer(rating) {
        const fetchMethod = window.comicFetch || fetch;
        const response = await fetchMethod(`/api/comics/${this.comicId}/rate`, {
            method: 'POST',
            body: JSON.stringify({ rating }),
            headers: {
                'Content-Type': 'application/json'
            }
        });
        return response;
    }

    updateStars() {
        this.stars.forEach((star, index) => {
            const isActive = index < this.userRating;
            star.classList.toggle('active', isActive);
            star.classList.toggle('user-rated', isActive);
            
            // Add accessibility attributes
            star.setAttribute('aria-checked', isActive);
            star.setAttribute('tabindex', '0');
            star.setAttribute('role', 'radio');
        });

        if (this.ratingInput) {
            this.ratingInput.value = this.userRating;
        }
    }

    updateDisplay() {
        if (this.averageDisplay) {
            this.averageDisplay.textContent = this.averageRating.toFixed(1);
            this.averageDisplay.setAttribute('aria-label', `Average rating: ${this.averageRating.toFixed(1)} out of 5`);
        }

        if (this.totalDisplay) {
            this.totalDisplay.textContent = this.formatTotalRatings(this.totalRatings);
            this.totalDisplay.setAttribute('aria-label', `Based on ${this.totalRatings} ratings`);
        }

        // Update container state
        this.ratingContainer.classList.toggle('has-rating', this.userRating > 0);
        this.ratingContainer.classList.toggle('has-many-ratings', this.totalRatings > 10);
    }

    formatTotalRatings(total) {
        if (total >= 1000) {
            return (total / 1000).toFixed(1) + 'k';
        }
        return total.toString();
    }

    createRatingBreakdown() {
        if (!this.options.showBreakdown) return;

        const existingBreakdown = this.ratingContainer.querySelector('.rating-breakdown');
        if (existingBreakdown) {
            existingBreakdown.remove();
        }

        const breakdownHtml = `
            <div class="rating-breakdown animate-fade-in">
                <h4 class="breakdown-title text-sm font-comic font-bold text-comic-yellow mb-3">RATING BREAKDOWN</h4>
                <div class="breakdown-bars space-y-2">
                    ${[5, 4, 3, 2, 1].map(stars => `
                        <div class="rating-bar flex items-center gap-3 text-xs">
                            <span class="rating-label w-8 font-comic font-bold">${stars}★</span>
                            <div class="rating-progress flex-1 bg-gray-700 rounded-full h-2 overflow-hidden">
                                <div class="rating-progress-bar h-full bg-gradient-to-r from-comic-red to-comic-yellow rounded-full transition-all duration-500"
                                     style="width: ${this.getBreakdownPercentage(stars)}%">
                                </div>
                            </div>
                            <span class="rating-count w-12 text-right font-comic">${this.ratingBreakdown[stars] || 0}</span>
                        </div>
                    `).join('')}
                </div>
            </div>
        `;

        this.ratingContainer.insertAdjacentHTML('beforeend', breakdownHtml);
    }

    updateBreakdown() {
        if (!this.options.showBreakdown) return;

        const breakdownBars = this.ratingContainer.querySelectorAll('.rating-progress-bar');
        breakdownBars.forEach((bar, index) => {
            const stars = 5 - index; // Reverse order (5 to 1)
            setTimeout(() => {
                bar.style.width = `${this.getBreakdownPercentage(stars)}%`;
            }, index * 100);
        });

        const ratingCounts = this.ratingContainer.querySelectorAll('.rating-count');
        ratingCounts.forEach((count, index) => {
            const stars = 5 - index;
            count.textContent = this.ratingBreakdown[stars] || 0;
        });
    }

    getBreakdownPercentage(stars) {
        const count = this.ratingBreakdown[stars] || 0;
        if (this.totalRatings === 0) return 0;
        return (count / this.totalRatings) * 100;
    }

    showRatingMessage(message, type = 'info') {
        // Use toast system if available, otherwise create temporary message
        if (window.ComicToasts) {
            ComicToasts.showToast(message, type);
            return;
        }

        const messageEl = document.createElement('div');
        messageEl.className = `rating-message comic-bubble fixed bottom-4 left-1/2 transform -translate-x-1/2 z-50 animate-comic-pulse ${type}`;
        messageEl.textContent = message;
        
        document.body.appendChild(messageEl);

        setTimeout(() => {
            messageEl.remove();
        }, 3000);
    }

    // Public methods
    getRating() {
        return {
            userRating: this.userRating,
            averageRating: this.averageRating,
            totalRatings: this.totalRatings,
            breakdown: this.ratingBreakdown
        };
    }

    setRating(rating) {
        this.userRating = Math.max(1, Math.min(5, rating));
        this.updateStars();
        this.updateDisplay();
    }

    resetRating() {
        this.userRating = 0;
        this.updateStars();
        this.updateDisplay();
    }

    destroy() {
        // Clean up event listeners
        this.stars.forEach(star => {
            star.replaceWith(star.cloneNode(true));
        });
        
        const breakdown = this.ratingContainer.querySelector('.rating-breakdown');
        if (breakdown) {
            breakdown.remove();
        }
    }
}

// Initialize all rating systems on the page
export function initAllRatings() {
    document.querySelectorAll('[data-comic-rating]').forEach(container => {
        const comicId = container.dataset.comicRating;
        const options = JSON.parse(container.dataset.ratingOptions || '{}');
        
        new ComicRating(comicId, options);
    });
}

// Auto-initialize when DOM is ready
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initAllRatings);
} else {
    initAllRatings();
}

// Export for global usage
window.ComicRating = ComicRating;
window.initAllRatings = initAllRatings;