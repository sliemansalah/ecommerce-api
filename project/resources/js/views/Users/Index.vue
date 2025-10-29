<template>
    <v-container fluid>
        <!-- الإحصائيات -->
        <v-row class="mb-4">
            <v-col cols="12" sm="6" md="3">
                <v-card>
                    <v-card-text>
                        <div class="stat-card-content">
                            <v-avatar color="primary" size="56" class="stat-avatar">
                                <v-icon size="32" color="white">mdi-account-group</v-icon>
                            </v-avatar>
                            <div class="stat-info">
                                <div class="text-h5 font-weight-bold">{{ stats?.total_users || 0 }}</div>
                                <div class="text-caption text-medium-emphasis">{{ t('users.totalUsers') }}</div>
                            </div>
                        </div>
                    </v-card-text>
                </v-card>
            </v-col>

            <v-col cols="12" sm="6" md="3">
                <v-card>
                    <v-card-text>
                        <div class="stat-card-content">
                            <v-avatar color="success" size="56" class="stat-avatar">
                                <v-icon size="32" color="white">mdi-account-check</v-icon>
                            </v-avatar>
                            <div class="stat-info">
                                <div class="text-h5 font-weight-bold">{{ stats?.active_users || 0 }}</div>
                                <div class="text-caption text-medium-emphasis">{{ t('users.activeUsers') }}</div>
                            </div>
                        </div>
                    </v-card-text>
                </v-card>
            </v-col>

            <v-col cols="12" sm="6" md="3">
                <v-card>
                    <v-card-text>
                        <div class="stat-card-content">
                            <v-avatar color="warning" size="56" class="stat-avatar">
                                <v-icon size="32" color="white">mdi-account-clock</v-icon>
                            </v-avatar>
                            <div class="stat-info">
                                <div class="text-h5 font-weight-bold">{{ stats?.inactive_users || 0 }}</div>
                                <div class="text-caption text-medium-emphasis">{{ t('users.inactiveUsers') }}</div>
                            </div>
                        </div>
                    </v-card-text>
                </v-card>
            </v-col>

            <v-col cols="12" sm="6" md="3">
                <v-card>
                    <v-card-text>
                        <div class="stat-card-content">
                            <v-avatar color="info" size="56" class="stat-avatar">
                                <v-icon size="32" color="white">mdi-shield-account</v-icon>
                            </v-avatar>
                            <div class="stat-info">
                                <div class="text-h5 font-weight-bold">{{ roles.length }}</div>
                                <div class="text-caption text-medium-emphasis">{{ t('users.totalRoles') }}</div>
                            </div>
                        </div>
                    </v-card-text>
                </v-card>
            </v-col>
        </v-row>

        <!-- الجدول -->
        <v-card>
            <v-card-title class="d-flex align-center py-4 flex-wrap ga-2">
                <div class="d-flex align-center">
                    <v-icon :class="isRTL ? 'ms-2' : 'me-2'">mdi-account-multiple</v-icon>
                    <span class="text-h6">{{ t('users.title') }}</span>
                </div>
                
                <v-spacer></v-spacer>

                <!-- البحث -->
                <v-text-field
                    v-model="search"
                    :label="t('common.search')"
                    prepend-inner-icon="mdi-magnify"
                    variant="outlined"
                    density="compact"
                    hide-details
                    clearable
                    style="max-width: 300px;"
                    @update:model-value="handleSearch"
                ></v-text-field>

                <!-- الفلترة -->
                <v-menu location="bottom" :close-on-content-click="false">
                    <template v-slot:activator="{ props }">
                        <v-btn
                            v-bind="props"
                            icon
                            variant="text"
                        >
                            <v-badge
                                :content="activeFiltersCount"
                                :model-value="activeFiltersCount > 0"
                                color="primary"
                            >
                                <v-icon>mdi-filter-variant</v-icon>
                            </v-badge>
                        </v-btn>
                    </template>

                    <v-card min-width="300">
                        <v-card-title>{{ t('common.filters') }}</v-card-title>
                        <v-divider></v-divider>
                        <v-card-text>
                            <v-select
                                v-model="filters.is_active"
                                :label="t('users.status')"
                                :items="statusOptions"
                                variant="outlined"
                                density="compact"
                                clearable
                                class="mb-3"
                            ></v-select>

                            <v-select
                                v-model="filters.role"
                                :label="t('users.role')"
                                :items="roles"
                                item-title="display_name"
                                item-value="name"
                                variant="outlined"
                                density="compact"
                                clearable
                            ></v-select>
                        </v-card-text>
                        <v-card-actions>
                            <v-spacer></v-spacer>
                            <v-btn @click="clearFilters" variant="text">{{ t('common.clear') }}</v-btn>
                            <v-btn @click="applyFiltersAndClose" color="primary">{{ t('common.apply') }}</v-btn>
                        </v-card-actions>
                    </v-card>
                </v-menu>

                <!-- زر إضافة -->
                <v-btn
                    color="primary"
                    @click="openCreateDialog"
                    class="add-user-btn"
                >
                    <v-icon class="btn-icon">mdi-plus</v-icon>
                    <span>{{ t('users.addNew') }}</span>
                </v-btn>
            </v-card-title>

            <v-divider></v-divider>

            <!-- Loading -->
            <v-progress-linear
                v-if="loading"
                indeterminate
                color="primary"
            ></v-progress-linear>

            <!-- الجدول -->
            <div :class="isRTL ? 'rtl-table' : 'ltr-table'">
                <v-data-table-server
                    v-model:items-per-page="itemsPerPage"
                    v-model:page="page"
                    :headers="headers"
                    :items="users"
                    :items-length="pagination.total"
                    :loading="loading"
                    class="users-table"
                    @update:options="loadUsers"
                >
                    <!-- الصورة والاسم -->
                    <template v-slot:item.name="{ item }">
                        <div class="user-cell">
                            <v-avatar
                                :color="item.avatar ? '' : 'primary'"
                                size="40"
                                class="user-avatar"
                            >
                                <v-img v-if="item.avatar" :src="item.avatar"></v-img>
                                <span v-else class="text-white">{{ item.initials }}</span>
                            </v-avatar>
                            <div class="user-info">
                                <div class="font-weight-medium">{{ item.name }}</div>
                                <div class="text-caption text-medium-emphasis">{{ item.email }}</div>
                            </div>
                        </div>
                    </template>

                    <!-- رقم الهاتف -->
                    <template v-slot:item.phone="{ item }">
                        <span class="cell-content">
                            {{ item.phone || '-' }}
                        </span>
                    </template>

                    <!-- الدور -->
                    <template v-slot:item.roles="{ item }">
                        <div class="roles-cell">
                            <v-chip
                                v-for="role in item.roles"
                                :key="role.id"
                                size="small"
                                class="role-chip"
                                :color="getRoleColor(role.name)"
                            >
                                {{ role.display_name }}
                            </v-chip>
                        </div>
                    </template>

                    <!-- الحالة -->
                    <template v-slot:item.is_active="{ item }">
                        <div class="status-cell">
                            <v-chip
                                :color="item.is_active ? 'success' : 'error'"
                                size="small"
                            >
                                {{ item.is_active ? t('common.active') : t('common.inactive') }}
                            </v-chip>
                        </div>
                    </template>

                    <!-- آخر تسجيل دخول -->
                    <template v-slot:item.last_login_at="{ item }">
                        <span class="cell-content text-caption">
                            {{ item.last_login_at || t('users.neverLoggedIn') }}
                        </span>
                    </template>

                    <!-- الإجراءات -->
                    <template v-slot:item.actions="{ item }">
                        <div class="actions-cell">
                            <v-menu :location="isRTL ? 'start' : 'end'">
                                <template v-slot:activator="{ props }">
                                    <v-btn
                                        v-bind="props"
                                        icon="mdi-dots-vertical"
                                        variant="text"
                                        size="small"
                                    ></v-btn>
                                </template>

                                <v-list density="compact">
                                    <v-list-item @click="viewUser(item)">
                                        <template v-slot:prepend>
                                            <v-icon>mdi-eye</v-icon>
                                        </template>
                                        <v-list-item-title>{{ t('common.view') }}</v-list-item-title>
                                    </v-list-item>

                                    <v-list-item @click="editUser(item)">
                                        <template v-slot:prepend>
                                            <v-icon>mdi-pencil</v-icon>
                                        </template>
                                        <v-list-item-title>{{ t('common.edit') }}</v-list-item-title>
                                    </v-list-item>

                                    <v-list-item @click="toggleStatus(item)">
                                        <template v-slot:prepend>
                                            <v-icon>{{ item.is_active ? 'mdi-account-off' : 'mdi-account-check' }}</v-icon>
                                        </template>
                                        <v-list-item-title>
                                            {{ item.is_active ? t('users.deactivate') : t('users.activate') }}
                                        </v-list-item-title>
                                    </v-list-item>

                                    <v-divider></v-divider>

                                    <v-list-item @click="confirmDelete(item)" class="text-error">
                                        <template v-slot:prepend>
                                            <v-icon color="error">mdi-delete</v-icon>
                                        </template>
                                        <v-list-item-title>{{ t('common.delete') }}</v-list-item-title>
                                    </v-list-item>
                                </v-list>
                            </v-menu>
                        </div>
                    </template>
                </v-data-table-server>
            </div>
        </v-card>

        <!-- Dialogs -->
        <user-form-dialog
            v-model="formDialog"
            :user="selectedUser"
            :roles="roles"
            @saved="handleUserSaved"
        />

        <user-view-dialog
            v-model="viewDialog"
            :user="selectedUser"
        />

        <v-dialog v-model="deleteDialog" max-width="500">
            <v-card>
                <v-card-title class="text-h5">{{ t('users.confirmDelete') }}</v-card-title>
                <v-card-text>
                    {{ t('users.confirmDeleteMessage', { name: selectedUser?.name }) }}
                </v-card-text>
                <v-card-actions>
                    <v-spacer></v-spacer>
                    <v-btn @click="deleteDialog = false">{{ t('common.cancel') }}</v-btn>
                    <v-btn
                        color="error"
                        :loading="loading"
                        @click="handleDelete"
                    >
                        {{ t('common.delete') }}
                    </v-btn>
                </v-card-actions>
            </v-card>
        </v-dialog>
    </v-container>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue';
