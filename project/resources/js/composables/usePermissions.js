import { ref } from 'vue';
import axios from '../plugins/axios';
import { useSnackbar } from './useSnackbar';

export function usePermissions() {
    const { showSuccess, showError } = useSnackbar();
    
    const permissions = ref([]);
    const permission = ref(null);
    const loading = ref(false);

    // جلب قائمة الصلاحيات
    const fetchPermissions = async (params = {}) => {
        loading.value = true;
        try {
            const response = await axios.get('/permissions', { params });
            permissions.value = response.data.data || response.data;
            return response.data;
        } catch (error) {
            console.error('Error fetching permissions:', error);
            showError(error.response?.data?.message || 'فشل في جلب الصلاحيات');
            throw error;
        } finally {
            loading.value = false;
        }
    };

    // جلب صلاحية محددة
    const fetchPermission = async (id) => {
        loading.value = true;
        try {
            const response = await axios.get(`/permissions/${id}`);
            permission.value = response.data.data;
            return response.data.data;
        } catch (error) {
            showError(error.response?.data?.message || 'فشل في جلب بيانات الصلاحية');
            throw error;
        } finally {
            loading.value = false;
        }
    };

    // إنشاء صلاحية جديدة
    const createPermission = async (permissionData) => {
        loading.value = true;
        try {
            const response = await axios.post('/permissions', permissionData);
            showSuccess('تم إنشاء الصلاحية بنجاح');
            return response.data.data;
        } catch (error) {
            showError(error.response?.data?.message || 'فشل في إنشاء الصلاحية');
            throw error;
        } finally {
            loading.value = false;
        }
    };

    // تحديث صلاحية
    const updatePermission = async (id, permissionData) => {
        loading.value = true;
        try {
            const response = await axios.put(`/permissions/${id}`, permissionData);
            showSuccess('تم تحديث الصلاحية بنجاح');
            return response.data.data;
        } catch (error) {
            showError(error.response?.data?.message || 'فشل في تحديث الصلاحية');
            throw error;
        } finally {
            loading.value = false;
        }
    };

    // حذف صلاحية
    const deletePermission = async (id) => {
        loading.value = true;
        try {
            await axios.delete(`/permissions/${id}`);
            showSuccess('تم حذف الصلاحية بنجاح');
        } catch (error) {
            showError(error.response?.data?.message || 'فشل في حذف الصلاحية');
            throw error;
        } finally {
            loading.value = false;
        }
    };

    return {
        permissions,
        permission,
        loading,
        fetchPermissions,
        fetchPermission,
        createPermission,
        updatePermission,
        deletePermission,
    };
}