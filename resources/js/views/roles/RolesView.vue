<!-- resources/js/views/roles/RolesView.vue -->
<template>
    <dashboard-layout>
        <v-container fluid class="pa-6">
            <!-- Header -->
            <v-row class="mb-6">
                <v-col cols="12">
                    <div class="d-flex align-center justify-space-between mb-4">
                        <div>
                            <h1 class="text-h4 font-weight-bold mb-2">
                                <v-icon icon="mdi-shield-account" class="me-2" color="primary"></v-icon>
                                إدارة الأدوار والصلاحيات
                            </h1>
                            <p class="text-subtitle-1 text-medium-emphasis mb-0">
                                إدارة أدوار النظام والصلاحيات المرتبطة بها
                            </p>
                        </div>
                        <v-btn
                            color="primary"
                            size="large"
                            prepend-icon="mdi-plus"
                            @click="openCreateDialog"
                            elevation="2"
                        >
                            إضافة دور جديد
                        </v-btn>
                    </div>
                </v-col>
            </v-row>

            <!-- Search & Filters Card -->
            <v-card class="mb-6" elevation="2">
                <v-card-text>
                    <v-row align="center">
                        <!-- البحث -->
                        <v-col cols="12" md="6">
                            <v-text-field
                                v-model="searchQuery"
                                prepend-inner-icon="mdi-magnify"
                                label="البحث في الأدوار"
                                placeholder="ابحث عن دور..."
                                variant="outlined"
                                density="comfortable"
                                clearable
                                hide-details
                                @keyup.enter="applySearch"
                            ></v-text-field>
                        </v-col>

                        <!-- عدد العناصر في الصفحة -->
                        <v-col cols="12" md="3">
                            <v-select
                                v-model="perPage"
                                :items="[5, 10, 25, 50]"
                                label="العناصر"
                                variant="outlined"
                                density="comfortable"
                                hide-details
                                prepend-inner-icon="mdi-table-row"
                                @update:model-value="changePerPage"
                            ></v-select>
                        </v-col>

                        <!-- الترتيب -->
                        <v-col cols="12" md="3">
                            <v-btn-toggle
                                v-model="sortOrder"
                                mandatory
                                divided
                                density="comfortable"
                                @update:model-value="applyFilters"
                            >
                                <v-btn value="desc">
                                    <v-icon>mdi-sort-descending</v-icon>
                                </v-btn>
                                <v-btn value="asc">
                                    <v-icon>mdi-sort-ascending</v-icon>
                                </v-btn>
                            </v-btn-toggle>
                        </v-col>
                    </v-row>
                </v-card-text>
            </v-card>

            <!-- Roles Table Card -->
            <v-card elevation="2">
                <v-card-text class="pa-0">
                    <v-data-table-server
                        :headers="headers"
                        :items="roleStore.roles"
                        :items-length="roleStore.pagination.total"
                        :loading="roleStore.loading"
                        :items-per-page="roleStore.pagination.perPage"
                        :page="roleStore.pagination.currentPage"
                        @update:page="loadPage"
                        loading-text="جاري تحميل البيانات..."
                        no-data-text="لا يوجد أدوار"
                        items-per-page-text="عدد الصفوف في الصفحة:"
                        class="elevation-0"
                        hover
                    >
                        <!-- Role Name Column -->
                        <template v-slot:item.name="{ item }">
                            <div class="d-flex align-center py-4">
                                <v-avatar 
                                    :color="getRoleColor(item.name)" 
                                    size="48"
                                    class="me-4"
                                    variant="tonal"
                                >
                                    <v-icon icon="mdi-shield-account" size="24"></v-icon>
                                </v-avatar>
                                <div>
                                    <div class="font-weight-bold text-body-1">
                                        {{ formatRoleName(item.name) }}
                                    </div>
                                    <div class="text-caption text-medium-emphasis">
                                        <v-icon icon="mdi-key" size="14" class="me-1"></v-icon>
                                        {{ item.permissions?.length || 0 }} صلاحية
                                    </div>
                                </div>
                            </div>
                        </template>

                        <!-- Users Count Column -->
                        <template v-slot:item.users_count="{ item }">
                            <v-chip
                                :color="item.users_count > 0 ? 'info' : 'grey'"
                                size="default"
                                variant="tonal"
                                prepend-icon="mdi-account-multiple"
                            >
                                {{ item.users_count || 0 }} مستخدم
                            </v-chip>
                        </template>

                        <!-- Permissions Column -->
                        <template v-slot:item.permissions="{ item }">
                            <div class="py-2">
                                <div v-if="item.permissions && item.permissions.length > 0" class="d-flex flex-wrap ga-1">
                                    <v-chip
                                        v-for="permission in item.permissions.slice(0, 4)"
                                        :key="permission.id"
                                        size="small"
                                        color="success"
                                        variant="flat"
                                        class="text-caption"
                                    >
                                        {{ formatPermissionName(permission.name) }}
                                    </v-chip>
                                    <v-chip
                                        v-if="item.permissions.length > 4"
                                        size="small"
                                        color="grey-darken-1"
                                        variant="flat"
                                        class="text-caption"
                                    >
                                        +{{ item.permissions.length - 4 }}
                                    </v-chip>
                                </div>
                                <span v-else class="text-medium-emphasis text-caption">
                                    لا يوجد صلاحيات
                                </span>
                            </div>
                        </template>

                        <!-- System Role Badge -->
                        <template v-slot:item.system="{ item }">
                            <v-chip
                                v-if="isSystemRole(item.name)"
                                color="warning"
                                size="small"
                                variant="tonal"
                                prepend-icon="mdi-shield-lock"
                            >
                                محمي
                            </v-chip>
                        </template>

                        <!-- Actions Column -->
                        <template v-slot:item.actions="{ item }">
                            <div class="d-flex ga-1">
                                <v-tooltip text="عرض الصلاحيات" location="top">
                                    <template v-slot:activator="{ props }">
                                        <v-btn
                                            v-bind="props"
                                            icon="mdi-eye"
                                            variant="text"
                                            size="small"
                                            color="info"
                                            @click="viewRolePermissions(item)"
                                        ></v-btn>
                                    </template>
                                </v-tooltip>

                                <v-tooltip text="تعديل" location="top">
                                    <template v-slot:activator="{ props }">
                                        <v-btn
                                            v-bind="props"
                                            icon="mdi-pencil"
                                            variant="text"
                                            size="small"
                                            color="primary"
                                            :disabled="isSystemRole(item.name)"
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
                                            :disabled="isSystemRole(item.name)"
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
                                    عرض {{ roleStore.pagination.from }} إلى {{ roleStore.pagination.to }} 
                                    من أصل {{ roleStore.pagination.total }} دور
                                </div>
                                
                                <v-pagination
                                    v-model="currentPage"
                                    :length="roleStore.pagination.lastPage"
                                    :total-visible="7"
                                    @update:model-value="loadPage"
                                    rounded="circle"
                                ></v-pagination>
                            </div>
                        </template>
                    </v-data-table-server>
                </v-card-text>
            </v-card>

            <!-- Role Dialog (Create/Edit) -->
            <role-dialog
                v-model="dialog"
                :role="selectedRole"
                :permissions="roleStore.permissions"
                @saved="onRoleSaved"
            />

            <!-- Delete Confirmation Dialog -->
            <role-delete-dialog
                v-model="deleteDialog"
                :role="selectedRole"
                @deleted="onRoleDeleted"
            />

            <!-- View Permissions Dialog -->
            <v-dialog v-model="viewDialog" max-width="700px">
                <v-card v-if="selectedRole">
                    <v-card-title class="d-flex align-center justify-space-between bg-primary pa-4">
                        <div class="d-flex align-center">
                            <v-icon icon="mdi-shield-account" class="me-2" color="white"></v-icon>
                            <span class="text-h6 text-white">{{ formatRoleName(selectedRole.name) }}</span>
                        </div>
                        <v-btn
                            icon="mdi-close"
                            variant="text"
                            color="white"
                            @click="viewDialog = false"
                        ></v-btn>
                    </v-card-title>

                    <v-card-text class="pt-6">
                        <!-- Stats -->
                        <v-row class="mb-4">
                            <v-col cols="6">
                                <v-card variant="tonal" color="info">
                                    <v-card-text class="text-center">
                                        <v-icon icon="mdi-account-multiple" size="32" class="mb-2"></v-icon>
                                        <div class="text-h5 font-weight-bold">{{ selectedRole.users_count || 0 }}</div>
                                        <div class="text-caption">مستخدم</div>
                                    </v-card-text>
                                </v-card>
                            </v-col>
                            <v-col cols="6">
                                <v-card variant="tonal" color="success">
                                    <v-card-text class="text-center">
                                        <v-icon icon="mdi-key" size="32" class="mb-2"></v-icon>
                                        <div class="text-h5 font-weight-bold">{{ selectedRole.permissions?.length || 0 }}</div>
                                        <div class="text-caption">صلاحية</div>
                                    </v-card-text>
                                </v-card>
                            </v-col>
                        </v-row>

                        <v-divider class="mb-4"></v-divider>

                        <!-- Permissions List -->
                        <h3 class="text-h6 mb-3">الصلاحيات:</h3>
                        
                        <div v-if="selectedRole.permissions && selectedRole.permissions.length > 0">
                            <v-chip-group column>
                                <v-chip
                                    v-for="permission in selectedRole.permissions"
                                    :key="permission.id"
                                    color="success"
                                    variant="tonal"
                                    prepend-icon="mdi-check-circle"
                                >
                                    {{ formatPermissionName(permission.name) }}
                                </v-chip>
                            </v-chip-group>
                        </div>

                        <v-alert v-else type="info" variant="tonal">
                            لا يوجد صلاحيات لهذا الدور
                        </v-alert>
                    </v-card-text>

                    <v-card-actions class="pa-4">
                        <v-spacer></v-spacer>
                        <v-btn
                            color="primary"
                            variant="elevated"
                            @click="viewDialog = false"
                        >
                            إغلاق
                        </v-btn>
                    </v-card-actions>
                </v-card>
            </v-dialog>
        </v-container>
    </dashboard-layout>
