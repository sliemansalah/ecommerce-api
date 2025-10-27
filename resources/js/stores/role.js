// resources/js/stores/role.js

import axios from "axios";
import { defineStore } from "pinia";

/**
 * Role Store
 *
 * مسؤول عن إدارة الأدوار (Roles) والصلاحيات (Permissions)
 */
export const useRoleStore = defineStore("role", {
  // ====================================
  // State
  // ====================================
  state: () => ({
    roles: [], // مصفوفة الأدوار
    permissions: {}, // جميع الصلاحيات (مجمعة حسب النوع)
    currentRole: null, // الدور المحدد حالياً
    loading: false,
    error: null,
  }),

  // ====================================
  // Getters
  // ====================================
  getters: {
    /**
     * عدد الأدوار
     */
    rolesCount: (state) => state.roles.length,

    /**
     * البحث عن دور بـ ID
     */
    getRoleById: (state) => (id) => {
      return state.roles.find(role => role.id === id);
    },

    /**
     * البحث عن دور بالاسم
     */
    getRoleByName: (state) => (name) => {
      return state.roles.find(role => role.name === name);
    },

    /**
     * قائمة أسماء الأدوار (للاستخدام في Select)
     */
    roleNames: (state) => {
      return state.roles.map(role => ({
        id: role.id,
        name: role.name,
      }));
    },

    /**
     * جميع الصلاحيات كمصفوفة مسطحة
     */
    allPermissions: (state) => {
      const permissions = [];
      Object.keys(state.permissions).forEach(key => {
        permissions.push(...state.permissions[key]);
      });
      return permissions;
    },
  },

  // ====================================
  // Actions
  // ====================================
  actions: {
    /**
     * جلب جميع الأدوار
     *
     * GET /api/roles
     */
    async fetchRoles() {
      this.loading = true;
      this.error = null;

      try {
        const response = await axios.get("/api/roles");
        this.roles = response.data.data;

        return { success: true, data: response.data.data };
      } catch (error) {
        this.error = error.response?.data?.message || "Failed to fetch roles";
        return { success: false, message: this.error };
      } finally {
        this.loading = false;
      }
    },

    /**
     * جلب جميع الصلاحيات المتاحة
     *
     * GET /api/permissions
     *
     * ترجع الصلاحيات مجمعة حسب النوع:
     * {
     *   products: [{ id: 1, name: 'products.view' }, ...],
     *   categories: [{ id: 5, name: 'categories.view' }, ...],
     *   ...
     * }
     */
    async fetchPermissions() {
      this.loading = true;
      this.error = null;

      try {
        const response = await axios.get("/api/permissions");
        this.permissions = response.data.data;

        return { success: true, data: response.data.data };
      } catch (error) {
        this.error = error.response?.data?.message || "Failed to fetch permissions";
        return { success: false, message: this.error };
      } finally {
        this.loading = false;
      }
    },

    /**
     * جلب دور واحد بـ ID
     *
     * GET /api/roles/{id}
     */
    async fetchRole(id) {
      this.loading = true;
      this.error = null;

      try {
        const response = await axios.get(`/api/roles/${id}`);
        this.currentRole = response.data.data;

        return { success: true, data: response.data.data };
      } catch (error) {
        this.error = error.response?.data?.message || "Failed to fetch role";
        return { success: false, message: this.error };
      } finally {
        this.loading = false;
      }
    },

    /**
     * إضافة دور جديد
     *
     * POST /api/roles
     *
     * @param {Object} roleData
     * @param {string} roleData.name - اسم الدور
     * @param {Array} roleData.permissions - مصفوفة IDs الصلاحيات
     */
    async createRole(roleData) {
      this.loading = true;
      this.error = null;

      try {
        const response = await axios.post("/api/roles", roleData);

        // إضافة الدور الجديد للمصفوفة
        this.roles.push(response.data.data);

        return {
          success: true,
          data: response.data.data,
          message: "Role created successfully",
        };
      } catch (error) {
        this.error = error.response?.data?.message || "Failed to create role";

        return {
          success: false,
          message: this.error,
          errors: error.response?.data?.errors,
        };
      } finally {
        this.loading = false;
      }
    },

    /**
     * تعديل دور موجود
     *
     * PUT /api/roles/{id}
     */
    async updateRole(id, roleData) {
      this.loading = true;
      this.error = null;

      try {
        const response = await axios.put(`/api/roles/${id}`, roleData);

        // تحديث الدور في المصفوفة
        const index = this.roles.findIndex(r => r.id === id);
        if (index !== -1) {
          this.roles[index] = response.data.data;
        }

        return {
          success: true,
          data: response.data.data,
          message: "Role updated successfully",
        };
      } catch (error) {
        this.error = error.response?.data?.message || "Failed to update role";

        return {
          success: false,
          message: this.error,
          errors: error.response?.data?.errors,
        };
      } finally {
        this.loading = false;
      }
    },

    /**
     * حذف دور
     *
     * DELETE /api/roles/{id}
     */
    async deleteRole(id) {
      this.loading = true;
      this.error = null;

      try {
        await axios.delete(`/api/roles/${id}`);

        // حذف الدور من المصفوفة
        this.roles = this.roles.filter(r => r.id !== id);

        return {
          success: true,
          message: "Role deleted successfully",
        };
      } catch (error) {
        this.error = error.response?.data?.message || "Failed to delete role";

        return {
          success: false,
          message: this.error,
        };
      } finally {
        this.loading = false;
      }
    },

    /**
     * مسح حالة الخطأ
     */
    clearError() {
      this.error = null;
    },

    /**
     * مسح الدور الحالي
     */
    clearCurrentRole() {
      this.currentRole = null;
    },
  },
});
