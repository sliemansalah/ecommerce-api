import { ref } from 'vue';
import axios from '../plugins/axios'; // ✅ استيراد الـ instance المُعدّ
import { useSnackbar } from './useSnackbar';

export function useRoles() {
    const { showSuccess, showError } = useSnackbar();
    
    const roles = ref([]);
    const role = ref(null);
    const loading = ref(false);
    const pagination = ref({
        page: 1,
        perPage: 15,
        total: 0,
        lastPage: 1,
    });

    // جلب قائمة الأدوار
    const fetchRoles = async (params = {}) => {
        loading.value = true;
        try {
            const response = await axios.get('/roles', { params }); // ✅ بدون /api
            
            if (response.data.data) {
                roles.value = response.data.data;
                
                // تحديث Pagination
                if (response.data.meta) {
                    pagination.value = {
                        page: response.data.meta.current_page,
                        perPage: response.data.meta.per_page,
                        total: response.data.meta.total,
                        lastPage: response.data.meta.last_page,
                    };
                }
            } else {
                roles.value = response.data;
            }
            
            return response.data;
        } catch (error) {
            console.error('Error fetching roles:', error);
            showError(error.response?.data?.message || 'فشل في جلب الأدوار');
            throw error;
        } finally {
            loading.value = false;
        }
    };

    // جلب دور محدد
    const fetchRole = async (id) => {
        loading.value = true;
        try {
            const response = await axios.get(`/roles/${id}`); // ✅ بدون /api
            role.value = response.data.data;
            return response.data.data;
        } catch (error) {
            showError(error.response?.data?.message || 'فشل في جلب بيانات الدور');
            throw error;
        } finally {
            loading.value = false;
        }
    };

    // إنشاء دور جديد
    const createRole = async (roleData) => {
        loading.value = true;
        try {
            const response = await axios.post('/roles', roleData); // ✅ بدون /api
            showSuccess('تم إنشاء الدور بنجاح');
            return response.data.data;
        } catch (error) {
            showError(error.response?.data?.message || 'فشل في إنشاء الدور');
            throw error;
        } finally {
            loading.value = false;
        }
    };

    // تحديث دور
    const updateRole = async (id, roleData) => {
        loading.value = true;
        try {
            const response = await axios.put(`/roles/${id}`, roleData); // ✅ بدون /api
            showSuccess('تم تحديث الدور بنجاح');
            return response.data.data;
        } catch (error) {
            showError(error.response?.data?.message || 'فشل في تحديث الدور');
            throw error;
        } finally {
            loading.value = false;
        }
    };

    // حذف دور
    const deleteRole = async (id) => {
        loading.value = true;
        try {
            await axios.delete(`/roles/${id}`); // ✅ بدون /api
            showSuccess('تم حذف الدور بنجاح');
        } catch (error) {
            showError(error.response?.data?.message || 'فشل في حذف الدور');
            throw error;
        } finally {
            loading.value = false;
        }
    };

    return {
        roles,
        role,
        loading,
        pagination,
        fetchRoles,
        fetchRole,
        createRole,
        updateRole,
        deleteRole,
    };
}