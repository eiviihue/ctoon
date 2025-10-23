import axios from 'axios';

// Configure axios globally
window.axios = axios;
window.axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';

// Add CSRF token to all requests
const token = document.querySelector('meta[name="csrf-token"]');
if (token) {
    window.axios.defaults.headers.common['X-CSRF-TOKEN'] = token.content;
} else {
    console.error('CSRF token not found');
}

// Add response interceptor for common error handling
axios.interceptors.response.use(
    response => response,
    error => {
        if (error.response) {
            // Handle specific HTTP errors
            switch (error.response.status) {
                case 419: // CSRF token mismatch
                    window.location.reload();
                    break;
                case 401: // Unauthorized
                    window.location.href = '/login';
                    break;
                case 403: // Forbidden
                    console.error('Access denied');
                    break;
                case 422: // Validation errors
                    return Promise.reject(error.response.data);
                default:
                    console.error('API Error:', error.response.data);
            }
        }
        return Promise.reject(error);
    }
);

// Enable custom events for real-time updates if Echo is configured
// import Echo from 'laravel-echo';
// import Pusher from 'pusher-js';
// window.Pusher = Pusher;
// window.Echo = new Echo({
//     broadcaster: 'pusher',
//     key: import.meta.env.VITE_PUSHER_APP_KEY,
//     cluster: import.meta.env.VITE_PUSHER_APP_CLUSTER,
//     forceTLS: true
// });
