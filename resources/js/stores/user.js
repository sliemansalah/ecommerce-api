// resources/js/stores/user.js

import { defineStore } from 'pinia';
import axios from '@/plugins/axios';

export const useUserStore = defineStore('user', {
    // ====================================
    // State
    // ====================================
    state: () => ({
        users: [],
        loading: false,
        error: null,
        
        // Pagination
        pagination: {
            currentPage: 1,
            lastPage: 1,
            perPage: 10,
            total: 0,
            from: 0,
            to: 0,
        },
        
        // Filters
        filters: {
            search: '',
            roleId: null,
            sortBy: 'created_at',
            sortOrder: 'desc',
        },
    }),

    // ====================================
    // Getters
    // ====================================
    getters: {
        /**
         * الحصول على مستخدم حسب ID
         */
        getUserById: (state) => (id) => {
            return state.users.find(user => user.id === id);
        },

        /**
         * هل يوجد صفحة تالية؟
         */
        hasNextPage: (state) => {
            return state.pagination.currentPage < state.pagination.lastPage;
        },

        /**
         * هل يوجد صفحة سابقة؟
         */
        hasPrevPage: (state) => {
            return state.pagination.currentPage > 1;
        },
    },

    // ====================================
    // Actions
    // ====================================
    actions: {
        /**
         * جلب المستخدمين مع Pagination
         */
        async fetchUsers(page = 1) {
            this.loading = true;
            this.error = null;
            
            try {
                const params = {
                    page: page,
                    per_page: this.pagination.perPage,
                    search: this.filters.search || undefined,
                    role_id: this.filters.roleId || undefined,
                    sort_by: this.filters.sortBy,
                    sort_order: this.filters.sortOrder,
                };

                const response = await axios.get('/api/users', { params });
                
                console.log('Fetch Users Response:', response.data);
                
                // تخزين المستخدمين
                this.users = response.data.data || [];
                
                // تحديث pagination
                if (response.data.pagination) {
                    this.pagination = response.data.pagination;
                }
                
                return { success: true, data: this.users };
            } catch (error) {
                this.error = error.response?.data?.message || 'Failed to fetch users';
                console.error('Fetch users error:', error);
                
                return { 
                    success: false, 
                    message: this.error 
                };
            } finally {
                this.loading = false;
            }
        },

        /**
         * تطبيق الفلاتر والبحث
         */
        async applyFilters(filters = {}) {
            this.filters = {
                ...this.filters,
                ...filters,
            };
            
            // إعادة التحميل من الصفحة الأولى
            await this.fetchUsers(1);
        },

        /**
         * تغيير عدد العناصر في الصفحة
         */
        async changePerPage(perPage) {
            this.pagination.perPage = perPage;
            await this.fetchUsers(1);
        },

        /**
         * الانتقال للصفحة التالية
         */
        async nextPage() {
            if (this.hasNextPage) {
                await this.fetchUsers(this.pagination.currentPage + 1);
            }
        },

        /**
         * الانتقال للصفحة السابقة
         */
        async prevPage() {
            if (this.hasPrevPage) {
                await this.fetchUsers(this.pagination.currentPage - 1);
            }
        },

        /**
         * إنشاء مستخدم جديد
         */
        async createUser(userData) {
            this.loading = true;
            
            try {
                const response = await axios.post('/api/users', userData);
                
                // إعادة تحميل القائمة
                await this.fetchUsers(this.pagination.currentPage);
                
                return { 
                    success: true, 
                    data: response.data.data 
                };
            } catch (error) {
                return {
                    success: false,
                    message: error.response?.data?.message || 'Failed to create user',
                    errors: error.response?.data?.errors,
                };
            } finally {
                this.loading = false;
            }
        },

        /**
         * تحديث مستخدم
         */
        async updateUser(id, userData) {
            this.loading = true;
            
            try {
                const response = await axios.put(`/api/users/${id}`, userData);
                
                // تحديث في القائمة المحلية
                const index = this.users.findIndex(u => u.id === id);
                if (index !== -1) {
                    this.users[index] = response.data.data;
                }
                
                return { 
                    success: true, 
                    data: response.data.data 
                };
            } catch (error) {
                return {
                    success: false,
                    message: error.response?.data?.message || 'Failed to update user',
                    errors: error.response?.data?.errors,
                };
            } finally {
                this.loading = false;
            }
        },

        /**
         * حذف مستخدم
         */
        async deleteUser(id) {
            this.loading = true;
            
            try {
                await axios.delete(`/api/users/${id}`);
                
                // إزالة من القائمة المحلية
                this.users = this.users.filter(u => u.id !== id);
                
                // إعادة تحميل إذا كانت الصفحة فارغة
                if (this.users.length === 0 && this.pagination.currentPage > 1) {
                    await this.fetchUsers(this.pagination.currentPage - 1);
                }
                
                return { success: true };
            } catch (error) {
                return {
                    success: false,
                    message: error.response?.data?.message || 'Failed to delete user',
                };
            } finally {
                this.loading = false;
            }
        },

        /**
         * مسح الفلاتر
         */
        clearFilters() {
            this.filters = {
                search: '',
                roleId: null,
                sortBy: 'created_at',
                sortOrder: 'desc',
            };
        },
    },
});