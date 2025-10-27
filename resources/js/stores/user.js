// resources/js/stores/user.js

import axios from "axios";
import { defineStore } from "pinia";

/**
 * User Store
 *
 * هذا الـ Store مسؤول عن إدارة حالة المستخدمين في التطبيق
 * يحتوي على:
 * - state: البيانات (المستخدمين، حالة التحميل، الأخطاء)
 * - getters: دوال للحصول على بيانات معدّلة
 * - actions: دوال لتنفيذ العمليات (fetch, create, update, delete)
 */
export const useUserStore = defineStore("user", {
  // ====================================
  // State - الحالة
  // ====================================
  // البيانات التي سنخزنها في الـ Store
  state: () => ({
    users: [], // مصفوفة المستخدمين
    currentUser: null, // المستخدم المحدد حالياً
    loading: false, // حالة التحميل
    error: null, // رسالة الخطأ
  }),

  // ====================================
  // Getters - الدوال المحسوبة
  // ====================================
  // مثل computed properties، لكن للـ Store
  getters: {
    /**
     * عدد المستخدمين الكلي
     */
    usersCount: (state) => state.users.length,

    /**
     * البحث عن مستخدم بـ ID
     */
    getUserById: (state) => (id) => {
      return state.users.find(user => user.id === id);
    },

    /**
     * تصفية المستخدمين حسب الدور
     */
    getUsersByRole: (state) => (roleName) => {
      return state.users.filter(user => user.roles?.some(role => role.name === roleName));
    },
  },

  // ====================================
  // Actions - الإجراءات
  // ====================================
  // الدوال التي تُنفذ العمليات
  actions: {
    /**
     * جلب جميع المستخدمين من الـ API
     *
     * GET /api/users
     */
    async fetchUsers() {
      this.loading = true;
      this.error = null;

      try {
        // إرسال طلب GET للـ API
        const response = await axios.get("/api/users");

        console.log("Fetch Users Response:", response.data);

        // تخزين المستخدمين في الـ state
        // تحقق من البنية: response.data.data أو response.data
        this.users = response.data.data || response.data || [];

        console.log("Users stored:", this.users);

        return { success: true, data: this.users };
      } catch (error) {
        // في حالة الخطأ
        this.error = error.response?.data?.message || "Failed to fetch users";
        console.error("Fetch users error:", error);

        return {
          success: false,
          message: this.error,
        };
      } finally {
        // إيقاف حالة التحميل
        this.loading = false;
      }
    },

    /**
     * جلب مستخدم واحد بـ ID
     *
     * GET /api/users/{id}
     */
    async fetchUser(id) {
      this.loading = true;
      this.error = null;

      try {
        const response = await axios.get(`/api/users/${id}`);
        this.currentUser = response.data.data;

        return { success: true, data: response.data.data };
      } catch (error) {
        this.error = error.response?.data?.message || "Failed to fetch user";
        return { success: false, message: this.error };
      } finally {
        this.loading = false;
      }
    },

    /**
     * إضافة مستخدم جديد
     *
     * POST /api/users
     *
     * @param {Object} userData - بيانات المستخدم
     * @param {string} userData.name - الاسم
     * @param {string} userData.email - البريد الإلكتروني
     * @param {string} userData.password - كلمة المرور
     * @param {Array} userData.roles - مصفوفة IDs الأدوار
     */
    async createUser(userData) {
      this.loading = true;
      this.error = null;

      try {
        const response = await axios.post("/api/users", userData);

        // إضافة المستخدم الجديد للمصفوفة
        this.users.push(response.data.data);

        return {
          success: true,
          data: response.data.data,
          message: "User created successfully",
        };
      } catch (error) {
        this.error = error.response?.data?.message || "Failed to create user";

        return {
          success: false,
          message: this.error,
          errors: error.response?.data?.errors, // أخطاء التحقق
        };
      } finally {
        this.loading = false;
      }
    },

    /**
     * تعديل مستخدم موجود
     *
     * PUT /api/users/{id}
     *
     * @param {number} id - رقم المستخدم
     * @param {Object} userData - البيانات المُعدّلة
     */
    async updateUser(id, userData) {
      this.loading = true;
      this.error = null;

      try {
        const response = await axios.put(`/api/users/${id}`, userData);

        // تحديث المستخدم في المصفوفة
        const index = this.users.findIndex(u => u.id === id);
        if (index !== -1) {
          this.users[index] = response.data.data;
        }

        return {
          success: true,
          data: response.data.data,
          message: "User updated successfully",
        };
      } catch (error) {
        this.error = error.response?.data?.message || "Failed to update user";

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
     * حذف مستخدم
     *
     * DELETE /api/users/{id}
     *
     * @param {number} id - رقم المستخدم
     */
    async deleteUser(id) {
      this.loading = true;
      this.error = null;

      try {
        await axios.delete(`/api/users/${id}`);

        // حذف المستخدم من المصفوفة
        this.users = this.users.filter(u => u.id !== id);

        return {
          success: true,
          message: "User deleted successfully",
        };
      } catch (error) {
        this.error = error.response?.data?.message || "Failed to delete user";

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
     * مسح المستخدم الحالي
     */
    clearCurrentUser() {
      this.currentUser = null;
    },
  },
});

/**
 * كيف تستخدم الـ Store في Component؟
 *
 * في أي component:
 *
 * <script setup>
 * import { useUserStore } from '@/stores/user';
 *
 * const userStore = useUserStore();
 *
 * // جلب المستخدمين
 * await userStore.fetchUsers();
 *
 * // الوصول للبيانات
 * console.log(userStore.users);
 * console.log(userStore.loading);
 *
 * // استخدام getter
 * console.log(userStore.usersCount);
 * </script>
 */
