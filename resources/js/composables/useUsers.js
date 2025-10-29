import { ref } from 'vue';
import axios from '../plugins/axios'; // ✅ استيراد الـ instance المُعدّ (الأهم!)
import { useSnackbar } from './useSnackbar';

export function useUsers() {
    const { showSuccess, showError } = useSnackbar();
    
    const users = ref([]);
    const user = ref(null);
    const loading = ref(false);
    const pagination = ref({
        page: 1,
        perPage: 15,
        total: 0,
        lastPage: 1,
    });
    const stats = ref(null);

    // جلب قائمة المستخدمين
    const fetchUsers = async (params = {}) => {
        loading.value = true;
        try {
            const response = await axios.get('/users', { params }); // ✅ بدون /api
            users.value = response.data.data;
            
            // تحديث معلومات الـ Pagination
            if (response.data.meta) {
                pagination.value = {
                    page: response.data.meta.current_page,
                    perPage: response.data.meta.per_page,
                    total: response.data.meta.total,
                    lastPage: response.data.meta.last_page,
                };
            }
            
            return response.data;
        } catch (error) {
            showError(error.response?.data?.message || 'فشل في جلب المستخدمين');
            throw error;
        } finally {
            loading.value = false;
        }
    };

    // جلب مستخدم محدد
    const fetchUser = async (id) => {
        loading.value = true;
        try {
            const response = await axios.get(`/users/${id}`); // ✅ بدون /api
            user.value = response.data.data;
            return response.data.data;
        } catch (error) {
            showError(error.response?.data?.message || 'فشل في جلب بيانات المستخدم');
            throw error;
        } finally {
            loading.value = false;
        }
    };

    // إنشاء مستخدم جديد
    const createUser = async (userData) => {
        loading.value = true;
        try {
            const response = await axios.post('/users', userData); // ✅ بدون /api
            showSuccess('تم إنشاء المستخدم بنجاح');
            return response.data.data;
        } catch (error) {
            showError(error.response?.data?.message || 'فشل في إنشاء المستخدم');
            throw error;
        } finally {
            loading.value = false;
        }
    };

    // تحديث مستخدم
    const updateUser = async (id, userData) => {
        loading.value = true;
        try {
            const response = await axios.put(`/users/${id}`, userData); // ✅ بدون /api
            showSuccess('تم تحديث المستخدم بنجاح');
            return response.data.data;
        } catch (error) {
            showError(error.response?.data?.message || 'فشل في تحديث المستخدم');
            throw error;
        } finally {
            loading.value = false;
        }
    };

    // حذف مستخدم
    const deleteUser = async (id) => {
        loading.value = true;
        try {
            await axios.delete(`/users/${id}`); // ✅ بدون /api
            showSuccess('تم حذف المستخدم بنجاح');
        } catch (error) {
            showError(error.response?.data?.message || 'فشل في حذف المستخدم');
            throw error;
        } finally {
            loading.value = false;
        }
    };

    // تفعيل/تعطيل مستخدم
    const toggleUserStatus = async (id) => {
        loading.value = true;
        try {
            const response = await axios.post(`/users/${id}/toggle-status`); // ✅ بدون /api
            showSuccess('تم تغيير حالة المستخدم بنجاح');
            return response.data.data;
        } catch (error) {
            showError(error.response?.data?.message || 'فشل في تغيير حالة المستخدم');
            throw error;
        } finally {
            loading.value = false;
        }
    };

    // جلب إحصائيات المستخدمين
    const fetchStats = async () => {
        loading.value = true;
        try {
            const response = await axios.get('/users-stats'); // ✅ بدون /api
            stats.value = response.data;
            return response.data;
        } catch (error) {
            showError(error.response?.data?.message || 'فشل في جلب الإحصائيات');
            throw error;
        } finally {
            loading.value = false;
        }
    };

    return {
        users,
        user,
        loading,
        pagination,
        stats,
        fetchUsers,
        fetchUser,
        createUser,
        updateUser,
        deleteUser,
        toggleUserStatus,
        fetchStats,
    };
}