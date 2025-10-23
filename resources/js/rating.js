export function initRating(comicId) {
    const ratingContainer = document.querySelector('.rating-container');
    if (!ratingContainer) return;

    const stars = ratingContainer.querySelectorAll('.star');
    const averageDisplay = ratingContainer.querySelector('.average-rating');
    const totalDisplay = ratingContainer.querySelector('.total-ratings');

    let userRating = 0;

    // Load initial rating
    fetch(`/comics/${comicId}/rating`)
        .then(response => response.json())
        .then(data => {
            updateStars(data.userRating || 0);
            updateAverageDisplay(data.average, data.totalRatings);
        });

    // Handle star hover
    stars.forEach((star, index) => {
        star.addEventListener('mouseover', () => {
            updateStars(index + 1);
        });

        star.addEventListener('mouseout', () => {
            updateStars(userRating);
        });

        star.addEventListener('click', () => {
            const rating = index + 1;
            submitRating(rating);
        });
    });

    function updateStars(rating) {
        stars.forEach((star, index) => {
            star.classList.toggle('active', index < rating);
        });
    }

    function updateAverageDisplay(average, total) {
        if (averageDisplay) {
            averageDisplay.textContent = average;
        }
        if (totalDisplay) {
            totalDisplay.textContent = total;
        }
    }

    function submitRating(rating) {
        fetch(`/comics/${comicId}/rating`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            },
            body: JSON.stringify({ rating })
        })
        .then(response => response.json())
        .then(data => {
            userRating = rating;
            updateStars(rating);
            updateAverageDisplay(data.average, data.totalRatings);
        })
        .catch(error => {
            console.error('Error submitting rating:', error);
        });
    }
}