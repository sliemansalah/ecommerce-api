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
        const token = localStorage.getItem('auth_token');
        
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
        if (error.response && error.response.status === 401) {
            localStorage.removeItem('auth_token');
            localStorage.removeItem('user');
            router.push({ name: 'login' });
        }
        
        // معالجة خطأ 403 - Forbidden
        if (error.response && error.response.status === 403) {
            router.push({ name: 'forbidden' });
        }
        
        // معالجة خطأ 404 - Not Found
        if (error.response && error.response.status === 404) {
            // يمكن عرض رسالة خطأ
        }
        
        // معالجة خطأ 500 - Server Error
        if (error.response && error.response.status === 500) {
            // يمكن عرض رسالة خطأ عامة
        }
        
        return Promise.reject(error);
    }
);

export default axiosInstance;
