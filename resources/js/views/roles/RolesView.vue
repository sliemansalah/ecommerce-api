<!-- resources/js/views/roles/RolesView.vue -->
<template>
    <dashboard-layout>
        <v-container fluid>
            <!-- Header -->
            <v-row class="mb-4">
                <v-col cols="12" md="6">
                    <h1 class="text-h4 font-weight-bold">إدارة الأدوار</h1>
                    <p class="text-subtitle-1 text-medium-emphasis">
                        إدارة أدوار النظام والصلاحيات
                    </p>
                </v-col>
                <v-col cols="12" md="6" class="text-md-end">
                    <v-btn
                        color="primary"
                        prepend-icon="mdi-plus"
                        @click="openCreateDialog"
                    >
                        إضافة دور جديد
                    </v-btn>
                </v-col>
            </v-row>

            <!-- Search -->
            <v-card class="mb-4">
                <v-card-text>
                    <v-text-field
                        v-model="search"
                        prepend-inner-icon="mdi-magnify"
                        label="البحث في الأدوار"
                        placeholder="ابحث عن دور..."
                        variant="outlined"
                        density="comfortable"
                        clearable
                        hide-details
                    ></v-text-field>
                </v-card-text>
            </v-card>

            <!-- Roles Table -->
            <v-card>
                <v-data-table
                    :headers="headers"
                    :items="roleStore.roles"
                    :search="search"
                    :loading="roleStore.loading"
                    loading-text="جاري تحميل البيانات..."
                    no-data-text="لا يوجد أدوار"
                    items-per-page-text="عدد الصفوف في الصفحة:"
                    class="elevation-0"
                >
                    <!-- Role Name Column -->
                    <template v-slot:item.name="{ item }">
                        <div class="d-flex align-center">
                            <v-avatar color="primary" variant="tonal" class="me-3">
                                <v-icon icon="mdi-shield-account"></v-icon>
                            </v-avatar>
                            <div>
                                <div class="font-weight-bold text-h6">{{ item.name }}</div>
                                <div class="text-caption text-medium-emphasis">
                                    {{ item.permissions?.length || 0 }} صلاحية
                                </div>
                            </div>
                        </div>
                    </template>

                    <!-- Users Count Column -->
                    <template v-slot:item.users_count="{ item }">
                        <v-chip
                            size="small"
                            color="info"
                            variant="tonal"
                        >
                            <v-icon start size="small">mdi-account-multiple</v-icon>
                            {{ item.users_count || 0 }} مستخدم
                        </v-chip>
                    </template>

                    <!-- Permissions Column -->
                    <template v-slot:item.permissions="{ item }">
                        <div class="d-flex flex-wrap ga-1">
                            <v-chip
                                v-for="permission in item.permissions?.slice(0, 3)"
                                :key="permission.id"
                                size="small"
                                color="success"
                                variant="tonal"
                            >
                                {{ formatPermissionName(permission.name) }}
                            </v-chip>
                            <v-chip
                                v-if="item.permissions?.length > 3"
                                size="small"
                                color="grey"
                                variant="tonal"
                            >
                                +{{ item.permissions.length - 3 }} أخرى
                            </v-chip>
                        </div>
                        <span v-if="!item.permissions || item.permissions.length === 0" class="text-medium-emphasis">
                            لا يوجد صلاحيات
                        </span>
                    </template>

                    <!-- Actions Column -->
                    <template v-slot:item.actions="{ item }">
                        <v-btn
                            icon="mdi-eye"
                            variant="text"
                            size="small"
                            color="info"
                            @click="viewRolePermissions(item)"
                        ></v-btn>
                        <v-btn
                            icon="mdi-pencil"
                            variant="text"
                            size="small"
                            color="primary"
                            @click="openEditDialog(item)"
                            :disabled="isSystemRole(item.name)"
                        ></v-btn>
                        <v-btn
                            icon="mdi-delete"
                            variant="text"
                            size="small"
                            color="error"
                            @click="openDeleteDialog(item)"
                            :disabled="isSystemRole(item.name)"
                        ></v-btn>
                    </template>
                </v-data-table>
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
            <v-dialog
                v-model="viewDialog"
                max-width="600px"
            >
                <v-card v-if="selectedRole">
                    <v-card-title class="d-flex align-center justify-space-between bg-primary">
                        <span class="text-white">صلاحيات: {{ selectedRole.name }}</span>
                        <v-btn
                            icon="mdi-close"
                            variant="text"
                            color="white"
                            @click="viewDialog = false"
                        ></v-btn>
                    </v-card-title>

                    <v-card-text class="pt-6">
                        <v-alert type="info" variant="tonal" class="mb-4">
                            <div class="d-flex align-center">
                                <v-icon icon="mdi-account-multiple" class="me-3"></v-icon>
                                <div>
                                    <strong>عدد المستخدمين:</strong> {{ selectedRole.users_count || 0 }}
                                </div>
                            </div>
                        </v-alert>

                        <h3 class="text-h6 mb-3">الصلاحيات ({{ selectedRole.permissions?.length || 0 }}):</h3>
                        
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

                        <p v-if="!selectedRole.permissions || selectedRole.permissions.length === 0" 
                           class="text-center text-medium-emphasis mt-4">
                            لا يوجد صلاحيات لهذا الدور
                        </p>
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
import { ref, onMounted } from 'vue';
import { useRoleStore } from '@/stores/role';
import DashboardLayout from '@/layouts/DashboardLayout.vue';
import RoleDialog from '@/components/roles/RoleDialog.vue';
import RoleDeleteDialog from '@/components/roles/RoleDeleteDialog.vue';

// ====================================
// Store
// ====================================
const roleStore = useRoleStore();

// ====================================
// Reactive Data
// ====================================
const search = ref('');
const dialog = ref(false);
const deleteDialog = ref(false);
const viewDialog = ref(false);
const selectedRole = ref(null);

// ====================================
// Table Headers
// ====================================
const headers = [
    { title: 'الدور', key: 'name', sortable: true },
    { title: 'عدد المستخدمين', key: 'users_count', sortable: true },
    { title: 'الصلاحيات', key: 'permissions', sortable: false },
    { title: 'الإجراءات', key: 'actions', sortable: false, align: 'center' },
];

// ====================================
// Methods
// ====================================

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
 * عند حفظ دور (إضافة أو تعديل)
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
 * التحقق من أن الدور هو دور نظام (محمي)
 */
function isSystemRole(roleName) {
    return ['super_admin', 'admin'].includes(roleName);
}

/**
 * تنسيق اسم الصلاحية للعرض
 * مثلاً: products.view -> عرض المنتجات
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
        'permissions.view': 'عرض الصلاحيات',
        'permissions.create': 'إضافة صلاحية',
        'permissions.edit': 'تعديل صلاحية',
        'permissions.delete': 'حذف صلاحية',
    };

    return translations[permissionName] || permissionName;
}

// ====================================
// Lifecycle Hooks
// ====================================
onMounted(async () => {
    // جلب البيانات عند تحميل الصفحة
    await roleStore.fetchRoles();
    await roleStore.fetchPermissions();
});
</script>

<style scoped>
/* Styles مخصصة */
</style>