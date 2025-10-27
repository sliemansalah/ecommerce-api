<!-- resources/js/views/users/UsersView.vue -->
<template>
    <dashboard-layout>
        <v-container fluid class="pa-6">
            <!-- Header with Stats -->
            <v-row class="mb-6">
                <v-col cols="12">
                    <div class="d-flex align-center justify-space-between mb-4">
                        <div>
                            <h1 class="text-h4 font-weight-bold mb-2">
                                <v-icon icon="mdi-account-multiple" class="me-2" color="primary"></v-icon>
                                إدارة المستخدمين
                            </h1>
                            <p class="text-subtitle-1 text-medium-emphasis mb-0">
                                إدارة مستخدمي النظام والصلاحيات
                            </p>
                        </div>
                        <v-btn
                            color="primary"
                            size="large"
                            prepend-icon="mdi-plus"
                            @click="openCreateDialog"
                            elevation="2"
                        >
                            إضافة مستخدم جديد
                        </v-btn>
                    </div>
                </v-col>
            </v-row>

            <!-- Search & Filters Card -->
            <v-card class="mb-6" elevation="2">
                <v-card-text>
                    <v-row align="center">
                        <!-- البحث -->
                        <v-col cols="12" md="5">
                            <v-text-field
                                v-model="searchQuery"
                                prepend-inner-icon="mdi-magnify"
                                label="البحث في المستخدمين"
                                placeholder="ابحث بالاسم أو البريد الإلكتروني..."
                                variant="outlined"
                                density="comfortable"
                                clearable
                                hide-details
                                @keyup.enter="applySearch"
                            >
                                <template v-slot:append>
                                    <v-btn
                                        icon="mdi-filter"
                                        variant="text"
                                        @click="showFilters = !showFilters"
                                    ></v-btn>
                                </template>
                            </v-text-field>
                        </v-col>

                        <!-- تصفية حسب الدور -->
                        <v-col cols="12" md="3">
                            <v-select
                                v-model="filterRole"
                                :items="roleStore.roles"
                                item-title="name"
                                item-value="id"
                                label="تصفية حسب الدور"
                                variant="outlined"
                                density="comfortable"
                                clearable
                                hide-details
                                prepend-inner-icon="mdi-shield-account"
                                @update:model-value="applyFilters"
                            ></v-select>
                        </v-col>

                        <!-- عدد العناصر في الصفحة -->
                        <v-col cols="12" md="2">
                            <v-select
                                v-model="perPage"
                                :items="[5, 10, 25, 50, 100]"
                                label="العناصر"
                                variant="outlined"
                                density="comfortable"
                                hide-details
                                prepend-inner-icon="mdi-table-row"
                                @update:model-value="changePerPage"
                            ></v-select>
                        </v-col>

                        <!-- الترتيب -->
                        <v-col cols="12" md="2">
                            <v-btn-toggle
                                v-model="sortOrder"
                                mandatory
                                divided
                                density="comfortable"
                                @update:model-value="applyFilters"
                            >
                                <v-btn value="desc" icon="mdi-sort-descending"></v-btn>
                                <v-btn value="asc" icon="mdi-sort-ascending"></v-btn>
                            </v-btn-toggle>
                        </v-col>
                    </v-row>

                    <!-- Filters Expansion -->
                    <v-expand-transition>
                        <div v-show="showFilters" class="mt-4">
                            <v-divider class="mb-4"></v-divider>
                            <v-row>
                                <v-col cols="12" md="3">
                                    <v-select
                                        v-model="sortBy"
                                        :items="sortOptions"
                                        label="الترتيب حسب"
                                        variant="outlined"
                                        density="comfortable"
                                        hide-details
                                        @update:model-value="applyFilters"
                                    ></v-select>
                                </v-col>
                                <v-col cols="12" md="3">
                                    <v-btn
                                        variant="outlined"
                                        prepend-icon="mdi-filter-remove"
                                        @click="clearFilters"
                                        block
                                    >
                                        مسح الفلاتر
                                    </v-btn>
                                </v-col>
                            </v-row>
                        </div>
                    </v-expand-transition>
                </v-card-text>
            </v-card>

            <!-- Users Table Card -->
            <v-card elevation="2">
                <v-card-text class="pa-0">
                    <v-data-table-server
                        :headers="headers"
                        :items="userStore.users"
                        :items-length="userStore.pagination.total"
                        :loading="userStore.loading"
                        :items-per-page="userStore.pagination.perPage"
                        :page="userStore.pagination.currentPage"
                        @update:page="loadPage"
                        loading-text="جاري تحميل البيانات..."
                        no-data-text="لا يوجد مستخدمين"
                        items-per-page-text="عدد الصفوف في الصفحة:"
                        class="elevation-0"
                        hover
                    >
                        <!-- User Column -->
                        <template v-slot:item.user="{ item }">
                            <div class="d-flex align-center py-4">
                                <v-avatar 
                                    :color="getAvatarColor(item.name)" 
                                    size="48"
                                    class="me-4"
                                >
                                    <v-img v-if="item.avatar" :src="item.avatar"></v-img>
                                    <span v-else class="text-h6 font-weight-bold text-white">
                                        {{ getInitials(item.name) }}
                                    </span>
                                </v-avatar>
                                <div>
                                    <div class="font-weight-bold text-body-1">{{ item.name }}</div>
                                    <div class="text-caption text-medium-emphasis">
                                        <v-icon icon="mdi-email" size="14" class="me-1"></v-icon>
                                        {{ item.email }}
                                    </div>
                                </div>
                            </div>
                        </template>

                        <!-- Roles Column -->
                        <template v-slot:item.roles="{ item }">
                            <div class="d-flex flex-wrap ga-2">
                                <v-chip
                                    v-for="role in item.roles"
                                    :key="role.id"
                                    size="small"
                                    :color="getRoleColor(role.name)"
                                    variant="flat"
                                    label
                                >
                                    <v-icon start size="16">mdi-shield-account</v-icon>
                                    {{ role.name }}
                                </v-chip>
                            </div>
                            <span v-if="!item.roles || item.roles.length === 0" class="text-medium-emphasis text-caption">
                                لا يوجد أدوار
                            </span>
                        </template>

                        <!-- Status Column -->
                        <template v-slot:item.status="{ item }">
                            <v-chip
                                :color="item.email_verified_at ? 'success' : 'warning'"
                                size="small"
                                variant="tonal"
                            >
                                <v-icon start size="16">
                                    {{ item.email_verified_at ? 'mdi-check-circle' : 'mdi-clock-outline' }}
                                </v-icon>
                                {{ item.email_verified_at ? 'مفعّل' : 'قيد التفعيل' }}
                            </v-chip>
                        </template>

                        <!-- Created At Column -->
                        <template v-slot:item.created_at="{ item }">
                            <div class="text-body-2">
                                <v-icon icon="mdi-calendar" size="16" class="me-1"></v-icon>
                                {{ formatDate(item.created_at) }}
                            </div>
                            <div class="text-caption text-medium-emphasis">
                                {{ formatTimeAgo(item.created_at) }}
                            </div>
                        </template>

                        <!-- Actions Column -->
                        <template v-slot:item.actions="{ item }">
                            <div class="d-flex ga-1">
                                <v-tooltip text="تعديل" location="top">
                                    <template v-slot:activator="{ props }">
                                        <v-btn
                                            v-bind="props"
                                            icon="mdi-pencil"
                                            variant="text"
                                            size="small"
                                            color="primary"
                                            @click="openEditDialog(item)"
                                        ></v-btn>
                                    </template>
                                </v-tooltip>

                                <v-tooltip text="حذف" location="top">
                                    <template v-slot:activator="{ props }">
                                        <v-btn
                                            v-bind="props"
                                            icon="mdi-delete"
                                            variant="text"
                                            size="small"
                                            color="error"
                                            @click="openDeleteDialog(item)"
                                        ></v-btn>
                                    </template>
                                </v-tooltip>
                            </div>
                        </template>

                        <!-- Bottom Pagination -->
                        <template v-slot:bottom>
                            <div class="d-flex align-center justify-space-between pa-4">
                                <div class="text-caption text-medium-emphasis">
                                    عرض {{ userStore.pagination.from }} إلى {{ userStore.pagination.to }} 
                                    من أصل {{ userStore.pagination.total }} مستخدم
                                </div>
                                
                                <v-pagination
                                    v-model="currentPage"
                                    :length="userStore.pagination.lastPage"
                                    :total-visible="7"
                                    @update:model-value="loadPage"
                                    rounded="circle"
                                ></v-pagination>
                            </div>
                        </template>
                    </v-data-table-server>
                </v-card-text>
            </v-card>

            <!-- User Dialog (Create/Edit) -->
            <user-dialog
                v-model="dialog"
                :user="selectedUser"
                :roles="roleStore.roles"
                @saved="onUserSaved"
            />

            <!-- Delete Confirmation Dialog -->
            <user-delete-dialog
                v-model="deleteDialog"
                :user="selectedUser"
                @deleted="onUserDeleted"
            />
        </v-container>
    </dashboard-layout>
