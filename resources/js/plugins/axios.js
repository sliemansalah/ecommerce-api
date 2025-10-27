// resources/js/plugins/axios.js
import axios from 'axios';
import router from '@/router';

// إنشاء instance من Axios
const axiosInstance = axios.create({
    baseURL: '/api',
    timeout: 30000,
    headers: {
        'Content-Type': 'application/json',
        'Accept': 'application/json',
        'X-Requested-With': 'XMLHttpRequest',
    },
});

// Request Interceptor - إضافة Token تلقائياً
axiosInstance.interceptors.request.use(
    (config) => {
        // ✅ تغيير من 'auth_token' إلى 'token'
        const token = localStorage.getItem('token');
        
        if (token) {
            config.headers.Authorization = `Bearer ${token}`;
        }
        
        return config;
    },
    (error) => {
        return Promise.reject(error);
    }
);

// Response Interceptor - معالجة الأخطاء
axiosInstance.interceptors.response.use(
    (response) => {
        return response;
    },
    (error) => {
        // معالجة خطأ 401 - Unauthorized
        if (error.response?.status === 401) {
            // ✅ تغيير من 'auth_token' إلى 'token'
            localStorage.removeItem('token');
            localStorage.removeItem('user');
            
            // منع إعادة التوجيه المتكررة
            if (router.currentRoute.value.name !== 'login') {
                router.push({ name: 'login' });
            }
        }
        
        // معالجة خطأ 403 - Forbidden
        if (error.response?.status === 403) {
            router.push({ name: 'forbidden' });
        }
        
        // معالجة خطأ 404 - Not Found
        if (error.response?.status === 404) {
            console.error('Resource not found:', error.config?.url);
        }
        
        // معالجة خطأ 500 - Server Error
        if (error.response?.status === 500) {
            console.error('Server error:', error.response.data);
        }
        
        return Promise.reject(error);
    }
);

export default axiosInstance;