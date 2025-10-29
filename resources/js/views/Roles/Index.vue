<template>
    <v-container fluid>
        <!-- Header -->
        <v-row class="mb-4">
            <v-col cols="12">
                <div class="d-flex align-center">
                    <v-icon size="40" color="primary" class="me-3">mdi-shield-account</v-icon>
                    <div>
                        <h1 class="text-h4 font-weight-bold">{{ t('roles.title') }}</h1>
                        <p class="text-body-2 text-medium-emphasis">{{ t('roles.subtitle') }}</p>
                    </div>
                </div>
            </v-col>
        </v-row>

        <!-- الأدوار -->
        <v-row>
            <v-col
                v-for="role in roles"
                :key="role.id"
                cols="12"
                md="6"
                lg="4"
            >
                <v-card class="role-card" hover>
                    <v-card-title class="d-flex align-center pb-2">
                        <v-chip
                            :color="getRoleColor(role.name)"
                            label
                            size="small"
                            class="me-2"
                        >
                            <v-icon start size="16">mdi-shield</v-icon>
                            {{ role.display_name }}
                        </v-chip>
                        <v-spacer></v-spacer>
                        
                        <v-menu>
                            <template v-slot:activator="{ props }">
                                <v-btn
                                    v-bind="props"
                                    icon="mdi-dots-vertical"
                                    variant="text"
                                    size="small"
                                ></v-btn>
                            </template>

                            <v-list density="compact">
                                <v-list-item @click="viewRole(role)">
                                    <template v-slot:prepend>
                                        <v-icon>mdi-eye</v-icon>
                                    </template>
                                    <v-list-item-title>{{ t('common.view') }}</v-list-item-title>
                                </v-list-item>

                                <v-list-item 
                                    @click="editRole(role)"
                                    :disabled="role.name === 'super-admin'"
                                >
                                    <template v-slot:prepend>
                                        <v-icon>mdi-pencil</v-icon>
                                    </template>
                                    <v-list-item-title>{{ t('common.edit') }}</v-list-item-title>
                                </v-list-item>

                                <v-divider></v-divider>

                                <v-list-item 
                                    @click="confirmDelete(role)"
                                    :disabled="isProtectedRole(role.name)"
                                    class="text-error"
                                >
                                    <template v-slot:prepend>
                                        <v-icon color="error">mdi-delete</v-icon>
                                    </template>
                                    <v-list-item-title>{{ t('common.delete') }}</v-list-item-title>
                                </v-list-item>
                            </v-list>
                        </v-menu>
                    </v-card-title>

                    <v-card-text>
                        <!-- الإحصائيات -->
                        <div class="d-flex justify-space-around mb-4">
                            <div class="text-center">
                                <div class="text-h5 font-weight-bold primary--text">
                                    {{ role.permissions_count || 0 }}
                                </div>
                                <div class="text-caption text-medium-emphasis">
                                    {{ t('roles.permissions') }}
                                </div>
                            </div>
                            <v-divider vertical></v-divider>
                            <div class="text-center">
                                <div class="text-h5 font-weight-bold success--text">
                                    {{ role.users_count || 0 }}
                                </div>
                                <div class="text-caption text-medium-emphasis">
                                    {{ t('roles.users') }}
                                </div>
                            </div>
                        </div>

                        <!-- الوصف -->
                        <v-chip
                            size="x-small"
                            variant="tonal"
                            prepend-icon="mdi-clock-outline"
                            class="mb-2"
                        >
                            {{ t('common.createdAt') }}: {{ formatDate(role.created_at) }}
                        </v-chip>
                    </v-card-text>

                    <v-card-actions>
                        <v-btn
                            block
                            variant="tonal"
                            color="primary"
                            @click="viewRole(role)"
                        >
                            {{ t('roles.viewPermissions') }}
                            <v-icon end>mdi-arrow-left</v-icon>
                        </v-btn>
                    </v-card-actions>
                </v-card>
            </v-col>

            <!-- بطاقة إضافة دور جديد -->
            <v-col cols="12" md="6" lg="4">
                <v-card
                    class="role-card-add"
                    hover
                    @click="openCreateDialog"
                >
                    <v-card-text class="d-flex flex-column align-center justify-center" style="min-height: 250px;">
                        <v-avatar color="primary" size="80" class="mb-4">
                            <v-icon size="50" color="white">mdi-plus</v-icon>
                        </v-avatar>
                        <h3 class="text-h6 mb-2">{{ t('roles.addNew') }}</h3>
                        <p class="text-body-2 text-medium-emphasis text-center">
                            {{ t('roles.addNewDescription') }}
                        </p>
                    </v-card-text>
                </v-card>
            </v-col>
        </v-row>

        <!-- Dialog للإنشاء/التعديل -->
        <role-form-dialog
            v-model="formDialog"
            :role="selectedRole"
            :permissions="permissions"
            @saved="handleRoleSaved"
        />

        <!-- Dialog للعرض -->
        <role-view-dialog
            v-model="viewDialog"
            :role="selectedRole"
        />

        <!-- Dialog للتأكيد على الحذف -->
        <v-dialog v-model="deleteDialog" max-width="500">
            <v-card>
                <v-card-title class="text-h5">{{ t('roles.confirmDelete') }}</v-card-title>
                <v-card-text>
                    {{ t('roles.confirmDeleteMessage', { name: selectedRole?.display_name }) }}
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
import { ref, onMounted } from 'vue';
import { useRoles } from '../../composables/useRoles';
import { usePermissions } from '../../composables/usePermissions';
import { useLanguage } from '../../composables/useLanguage';
import RoleFormDialog from '../../components/Roles/RoleFormDialog.vue';
import RoleViewDialog from '../../components/Roles/RoleViewDialog.vue';