</template>

<script setup>
import { ref, computed, onMounted, watch } from 'vue';
import { useRoleStore } from '@/stores/role';
import DashboardLayout from '@/layouts/DashboardLayout.vue';
import RoleDialog from '@/components/roles/RoleDialog.vue';
import RoleDeleteDialog from '@/components/roles/RoleDeleteDialog.vue';

// ====================================
// Stores
// ====================================
const roleStore = useRoleStore();

// ====================================
// Reactive Data
// ====================================
const searchQuery = ref('');
const sortBy = ref('created_at');
const sortOrder = ref('desc');
const perPage = ref(10);
const dialog = ref(false);
const deleteDialog = ref(false);
const viewDialog = ref(false);
const selectedRole = ref(null);

// ====================================
// Computed
// ====================================
const currentPage = computed({
    get: () => roleStore.pagination.currentPage,
    set: (value) => loadPage(value)
});

// ====================================
// Table Headers
// ====================================
const headers = [
    { title: 'الدور', key: 'name', sortable: false, width: '300px' },
    { title: 'عدد المستخدمين', key: 'users_count', sortable: false, align: 'center', width: '180px' },
    { title: 'الصلاحيات', key: 'permissions', sortable: false },
    { title: 'النوع', key: 'system', sortable: false, align: 'center', width: '120px' },
    { title: 'الإجراءات', key: 'actions', sortable: false, align: 'center', width: '150px' },
];

