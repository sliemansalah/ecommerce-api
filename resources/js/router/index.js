import { createRouter, createWebHistory } from 'vue-router';
import { useAuth } from '../composables/useAuth';

const routes = [
      {
        path: '/login',
        name: 'login',
        component: () => import('../views/Login.vue'),
        meta: { 
            guest: true,
            layout: 'auth' // استخدام AuthLayout
        },
    },
    {
        path: '/',
        // component: () => import('../layouts/DashboardLayout.vue'),
        meta: { requiresAuth: true },
        children: [
            {
                path: '',
                name: 'Home',
                component: () => import('../views/Home.vue'),
            },
            {
                path: 'users',
                name: 'Users',
                component: () => import('../views/Users/Index.vue'),
            },
            {
                path: 'roles',
                name: 'Roles',
                component: () => import('../views/Roles/Index.vue'),
            },
        ],
    },
];

const router = createRouter({
    history: createWebHistory(),
    routes,
});

router.beforeEach((to, from, next) => {
    const { isAuthenticated } = useAuth();
    
    if (to.meta.requiresAuth && !isAuthenticated.value) {
        next({ name: 'Login' });
    } else if (to.meta.guest && isAuthenticated.value) {
        next({ name: 'Home' });
    } else {
        next();
    }
});

export default router;