import axios from 'axios';

const api = axios.create({
    baseURL: '/api',
    withCredentials: true,
    headers: {
        'X-Requested-With': 'XMLHttpRequest',
        'Content-Type': 'application/json',
        'Accept': 'application/json',
    },
});

// Interceptor to handle CSRF token fetch if needed
api.interceptors.request.use(async (config) => {
    // Sanctum handles this via the X-XSRF-TOKEN cookie.
    // Axios withCredentials: true will send it automatically if the path is on the same domain or proxied.
    return config;
}, (error) => {
    return Promise.reject(error);
});

// Add a helper to initialize the session. 
// Note: We use the root path because /sanctum/csrf-cookie is not under the /api/ prefix.
export const ensureCsrf = () => axios.get('/sanctum/csrf-cookie', { withCredentials: true });

export default api;
