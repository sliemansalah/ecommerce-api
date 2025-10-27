// resources/js/stores/auth.js
import { defineStore } from 'pinia';
import axios from '@/plugins/axios';
import router from '@/router';

export const useAuthStore = defineStore('auth', {
    state: () => ({
        user: JSON.parse(localStorage.getItem('user')) || null,
        token: localStorage.getItem('auth_token') || null,
        loading: false,
        error: null,
    }),

    getters: {
        // التحقق من تسجيل الدخول
        isAuthenticated: (state) => !!state.token,
        
        // الحصول على المستخدم الحالي
        currentUser: (state) => state.user,
        
        // الحصول على أدوار المستخدم
        userRoles: (state) => state.user?.roles || [],
        
        // الحصول على صلاحيات المستخدم
        userPermissions: (state) => {
            if (!state.user) return [];
            
            // جمع الصلاحيات المباشرة
            const directPermissions = state.user.permissions || [];
            
            // جمع صلاحيات الأدوار
            const rolePermissions = state.user.roles?.flatMap(role => role.permissions || []) || [];
            
            // دمج الصلاحيات وإزالة المكرر
            const allPermissions = [...directPermissions, ...rolePermissions];
            const uniquePermissions = [...new Set(allPermissions.map(p => p.name))];
            
            return uniquePermissions;
        },
        
        // التحقق من وجود دور معين
        hasRole: (state) => (role) => {
            return state.user?.roles?.some(r => r.name === role) || false;
        },
        
        // التحقق من وجود صلاحية معينة
        hasPermission: (state) => (permission) => {
            const permissions = state.user?.permissions?.map(p => p.name) || [];
            const rolePermissions = state.user?.roles?.flatMap(role => 
                role.permissions?.map(p => p.name) || []
            ) || [];
            
            const allPermissions = [...permissions, ...rolePermissions];
            
            return allPermissions.includes(permission);
        },
        
        // التحقق من أي صلاحية من مجموعة
        hasAnyPermission: (state) => (permissions) => {
            return permissions.some(permission => 
                state.userPermissions.includes(permission)
            );
        },
        
        // التحقق من جميع الصلاحيات
        hasAllPermissions: (state) => (permissions) => {
            return permissions.every(permission => 
                state.userPermissions.includes(permission)
            );
        },
    },

    actions: {
        // تسجيل الدخول
        async login(credentials) {
            this.loading = true;
            this.error = null;
            
            try {
                const response = await axios.post('/login', credentials);
                
                if (response.data.status === 'success') {
                    const { user, token } = response.data.data;
                    
                    // حفظ البيانات
                    this.user = user;
                    this.token = token;
                    
                    // حفظ في localStorage
                    localStorage.setItem('auth_token', token);
                    localStorage.setItem('user', JSON.stringify(user));
                    
                    return { success: true };
                }
            } catch (error) {
                this.error = error.response?.data?.message || 'فشل تسجيل الدخول';
                return { 
                    success: false, 
                    message: this.error 
                };
            } finally {
                this.loading = false;
            }
        },

        // تسجيل حساب جديد
        async register(userData) {
            this.loading = true;
            this.error = null;
            
            try {
                const response = await axios.post('/register', userData);
                
                if (response.data.status === 'success') {
                    const { user, token } = response.data.data;
                    
                    // حفظ البيانات
                    this.user = user;
                    this.token = token;
                    
                    // حفظ في localStorage
                    localStorage.setItem('auth_token', token);
                    localStorage.setItem('user', JSON.stringify(user));
                    
                    return { success: true };
                }
            } catch (error) {
                this.error = error.response?.data?.message || 'فشل إنشاء الحساب';
                return { 
                    success: false, 
                    message: this.error,
                    errors: error.response?.data?.errors || {}
                };
            } finally {
                this.loading = false;
            }
        },

        // تسجيل الخروج
        async logout() {
            this.loading = true;
            
            try {
                await axios.post('/logout');
            } catch (error) {
                console.error('Logout error:', error);
            } finally {
                // مسح البيانات
                this.user = null;
                this.token = null;
                this.error = null;
                
                // مسح من localStorage
                localStorage.removeItem('auth_token');
                localStorage.removeItem('user');
                
                this.loading = false;
                
                // إعادة التوجيه لصفحة تسجيل الدخول
                router.push({ name: 'login' });
            }
        },

        // جلب بيانات المستخدم الحالي
        async fetchUser() {
            if (!this.token) return;
            
            this.loading = true;
            
            try {
                const response = await axios.get('/me');
                
                if (response.data.status === 'success') {
                    this.user = response.data.data;
                    localStorage.setItem('user', JSON.stringify(this.user));
                }
            } catch (error) {
                console.error('Fetch user error:', error);
                
                // إذا فشل جلب البيانات، قد يكون الـ token منتهي
                if (error.response?.status === 401) {
                    this.logout();
                }
            } finally {
                this.loading = false;
            }
        },

        // تحديث بيانات المستخدم
        updateUser(userData) {
            this.user = { ...this.user, ...userData };
            localStorage.setItem('user', JSON.stringify(this.user));
        },

        // مسح الأخطاء
        clearError() {
            this.error = null;
        },
    },
});
