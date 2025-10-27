// resources/js/router/index.js
import { createRouter, createWebHistory } from 'vue-router';
import { useAuthStore } from '@/stores/auth';

const routes = [
    // المسارات العامة
    {
        path: '/',
        redirect: '/dashboard',
    },
    
    // صفحات المصادقة
    {
        path: '/login',
        name: 'login',
        component: () => import('@/views/auth/LoginView.vue'),
        meta: { 
            requiresGuest: true,
            title: 'تسجيل الدخول'
        },
    },
    {
        path: '/register',
        name: 'register',
        component: () => import('@/views/auth/RegisterView.vue'),
        meta: { 
            requiresGuest: true,
            title: 'إنشاء حساب جديد'
        },
    },
    
    // صفحات التطبيق الرئيسية
    {
        path: '/dashboard',
        name: 'dashboard',
        component: () => import('@/views/dashboard/DashboardView.vue'),
        meta: { 
            requiresAuth: true,
            title: 'لوحة التحكم'
        },
    },
    
    // المنتجات
    {
        path: '/products',
        name: 'products',
        component: () => import('@/views/products/ProductsView.vue'),
        meta: { 
            requiresAuth: true,
            permission: 'products.view',
            title: 'المنتجات'
        },
    },
    {
        path: '/products/create',
        name: 'products.create',
        component: () => import('@/views/products/ProductCreateView.vue'),
        meta: { 
            requiresAuth: true,
            permission: 'products.create',
            title: 'إضافة منتج جديد'
        },
    },
    {
        path: '/products/:id',
        name: 'products.show',
        component: () => import('@/views/products/ProductShowView.vue'),
        meta: { 
            requiresAuth: true,
            permission: 'products.view',
            title: 'تفاصيل المنتج'
        },
    },
    {
        path: '/products/:id/edit',
        name: 'products.edit',
        component: () => import('@/views/products/ProductEditView.vue'),
        meta: { 
            requiresAuth: true,
            permission: 'products.edit',
            title: 'تعديل المنتج'
        },
    },
    
    // الفئات
    {
        path: '/categories',
        name: 'categories',
        component: () => import('@/views/categories/CategoriesView.vue'),
        meta: { 
            requiresAuth: true,
            permission: 'categories.view',
            title: 'الفئات'
        },
    },
    
    // الطلبات
    {
        path: '/orders',
        name: 'orders',
        component: () => import('@/views/orders/OrdersView.vue'),
        meta: { 
            requiresAuth: true,
            permission: 'orders.view',
            title: 'الطلبات'
        },
    },
    
    // المستخدمين
    {
        path: '/users',
        name: 'users',
        component: () => import('@/views/users/UsersView.vue'),
        meta: { 
            requiresAuth: true,
            permission: 'users.view',
            title: 'المستخدمين'
        },
    },
    
    // الأدوار والصلاحيات
    {
        path: '/roles',
        name: 'roles',
        component: () => import('@/views/roles/RolesView.vue'),
        meta: { 
            requiresAuth: true,
            permission: 'roles.view',
            title: 'الأدوار'
        },
    },
    
    // صفحات الأخطاء
    {
        path: '/403',
        name: 'forbidden',
        component: () => import('@/views/errors/ForbiddenView.vue'),
        meta: {
            title: 'غير مصرح'
        },
    },
    {
        path: '/:pathMatch(.*)*',
        name: 'not-found',
        component: () => import('@/views/errors/NotFoundView.vue'),
        meta: {
            title: 'الصفحة غير موجودة'
        },
    },
];

const router = createRouter({
    history: createWebHistory(),
    routes,
    scrollBehavior(to, from, savedPosition) {
        if (savedPosition) {
            return savedPosition;
        } else {
            return { top: 0 };
        }
    },
});

// Navigation Guards
router.beforeEach(async (to, from, next) => {
    const authStore = useAuthStore();
    const isAuthenticated = authStore.isAuthenticated;
    
    // تحديث عنوان الصفحة
    document.title = to.meta.title 
        ? `${to.meta.title} - E-commerce System` 
        : 'E-commerce System';
    
    // التحقق من المصادقة
    if (to.meta.requiresAuth && !isAuthenticated) {
        next({ name: 'login', query: { redirect: to.fullPath } });
        return;
    }
    
    // منع الدخول لصفحات الضيوف للمستخدمين المسجلين
    if (to.meta.requiresGuest && isAuthenticated) {
        next({ name: 'dashboard' });
        return;
    }
    
    // التحقق من الصلاحيات
    if (to.meta.permission && !authStore.hasPermission(to.meta.permission)) {
        next({ name: 'forbidden' });
        return;
    }
    
    next();
});

export default router;