</template>

<script setup>
import { ref, computed, onMounted, watch } from 'vue';
import { useUserStore } from '@/stores/user';
import { useRoleStore } from '@/stores/role';
import DashboardLayout from '@/layouts/DashboardLayout.vue';
import UserDialog from '@/components/users/UserDialog.vue';
import UserDeleteDialog from '@/components/users/UserDeleteDialog.vue';

// ====================================
// Stores
// ====================================
const userStore = useUserStore();
const roleStore = useRoleStore();

// ====================================
// Reactive Data
// ====================================
const searchQuery = ref('');
const filterRole = ref(null);
const sortBy = ref('created_at');
const sortOrder = ref('desc');
const perPage = ref(10);
const showFilters = ref(false);
const dialog = ref(false);
const deleteDialog = ref(false);
const selectedUser = ref(null);

// ====================================
// Computed
// ====================================
const currentPage = computed({
    get: () => userStore.pagination.currentPage,
    set: (value) => loadPage(value)
});

const sortOptions = [
    { title: 'تاريخ الإنشاء', value: 'created_at' },
    { title: 'الاسم', value: 'name' },
    { title: 'البريد الإلكتروني', value: 'email' },
];

// ====================================
// Table Headers
// ====================================
const headers = [
    { title: 'المستخدم', key: 'user', sortable: false, width: '300px' },
    { title: 'الأدوار', key: 'roles', sortable: false },
    { title: 'الحالة', key: 'status', sortable: false, align: 'center' },
    { title: 'تاريخ الإنشاء', key: 'created_at', sortable: false },
    { title: 'الإجراءات', key: 'actions', sortable: false, align: 'center', width: '120px' },
];

