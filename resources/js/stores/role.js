// resources/js/stores/role.js

import { defineStore } from 'pinia';
import axios from '@/plugins/axios';

export const useRoleStore = defineStore('role', {
    // ====================================
    // State
    // ====================================
    state: () => ({
        roles: [],
        permissions: {},
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
            sortBy: 'created_at',
            sortOrder: 'desc',
        },
    }),

    // ====================================
    // Getters
    // ====================================
    getters: {
        /**
         * الحصول على دور حسب ID
         */
        getRoleById: (state) => (id) => {
            return state.roles.find(role => role.id === id);
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
         * جلب الأدوار مع Pagination
         */
        async fetchRoles(page = 1) {
            this.loading = true;
            this.error = null;
            
            try {
                const params = {
                    page: page,
                    per_page: this.pagination.perPage,
                    search: this.filters.search || undefined,
                    sort_by: this.filters.sortBy,
                    sort_order: this.filters.sortOrder,
                };

                const response = await axios.get('/api/roles', { params });
                
                console.log('Fetch Roles Response:', response.data);
                
                // تخزين الأدوار
                this.roles = response.data.data || [];
                
                // تحديث pagination
                if (response.data.pagination) {
                    this.pagination = response.data.pagination;
                }
                
                return { success: true, data: this.roles };
            } catch (error) {
                this.error = error.response?.data?.message || 'Failed to fetch roles';
                console.error('Fetch roles error:', error);
                
                return { 
                    success: false, 
                    message: this.error 
                };
            } finally {
                this.loading = false;
            }
        },

        /**
         * جلب جميع الصلاحيات مجمعة
         */
        async fetchPermissions() {
            try {
                const response = await axios.get('/api/permissions');
                this.permissions = response.data.data || {};
                
                return { success: true, data: this.permissions };
            } catch (error) {
                console.error('Fetch permissions error:', error);
                return { success: false };
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
            await this.fetchRoles(1);
        },

        /**
         * تغيير عدد العناصر في الصفحة
         */
        async changePerPage(perPage) {
            console.log('Changing per_page to:', perPage);
            this.pagination.perPage = perPage;
            this.pagination.currentPage = 1; // إعادة تعيين للصفحة الأولى
            await this.fetchRoles(1);
        },

        /**
         * الانتقال للصفحة التالية
         */
        async nextPage() {
            if (this.hasNextPage) {
                await this.fetchRoles(this.pagination.currentPage + 1);
            }
        },

        /**
         * الانتقال للصفحة السابقة
         */
        async prevPage() {
            if (this.hasPrevPage) {
                await this.fetchRoles(this.pagination.currentPage - 1);
            }
        },

        /**
         * إنشاء دور جديد
         */
        async createRole(roleData) {
            this.loading = true;
            
            try {
                const response = await axios.post('/api/roles', roleData);
                
                // إعادة تحميل القائمة
                await this.fetchRoles(this.pagination.currentPage);
                
                return { 
                    success: true, 
                    data: response.data.data 
                };
            } catch (error) {
                return {
                    success: false,
                    message: error.response?.data?.message || 'Failed to create role',
                    errors: error.response?.data?.errors,
                };
            } finally {
                this.loading = false;
            }
        },

        /**
         * تحديث دور
         */
        async updateRole(id, roleData) {
            this.loading = true;
            
            try {
                const response = await axios.put(`/api/roles/${id}`, roleData);
                
                // تحديث في القائمة المحلية
                const index = this.roles.findIndex(r => r.id === id);
                if (index !== -1) {
                    this.roles[index] = response.data.data;
                }
                
                return { 
                    success: true, 
                    data: response.data.data 
                };
            } catch (error) {
                return {
                    success: false,
                    message: error.response?.data?.message || 'Failed to update role',
                    errors: error.response?.data?.errors,
                };
            } finally {
                this.loading = false;
            }
        },

        /**
         * حذف دور
         */
        async deleteRole(id) {
            this.loading = true;
            
            try {
                await axios.delete(`/api/roles/${id}`);
                
                // إزالة من القائمة المحلية
                this.roles = this.roles.filter(r => r.id !== id);
                
                // إعادة تحميل إذا كانت الصفحة فارغة
                if (this.roles.length === 0 && this.pagination.currentPage > 1) {
                    await this.fetchRoles(this.pagination.currentPage - 1);
                }
                
                return { success: true };
            } catch (error) {
                return {
                    success: false,
                    message: error.response?.data?.message || 'Failed to delete role',
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
                sortBy: 'created_at',
                sortOrder: 'desc',
            };
        },
    },
});