const { t } = useLanguage();
const { roles, loading, fetchRoles, deleteRole } = useRoles();
const { permissions, fetchPermissions } = usePermissions();

const formDialog = ref(false);
const viewDialog = ref(false);
const deleteDialog = ref(false);
const selectedRole = ref(null);

// ألوان الأدوار
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

// الأدوار المحمية
const isProtectedRole = (roleName) => {
    return ['super-admin', 'admin', 'customer'].includes(roleName);
};

// تنسيق التاريخ
const formatDate = (date) => {
    if (!date) return '';
    return new Date(date).toLocaleDateString('ar-EG', {
        year: 'numeric',
        month: 'long',
        day: 'numeric',
    });
};

// فتح dialog الإنشاء
const openCreateDialog = () => {
    selectedRole.value = null;
    formDialog.value = true;
};

// عرض الدور
const viewRole = async (role) => {
    // جلب بيانات الدور مع الصلاحيات
    const { fetchRole } = useRoles();
    selectedRole.value = await fetchRole(role.id);
    viewDialog.value = true;
};

// تعديل الدور
const editRole = async (role) => {
    const { fetchRole } = useRoles();
    selectedRole.value = await fetchRole(role.id);
    formDialog.value = true;
};

// تأكيد الحذف
const confirmDelete = (role) => {
    selectedRole.value = role;
    deleteDialog.value = true;
};

// الحذف
const handleDelete = async () => {
    await deleteRole(selectedRole.value.id);
    deleteDialog.value = false;
    selectedRole.value = null;
    await fetchRoles();
};

// بعد الحفظ
const handleRoleSaved = async () => {
    formDialog.value = false;
    selectedRole.value = null;
    await fetchRoles();
};

onMounted(async () => {
    await Promise.all([
        fetchRoles(),
        fetchPermissions(),
    ]);
});
</script>

<style scoped>
.role-card {
    transition: all 0.3s ease;
}

.role-card:hover {
    transform: translateY(-5px);
}

.role-card-add {
    border: 2px dashed rgba(var(--v-theme-primary), 0.3);
    cursor: pointer;
    transition: all 0.3s ease;
}

.role-card-add:hover {
    border-color: rgb(var(--v-theme-primary));
    background: rgba(var(--v-theme-primary), 0.05);
}
</style>