import { useUsers } from '../../composables/useUsers';
import { useRoles } from '../../composables/useRoles';
import { useLanguage } from '../../composables/useLanguage';
import UserFormDialog from '../../components/Users/UserFormDialog.vue';
import UserViewDialog from '../../components/Users/UserViewDialog.vue';

const { t, currentLocale } = useLanguage();
const { 
    users, 
    loading, 
    pagination, 
    stats,
    fetchUsers, 
    deleteUser, 
    toggleUserStatus,
    fetchStats,
} = useUsers();
const { roles, fetchRoles } = useRoles();

const search = ref('');
const page = ref(1);
const itemsPerPage = ref(15);
const filters = ref({
    is_active: null,
    role: null,
});

const formDialog = ref(false);
const viewDialog = ref(false);
const deleteDialog = ref(false);
const selectedUser = ref(null);
const filterMenu = ref(false);

const isRTL = computed(() => currentLocale.value.dir === 'rtl');

const headers = computed(() => [
    { title: t('users.name'), key: 'name', sortable: true },
    { title: t('users.phone'), key: 'phone', sortable: false },
    { title: t('users.role'), key: 'roles', sortable: false },
    { title: t('users.status'), key: 'is_active', sortable: true },
    { title: t('users.lastLogin'), key: 'last_login_at', sortable: true },
    { title: t('common.actions'), key: 'actions', sortable: false },
]);

