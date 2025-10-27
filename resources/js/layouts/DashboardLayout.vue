<!-- resources/js/layouts/DashboardLayout.vue -->
<template>
    <v-layout>
        <!-- Navigation Drawer (القائمة الجانبية) -->
        <v-navigation-drawer
            v-model="drawer"
            :rail="rail"
            permanent
            @click="rail = false"
        >
            <v-list-item
                :prepend-avatar="user?.avatar || '/images/avatar-placeholder.png'"
                :title="user?.name"
                :subtitle="user?.email"
                nav
            >
                <template v-slot:append>
                    <v-btn
                        icon="mdi-chevron-right"
                        variant="text"
                        @click.stop="rail = !rail"
                    ></v-btn>
                </template>
            </v-list-item>

            <v-divider></v-divider>

            <v-list density="compact" nav>
                <!-- لوحة التحكم -->
                <v-list-item
                    prepend-icon="mdi-view-dashboard"
                    title="لوحة التحكم"
                    :to="{ name: 'dashboard' }"
                    value="dashboard"
                ></v-list-item>

                <!-- المنتجات -->
                <v-list-item
                    v-if="hasPermission('products.view')"
                    prepend-icon="mdi-package-variant-closed"
                    title="المنتجات"
                    :to="{ name: 'products' }"
                    value="products"
                ></v-list-item>

                <!-- الفئات -->
                <v-list-item
                    v-if="hasPermission('categories.view')"
                    prepend-icon="mdi-folder-multiple"
                    title="الفئات"
                    :to="{ name: 'categories' }"
                    value="categories"
                ></v-list-item>

                <!-- الطلبات -->
                <v-list-item
                    v-if="hasPermission('orders.view')"
                    prepend-icon="mdi-cart"
                    title="الطلبات"
                    :to="{ name: 'orders' }"
                    value="orders"
                ></v-list-item>

                <v-divider class="my-2"></v-divider>

                <!-- المستخدمين -->
                <v-list-item
                    v-if="hasPermission('users.view')"
                    prepend-icon="mdi-account-multiple"
                    title="المستخدمين"
                    :to="{ name: 'users' }"
                    value="users"
                ></v-list-item>

                <!-- الأدوار والصلاحيات -->
                <v-list-item
                    v-if="hasPermission('roles.view')"
                    prepend-icon="mdi-shield-account"
                    title="الأدوار"
                    :to="{ name: 'roles' }"
                    value="roles"
                ></v-list-item>
            </v-list>
        </v-navigation-drawer>

        <!-- App Bar (شريط العلوي) -->
        <v-app-bar elevation="1">
            <v-app-bar-nav-icon @click="drawer = !drawer"></v-app-bar-nav-icon>

            <v-app-bar-title>
                <v-icon icon="mdi-store" size="small" class="ml-2"></v-icon>
                نظام التجارة الإلكترونية
            </v-app-bar-title>

            <v-spacer></v-spacer>

            <!-- البحث -->
            <v-text-field
                v-model="search"
                prepend-inner-icon="mdi-magnify"
                placeholder="بحث..."
                density="compact"
                variant="solo-filled"
                flat
                hide-details
                single-line
                class="mx-4"
                style="max-width: 400px"
            ></v-text-field>

            <!-- الإشعارات -->
            <v-btn icon>
                <v-badge color="error" content="3" floating>
                    <v-icon>mdi-bell</v-icon>
                </v-badge>
            </v-btn>

            <!-- تبديل الوضع الداكن -->
            <v-btn
                icon
                @click="toggleTheme"
            >
                <v-icon>
                    {{ theme.global.current.value.dark ? 'mdi-weather-sunny' : 'mdi-weather-night' }}
                </v-icon>
            </v-btn>

            <!-- قائمة المستخدم -->
            <v-menu>
                <template v-slot:activator="{ props }">
                    <v-btn
                        icon
                        v-bind="props"
                    >
                        <v-avatar size="32">
                            <v-img :src="user?.avatar || '/images/avatar-placeholder.png'"></v-img>
                        </v-avatar>
                    </v-btn>
                </template>

                <v-list>
                    <v-list-item>
                        <v-list-item-title>{{ user?.name }}</v-list-item-title>
                        <v-list-item-subtitle>{{ user?.email }}</v-list-item-subtitle>
                    </v-list-item>

                    <v-divider></v-divider>

                    <v-list-item prepend-icon="mdi-account" title="الملف الشخصي"></v-list-item>
                    <v-list-item prepend-icon="mdi-cog" title="الإعدادات"></v-list-item>

                    <v-divider></v-divider>

                    <v-list-item
                        prepend-icon="mdi-logout"
                        title="تسجيل الخروج"
                        @click="handleLogout"
                    ></v-list-item>
                </v-list>
            </v-menu>
        </v-app-bar>

        <!-- المحتوى الرئيسي -->
        <v-main>
            <v-container fluid>
                <router-view />
            </v-container>
        </v-main>
    </v-layout>
</template>

<script setup>
import { ref, computed } from 'vue';
import { useTheme } from 'vuetify';
import { useAuthStore } from '@/stores/auth';

const theme = useTheme();
const authStore = useAuthStore();

const drawer = ref(true);
const rail = ref(false);
const search = ref('');

const user = computed(() => authStore.currentUser);
const hasPermission = computed(() => authStore.hasPermission);

const toggleTheme = () => {
    theme.global.name.value = theme.global.current.value.dark ? 'lightTheme' : 'darkTheme';
    localStorage.setItem('theme', theme.global.name.value);
};

const handleLogout = async () => {
    await authStore.logout();
};

// استرجاع الثيم المحفوظ
const savedTheme = localStorage.getItem('theme');
if (savedTheme) {
    theme.global.name.value = savedTheme;
}
</script>

<style scoped>
.v-navigation-drawer {
    transition: all 0.3s ease;
}
</style>
