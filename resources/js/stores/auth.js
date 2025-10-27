// resources/js/stores/auth.js

import { defineStore } from 'pinia';
import axios from 'axios';
import router from '@/router';

export const useAuthStore = defineStore('auth', {
    state: () => ({
        user: JSON.parse(localStorage.getItem('user')) || null,
        token: localStorage.getItem('token') || null,
    }),

    getters: {
        currentUser: (state) => state.user,
        isAuthenticated: (state) => !!state.token && !!state.user,
        
        hasPermission: (state) => (permission) => {
            if (!state.user) return false;
            
            // Super Admin له جميع الصلاحيات
            if (state.user.roles?.some(role => role.name === 'super_admin')) {
                return true;
            }
            
            // التحقق من الصلاحيات المباشرة
            if (state.user.permissions?.some(p => p.name === permission)) {
                return true;
            }
            
            // التحقق من صلاحيات الأدوار
            return state.user.roles?.some(role => 
                role.permissions?.some(p => p.name === permission)
            ) || false;
        },
    },

    actions: {
        /**
         * تسجيل الدخول
         */
        async login(credentials) {
            try {
                const response = await axios.post('/api/login', credentials);
                
                const { token, user } = response.data.data;
                
                // حفظ البيانات في State
                this.token = token;
                this.user = user;
                
                // حفظ في localStorage
                localStorage.setItem('token', token);
                localStorage.setItem('user', JSON.stringify(user));
                
                // إعداد Axios header
                axios.defaults.headers.common['Authorization'] = `Bearer ${token}`;
                
                return { success: true, user };
            } catch (error) {
                console.error('Login error:', error);
                return {
                    success: false,
                    message: error.response?.data?.message || 'فشل تسجيل الدخول',
                };
            }
        },

        /**
         * التسجيل
         */
        async register(userData) {
            try {
                const response = await axios.post('/api/register', userData);
                
                const { token, user } = response.data.data;
                
                // حفظ البيانات
                this.token = token;
                this.user = user;
                
                localStorage.setItem('token', token);
                localStorage.setItem('user', JSON.stringify(user));
                
                axios.defaults.headers.common['Authorization'] = `Bearer ${token}`;
                
                return { success: true, user };
            } catch (error) {
                return {
                    success: false,
                    message: error.response?.data?.message || 'فشل التسجيل',
                    errors: error.response?.data?.errors,
                };
            }
        },

        /**
         * تسجيل الخروج
         */
        async logout() {
            try {
                await axios.post('/api/logout');
            } catch (error) {
                console.error('Logout error:', error);
            } finally {
                // مسح البيانات
                this.token = null;
                this.user = null;
                
                localStorage.removeItem('token');
                localStorage.removeItem('user');
                
                delete axios.defaults.headers.common['Authorization'];
                
                router.push({ name: 'login' });
            }
        },

        /**
         * جلب بيانات المستخدم الحالي
         */
        async fetchUser() {
            try {
                const response = await axios.get('/api/me');
                this.user = response.data.data;
                localStorage.setItem('user', JSON.stringify(response.data.data));
                return { success: true };
            } catch (error) {
                return { success: false };
            }
        },

        /**
         * تهيئة Auth من localStorage
         */
        initAuth() {
            const token = localStorage.getItem('token');
            const user = localStorage.getItem('user');
            
            if (token && user) {
                this.token = token;
                this.user = JSON.parse(user);
                axios.defaults.headers.common['Authorization'] = `Bearer ${token}`;
            }
        },
    },
});