// ====================================
// Methods
// ====================================

/**
 * تحميل صفحة معينة
 */
async function loadPage(page) {
    await userStore.fetchUsers(page);
}

/**
 * تطبيق البحث
 */
async function applySearch() {
    await userStore.applyFilters({
        search: searchQuery.value,
    });
}

/**
 * تطبيق الفلاتر
 */
async function applyFilters() {
    await userStore.applyFilters({
        search: searchQuery.value,
        roleId: filterRole.value,
        sortBy: sortBy.value,
        sortOrder: sortOrder.value,
    });
}

/**
 * تغيير عدد العناصر في الصفحة
 */
async function changePerPage() {
    await userStore.changePerPage(perPage.value);
}

/**
 * مسح الفلاتر
 */
async function clearFilters() {
    searchQuery.value = '';
    filterRole.value = null;
    sortBy.value = 'created_at';
    sortOrder.value = 'desc';
    
    userStore.clearFilters();
    await userStore.fetchUsers(1);
}

/**
 * فتح نموذج إضافة مستخدم
 */
function openCreateDialog() {
    selectedUser.value = null;
    dialog.value = true;
}

/**
 * فتح نموذج تعديل مستخدم
 */
function openEditDialog(user) {
    selectedUser.value = { ...user };
    dialog.value = true;
}

/**
 * فتح نموذج حذف مستخدم
 */
function openDeleteDialog(user) {
    selectedUser.value = user;
    deleteDialog.value = true;
}

/**
 * عند حفظ مستخدم
 */
function onUserSaved() {
    dialog.value = false;
    selectedUser.value = null;
}

/**
 * عند حذف مستخدم
 */
function onUserDeleted() {
    deleteDialog.value = false;
    selectedUser.value = null;
}

/**
 * الحصول على الأحرف الأولى من الاسم
 */
function getInitials(name) {
    if (!name) return '؟';
    const parts = name.split(' ');
    if (parts.length >= 2) {
        return (parts[0][0] + parts[1][0]).toUpperCase();
    }
    return name.substring(0, 2).toUpperCase();
}

/**
 * الحصول على لون Avatar
 */
function getAvatarColor(name) {
    const colors = ['primary', 'secondary', 'success', 'info', 'warning', 'error'];
    const index = (name?.charCodeAt(0) || 0) % colors.length;
    return colors[index];
}

/**
 * الحصول على لون الدور
 */
function getRoleColor(roleName) {
    const colors = {
        'super_admin': 'error',
        'admin': 'warning',
        'manager': 'info',
        'employee': 'success',
    };
    return colors[roleName] || 'primary';
}

/**
 * تنسيق التاريخ
 */
function formatDate(date) {
    if (!date) return '-';
    return new Date(date).toLocaleDateString('ar-EG', {
        year: 'numeric',
        month: 'short',
        day: 'numeric'
    });
}

/**
 * تنسيق الوقت النسبي
 */
function formatTimeAgo(date) {
    if (!date) return '';
    
    const seconds = Math.floor((new Date() - new Date(date)) / 1000);
    
    if (seconds < 60) return 'منذ لحظات';
    if (seconds < 3600) return `منذ ${Math.floor(seconds / 60)} دقيقة`;
    if (seconds < 86400) return `منذ ${Math.floor(seconds / 3600)} ساعة`;
    if (seconds < 2592000) return `منذ ${Math.floor(seconds / 86400)} يوم`;
    
    return formatDate(date);
}

// ====================================
// Watchers
// ====================================

// مراقبة البحث مع Debounce
let searchTimeout;
watch(searchQuery, (newValue) => {
    clearTimeout(searchTimeout);
    searchTimeout = setTimeout(() => {
        applySearch();
    }, 500);
});

// ====================================
// Lifecycle Hooks
// ====================================
onMounted(async () => {
    // جلب البيانات عند تحميل الصفحة
    perPage.value = userStore.pagination.perPage;
    await userStore.fetchUsers(1);
    await roleStore.fetchRoles();
});
</script>

<style scoped>
/* تحسينات التصميم */
.v-data-table :deep(.v-data-table__tr):hover {
    background-color: rgba(var(--v-theme-primary), 0.05);
}

.v-pagination :deep(.v-pagination__item--is-active) {
    background-color: rgb(var(--v-theme-primary));
    color: white;
}
</style>