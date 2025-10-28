import api from './api';

export const authService = {
    // تسجيل الدخول
    async login(credentials) {
        const response = await api.post('/login', credentials);
        if (response.data.token) {
            localStorage.setItem('auth_token', response.data.token);
            localStorage.setItem('user', JSON.stringify(response.data.user));
        }
        return response.data;
    },

    // تسجيل الخروج
    async logout() {
        await api.post('/logout');
        localStorage.removeItem('auth_token');
        localStorage.removeItem('user');
    },

    // الحصول على المستخدم الحالي
    async getCurrentUser() {
        const response = await api.get('/me');
        localStorage.setItem('user', JSON.stringify(response.data.user));
        return response.data.user;
    },

    // تحديث الملف الشخصي
    async updateProfile(data) {
        const response = await api.put('/profile', data);
        localStorage.setItem('user', JSON.stringify(response.data.user));
        return response.data;
    },

    // التحقق من تسجيل الدخول
    isAuthenticated() {
        return !!localStorage.getItem('auth_token');
    },

    // الحصول على المستخدم من localStorage
    getUser() {
        const user = localStorage.getItem('user');
        return user ? JSON.parse(user) : null;
    },
};