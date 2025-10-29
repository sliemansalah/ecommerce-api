import { ref, computed } from 'vue';
import { useRouter } from 'vue-router';
import axios from '../plugins/axios';
import { useSnackbar } from './useSnackbar';

export function useAuth() {
    const router = useRouter();
    const { showSuccess, showError } = useSnackbar();
    
    const user = ref(null);
    const loading = ref(false);

    // ✅ التحقق من وجود Token في localStorage
    const isAuthenticated = computed(() => {
        return !!localStorage.getItem('token');
    });

    // ✅ تهيئة المستخدم من localStorage عند بدء التطبيق
    const initAuth = () => {
        const storedUser = localStorage.getItem('user');
        const storedToken = localStorage.getItem('token');
        
        if (storedUser && storedToken) {
            try {
                user.value = JSON.parse(storedUser);
            } catch (error) {
                console.error('Error parsing stored user:', error);
                localStorage.removeItem('user');
                localStorage.removeItem('token');
            }
        }
    };

    // ✅ تسجيل الدخول
    const login = async (credentials) => {
        loading.value = true;
        try {
            const response = await axios.post('/login', credentials);
            
            const { user: userData, token } = response.data;
            
            // ✅ حفظ الـ Token والمستخدم
            localStorage.setItem('token', token);
            localStorage.setItem('user', JSON.stringify(userData));
            
            user.value = userData;
            
            showSuccess('تم تسجيل الدخول بنجاح');
            
            // الانتقال للصفحة الرئيسية
            router.push({ name: 'Home' });
            
            return response.data;
        } catch (error) {
            const errorMessage = error.response?.data?.message || 'فشل في تسجيل الدخول';
            showError(errorMessage);
            throw error;
        } finally {
            loading.value = false;
        }
    };

    // ✅ تسجيل الخروج
    const logout = async () => {
        loading.value = true;
        try {
            await axios.post('/logout');
        } catch (error) {
            console.error('Logout error:', error);
        } finally {
            // ✅ مسح البيانات المحلية
            localStorage.removeItem('token');
            localStorage.removeItem('user');
            user.value = null;
            
            loading.value = false;
            
            // الانتقال لصفحة تسجيل الدخول
            router.push({ name: 'Login' });
        }
    };

    // ✅ جلب بيانات المستخدم الحالي
    const fetchUser = async () => {
        loading.value = true;
        try {
            const response = await axios.get('/me');
            user.value = response.data.data || response.data;
            
            // تحديث localStorage
            localStorage.setItem('user', JSON.stringify(user.value));
            
            return user.value;
        } catch (error) {
            showError('فشل في جلب بيانات المستخدم');
            
            // إذا فشل، امسح البيانات
            localStorage.removeItem('token');
            localStorage.removeItem('user');
            user.value = null;
            
            throw error;
        } finally {
            loading.value = false;
        }
    };

    // ✅ تحديث الملف الشخصي
    const updateProfile = async (profileData) => {
        loading.value = true;
        try {
            const response = await axios.put('/profile', profileData);
            user.value = response.data.data || response.data;
            
            // تحديث localStorage
            localStorage.setItem('user', JSON.stringify(user.value));
            
            showSuccess('تم تحديث الملف الشخصي بنجاح');
            return user.value;
        } catch (error) {
            showError(error.response?.data?.message || 'فشل في تحديث الملف الشخصي');
            throw error;
        } finally {
            loading.value = false;
        }
    };

    // ✅ التهيئة عند استدعاء الـ composable
    initAuth();

    return {
        user,
        loading,
        isAuthenticated,
        login,
        logout,
        fetchUser,
        updateProfile,
    };
}