<!-- resources/js/views/users/UsersView.vue -->
<template>
    <dashboard-layout>
        <v-container fluid>
            <!-- Header -->
            <v-row class="mb-4">
                <v-col cols="12" md="6">
                    <h1 class="text-h4 font-weight-bold">إدارة المستخدمين</h1>
                    <p class="text-subtitle-1 text-medium-emphasis">
                        إدارة مستخدمي النظام والصلاحيات
                    </p>
                </v-col>
                <v-col cols="12" md="6" class="text-md-end">
                    <v-btn
                        color="primary"
                        prepend-icon="mdi-plus"
                        @click="openCreateDialog"
                    >
                        إضافة مستخدم جديد
                    </v-btn>
                </v-col>
            </v-row>

            <!-- Search & Filters -->
            <v-card class="mb-4">
                <v-card-text>
                    <v-row>
                        <v-col cols="12" md="8">
                            <v-text-field
                                v-model="search"
                                prepend-inner-icon="mdi-magnify"
                                label="البحث في المستخدمين"
                                placeholder="ابحث بالاسم أو البريد الإلكتروني..."
                                variant="outlined"
                                density="comfortable"
                                clearable
                                hide-details
                            ></v-text-field>
                        </v-col>
                        <v-col cols="12" md="4">
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
                            ></v-select>
                        </v-col>
                    </v-row>
                </v-card-text>
            </v-card>

            <!-- Users Table -->
            <v-card>
                <v-data-table
                    :headers="headers"
                    :items="filteredUsers"
                    :loading="userStore.loading"
                    loading-text="جاري تحميل البيانات..."
                    no-data-text="لا يوجد مستخدمين"
                    items-per-page-text="عدد الصفوف في الصفحة:"
                    class="elevation-0"
                    :custom-filter="customFilter"
                    :search="search"
                >
                    <!-- User Column -->
                    <template v-slot:item.user="{ item }">
                        <div class="d-flex align-center py-3">
                            <v-avatar color="primary" variant="tonal" class="me-3">
                                <v-img v-if="item.avatar" :src="item.avatar"></v-img>
                                <span v-else class="text-h6">{{ item.name.charAt(0) }}</span>
                            </v-avatar>
                            <div>
                                <div class="font-weight-bold">{{ item.name }}</div>
                                <div class="text-caption text-medium-emphasis">{{ item.email }}</div>
                            </div>
                        </div>
                    </template>

                    <!-- Roles Column -->
                    <template v-slot:item.roles="{ item }">
                        <div class="d-flex flex-wrap ga-1">
                            <v-chip
                                v-for="role in item.roles"
                                :key="role.id"
                                size="small"
                                :color="getRoleColor(role.name)"
                                variant="tonal"
                            >
                                {{ role.name }}
                            </v-chip>
                        </div>
                        <span v-if="!item.roles || item.roles.length === 0" class="text-medium-emphasis">
                            لا يوجد أدوار
                        </span>
                    </template>

                    <!-- Created At Column -->
                    <template v-slot:item.created_at="{ item }">
                        <div class="text-caption">
                            {{ formatDate(item.created_at) }}
                        </div>
                    </template>

                    <!-- Actions Column -->
                    <template v-slot:item.actions="{ item }">
                        <v-btn
                            icon="mdi-pencil"
                            variant="text"
                            size="small"
                            color="primary"
                            @click="openEditDialog(item)"
                        ></v-btn>
                        <v-btn
                            icon="mdi-delete"
                            variant="text"
                            size="small"
                            color="error"
                            @click="openDeleteDialog(item)"
                        ></v-btn>
                    </template>
                </v-data-table>
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
import { ref, computed, onMounted } from 'vue';
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
const search = ref('');
const filterRole = ref(null);
const dialog = ref(false);
const deleteDialog = ref(false);
const selectedUser = ref(null);

// ====================================
// Table Headers
// ====================================
const headers = [
    { title: 'المستخدم', key: 'user', sortable: false },
    { title: 'الأدوار', key: 'roles', sortable: false },
    { title: 'تاريخ الإنشاء', key: 'created_at', sortable: true },
    { title: 'الإجراءات', key: 'actions', sortable: false, align: 'center' },
];

// ====================================
// Computed
// ====================================

/**
 * المستخدمين المصفاة حسب الدور
 */
const filteredUsers = computed(() => {
    if (!filterRole.value) {
        return userStore.users;
    }
    
    return userStore.users.filter(user => 
        user.roles?.some(role => role.id === filterRole.value)
    );
});

// ====================================
// Methods
// ====================================

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
 * عند حفظ مستخدم (إضافة أو تعديل)
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
 * Custom Filter للبحث في الاسم والبريد والأدوار
 * يدعم العربية والإنجليزية وغير حساس للحروف الكبيرة/الصغيرة
 */
function customFilter(value, query, item) {
    if (!query) return true;
    
    // تحويل query إلى lowercase وإزالة المسافات الزائدة
    const searchQuery = query.toString().toLowerCase().trim();
    console.log(searchQuery)
    // الحصول على البيانات من المستخدم
    const name = (item.name || '').toLowerCase().trim();
    const email = (item.email || '').toLowerCase().trim();
    
    // الحصول على الأدوار
    let roles = '';
    if (item.roles && Array.isArray(item.roles)) {
        roles = item.roles
            .map(r => (r.name || '').toLowerCase())
            .join(' ');
    }
    
    // البحث في جميع الحقول
    return name.includes(searchQuery) || 
           email.includes(searchQuery) || 
           roles.includes(searchQuery);
}

// ====================================
// Lifecycle Hooks
// ====================================
onMounted(async () => {
    // جلب البيانات عند تحميل الصفحة
    await userStore.fetchUsers();
    await roleStore.fetchRoles();
});
</script>

<style scoped>
/* Styles مخصصة */
</style>