// ====================================
// Methods
// ====================================

/**
 * تحميل صفحة معينة
 */
async function loadPage(page) {
    await roleStore.fetchRoles(page);
}

/**
 * تطبيق البحث
 */
async function applySearch() {
    await roleStore.applyFilters({
        search: searchQuery.value,
    });
}

/**
 * تطبيق الفلاتر
 */
async function applyFilters() {
    await roleStore.applyFilters({
        search: searchQuery.value,
        sortBy: sortBy.value,
        sortOrder: sortOrder.value,
    });
}

/**
 * تغيير عدد العناصر في الصفحة
 */
async function changePerPage() {
    await roleStore.changePerPage(perPage.value);
}

/**
 * فتح نموذج إضافة دور
 */
function openCreateDialog() {
    selectedRole.value = null;
    dialog.value = true;
}

/**
 * فتح نموذج تعديل دور
 */
function openEditDialog(role) {
    selectedRole.value = { ...role };
    dialog.value = true;
}

/**
 * فتح نموذج حذف دور
 */
function openDeleteDialog(role) {
    selectedRole.value = role;
    deleteDialog.value = true;
}

/**
 * عرض صلاحيات الدور
 */
function viewRolePermissions(role) {
    selectedRole.value = role;
    viewDialog.value = true;
}

/**
 * عند حفظ دور
 */
function onRoleSaved() {
    dialog.value = false;
    selectedRole.value = null;
}

/**
 * عند حذف دور
 */
function onRoleDeleted() {
    deleteDialog.value = false;
    selectedRole.value = null;
}

/**
 * التحقق من أن الدور محمي
 */
function isSystemRole(roleName) {
    return ['super_admin', 'admin'].includes(roleName);
}

/**
 * تنسيق اسم الدور
 */
function formatRoleName(roleName) {
    const names = {
        'super_admin': 'مدير النظام',
        'admin': 'مدير',
        'manager': 'مشرف',
        'employee': 'موظف',
    };
    return names[roleName] || roleName;
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
 * تنسيق اسم الصلاحية
 */
function formatPermissionName(permissionName) {
    const translations = {
        'products.view': 'عرض المنتجات',
        'products.create': 'إضافة منتج',
        'products.edit': 'تعديل منتج',
        'products.delete': 'حذف منتج',
        'categories.view': 'عرض الفئات',
        'categories.create': 'إضافة فئة',
        'categories.edit': 'تعديل فئة',
        'categories.delete': 'حذف فئة',
        'orders.view': 'عرض الطلبات',
        'orders.create': 'إضافة طلب',
        'orders.edit': 'تعديل طلب',
        'orders.delete': 'حذف طلب',
        'users.view': 'عرض المستخدمين',
        'users.create': 'إضافة مستخدم',
        'users.edit': 'تعديل مستخدم',
        'users.delete': 'حذف مستخدم',
        'roles.view': 'عرض الأدوار',
        'roles.create': 'إضافة دور',
        'roles.edit': 'تعديل دور',
        'roles.delete': 'حذف دور',
    };
    return translations[permissionName] || permissionName;
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
    perPage.value = roleStore.pagination.perPage;
    await roleStore.fetchRoles(1);
    await roleStore.fetchPermissions();
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