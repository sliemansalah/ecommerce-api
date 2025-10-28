import { ref, computed } from 'vue';
import { authService } from '../services/auth';
import { useRouter } from 'vue-router';

const user = ref(authService.getUser());
const isAuthenticated = ref(authService.isAuthenticated());
const loading = ref(false);

export function useAuth() {
    const router = useRouter();

    const login = async (credentials) => {
        loading.value = true;
        try {
            const data = await authService.login(credentials);
            user.value = data.user;
            isAuthenticated.value = true;
            return data;
        } catch (error) {
            throw error;
        } finally {
            loading.value = false;
        }
    };

    const logout = async () => {
        loading.value = true;
        try {
            await authService.logout();
            user.value = null;
            isAuthenticated.value = false;
            router.push('/login');
        } catch (error) {
            console.error('Logout error:', error);
        } finally {
            loading.value = false;
        }
    };

    const updateProfile = async (data) => {
        loading.value = true;
        try {
            const response = await authService.updateProfile(data);
            user.value = response.user;
            return response;
        } catch (error) {
            throw error;
        } finally {
            loading.value = false;
        }
    };

    const refreshUser = async () => {
        if (!isAuthenticated.value) return;
        
        try {
            const userData = await authService.getCurrentUser();
            user.value = userData;
        } catch (error) {
            console.error('Refresh user error:', error);
        }
    };

    return {
        user: computed(() => user.value),
        isAuthenticated: computed(() => isAuthenticated.value),
        loading: computed(() => loading.value),
        login,
        logout,
        updateProfile,
        refreshUser,
    };
}