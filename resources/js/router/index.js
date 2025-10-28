import { createRouter, createWebHistory } from 'vue-router';
import { authService } from '../services/auth';

const routes = [
    {
        path: '/',
        name: 'home',
        component: () => import('../views/Home.vue'),
        meta: { requiresAuth: true },
    },
    {
        path: '/about',
        name: 'about',
        component: () => import('../views/About.vue'),
        meta: { requiresAuth: true },
    },
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
        path: '/profile',
        name: 'profile',
        component: () => import('../views/Profile.vue'),
        meta: { requiresAuth: true },
    },
];

const router = createRouter({
    history: createWebHistory(),
    routes,
});

// Navigation Guard
router.beforeEach((to, from, next) => {
    const isAuthenticated = authService.isAuthenticated();

    // صفحات تحتاج تسجيل دخول
    if (to.meta.requiresAuth && !isAuthenticated) {
        next('/login');
    }
    // صفحات للضيوف فقط (مثل Login)
    else if (to.meta.guest && isAuthenticated) {
        next('/');
    }
    else {
        next();
    }
});

export default router;