const statusOptions = computed(() => [
    { title: t('common.active'), value: true },
    { title: t('common.inactive'), value: false },
]);

const activeFiltersCount = computed(() => {
    let count = 0;
    if (filters.value.is_active !== null) count++;
    if (filters.value.role) count++;
    return count;
});

const loadUsers = async ({ page: p, itemsPerPage: perPage, sortBy }) => {
    const params = {
        page: p,
        per_page: perPage,
        search: search.value,
        ...filters.value,
    };

    if (sortBy && sortBy.length > 0) {
        params.sort_by = sortBy[0].key;
        params.sort_order = sortBy[0].order;
    }

    await fetchUsers(params);
};

let searchTimeout;
const handleSearch = () => {
    clearTimeout(searchTimeout);
    searchTimeout = setTimeout(() => {
        page.value = 1;
        loadUsers({ page: page.value, itemsPerPage: itemsPerPage.value });
    }, 500);
};

const applyFilters = () => {
    page.value = 1;
    loadUsers({ page: page.value, itemsPerPage: itemsPerPage.value });
};

// تطبيق الفلاتر وإغلاق القائمة
const applyFiltersAndClose = () => {
    applyFilters();
};

const clearFilters = () => {
    filters.value = {
        is_active: null,
        role: null,
    };
    applyFilters();
};

const openCreateDialog = () => {
    selectedUser.value = null;
    formDialog.value = true;
};

const viewUser = (user) => {
    selectedUser.value = user;
    viewDialog.value = true;
};

const editUser = (user) => {
    selectedUser.value = user;
    formDialog.value = true;
};

const toggleStatus = async (user) => {
    await toggleUserStatus(user.id);
    await loadUsers({ page: page.value, itemsPerPage: itemsPerPage.value });
    await fetchStats();
};

const confirmDelete = (user) => {
    selectedUser.value = user;
    deleteDialog.value = true;
};

const handleDelete = async () => {
    await deleteUser(selectedUser.value.id);
    deleteDialog.value = false;
    selectedUser.value = null;
    await loadUsers({ page: page.value, itemsPerPage: itemsPerPage.value });
    await fetchStats();
};

const handleUserSaved = async () => {
    formDialog.value = false;
    selectedUser.value = null;
    await loadUsers({ page: page.value, itemsPerPage: itemsPerPage.value });
    await fetchStats();
};

const getRoleColor = (roleName) => {
    const colors = {
        'super-admin': 'error',
        'admin': 'warning',
        'manager': 'info',
        'employee': 'success',
        'customer': 'primary',
    };
    return colors[roleName] || 'default';
};

onMounted(async () => {
    await Promise.all([
        fetchRoles(),
        fetchStats(),
    ]);
});
</script>

<style scoped>
/* ============================================ */
/* Float Helpers for RTL/LTR */
/* ============================================ */
[dir="rtl"] .float-start {
    float: right !important;
}

[dir="rtl"] .float-end {
    float: left !important;
}

[dir="ltr"] .float-start {
    float: left !important;
}

[dir="ltr"] .float-end {
    float: right !important;
}

.clearfix::after {
    content: "";
    display: table;
    clear: both;
}

/* ============================================ */
/* Statistics Cards RTL */
/* ============================================ */
.stat-card-content {
    display: flex;
    align-items: center;
    gap: 1rem;
}

[dir="rtl"] .stat-card-content {
    flex-direction: row;
}

[dir="ltr"] .stat-card-content {
    flex-direction: row;
}

[dir="rtl"] .stat-avatar {
    order: 2;
}

[dir="ltr"] .stat-avatar {
    order: 1;
}

[dir="rtl"] .stat-info {
    order: 1;
    text-align: right;
}

[dir="ltr"] .stat-info {
    order: 2;
    text-align: left;
}

/* ============================================ */
/* Add Button RTL */
/* ============================================ */
.add-user-btn {
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

[dir="rtl"] .add-user-btn .btn-icon {
    order: 2;
    margin-left: 0;
    margin-right: 0;
}

[dir="rtl"] .add-user-btn span {
    order: 1;
}

[dir="ltr"] .add-user-btn .btn-icon {
    order: 1;
    margin-right: 0;
    margin-left: 0;
}

[dir="ltr"] .add-user-btn span {
    order: 2;
}

/* ============================================ */
/* RTL Table - عربي */
/* ============================================ */
.rtl-table {
    direction: rtl;
}

.rtl-table :deep(.v-data-table) {
    direction: rtl;
}

.rtl-table :deep(.v-data-table__th),
.rtl-table :deep(.v-data-table__td) {
    text-align: right !important;
}

.rtl-table :deep(.v-data-table__th):last-child,
.rtl-table :deep(.v-data-table__td):last-child {
    text-align: center !important;
}

.rtl-table :deep(th:nth-child(4)),
.rtl-table :deep(td:nth-child(4)) {
    text-align: center !important;
}

.rtl-table .user-cell {
    display: flex;
    align-items: center;
    gap: 12px;
    direction: rtl;
}

.rtl-table .user-avatar {
    order: 2;
}

.rtl-table .user-info {
    order: 1;
    text-align: right;
}

.rtl-table .cell-content {
    display: block;
    text-align: right;
}

.rtl-table .roles-cell {
    text-align: right;
}

.rtl-table .role-chip {
    margin-left: 0.25rem;
    margin-right: 0;
}

.rtl-table .status-cell,
.rtl-table .actions-cell {
    text-align: center;
}

/* ============================================ */
/* LTR Table - English */
/* ============================================ */
.ltr-table {
    direction: ltr;
}

.ltr-table :deep(.v-data-table) {
    direction: ltr;
}

.ltr-table :deep(.v-data-table__th),
.ltr-table :deep(.v-data-table__td) {
    text-align: left !important;
}

.ltr-table :deep(.v-data-table__th):last-child,
.ltr-table :deep(.v-data-table__td):last-child {
    text-align: center !important;
}

.ltr-table :deep(th:nth-child(4)),
.ltr-table :deep(td:nth-child(4)) {
    text-align: center !important;
}

.ltr-table .user-cell {
    display: flex;
    align-items: center;
    gap: 12px;
    direction: ltr;
}

.ltr-table .user-avatar {
    order: 1;
}

.ltr-table .user-info {
    order: 2;
    text-align: left;
}

.ltr-table .cell-content {
    display: block;
    text-align: left;
}

.ltr-table .roles-cell {
    text-align: left;
}

.ltr-table .role-chip {
    margin-right: 0.25rem;
    margin-left: 0;
}

.ltr-table .status-cell,
.ltr-table .actions-cell {
    text-align: center;
}

/* ============================================ */
/* Common Styles */
/* ============================================ */
.user-cell {
    padding: 8px 0;
}

.user-info {
    min-width: 150px;
}
</style>