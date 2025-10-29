<template>
    <v-dialog
        :model-value="modelValue"
        @update:model-value="$emit('update:modelValue', $event)"
        max-width="700"
        scrollable
    >
        <v-card v-if="role">
            <v-card-title class="bg-primary">
                <span class="text-h5 text-white">
                    <v-icon color="white" class="me-2">mdi-shield-account</v-icon>
                    {{ t('roles.roleDetails') }}
                </span>
            </v-card-title>

            <v-divider></v-divider>

            <v-card-text class="pa-6">
                <!-- المعلومات الأساسية -->
                <div class="mb-6">
                    <v-chip
                        :color="getRoleColor(role.name)"
                        size="large"
                        label
                        class="mb-4"
                    >
                        <v-icon start>mdi-shield</v-icon>
                        {{ role.display_name }}
                    </v-chip>

                    <div class="d-flex gap-4 mb-4">
                        <v-chip variant="tonal" prepend-icon="mdi-key">
                            {{ role.permissions_count || role.permissions?.length || 0 }} {{ t('roles.permissions') }}
                        </v-chip>
                        <v-chip variant="tonal" prepend-icon="mdi-account-group">
                            {{ role.users_count || 0 }} {{ t('roles.users') }}
                        </v-chip>
                    </div>

                    <v-list lines="one" density="compact" class="bg-transparent">
                        <v-list-item>
                            <template v-slot:prepend>
                                <v-icon>mdi-identifier</v-icon>
                            </template>
                            <v-list-item-title>{{ t('roles.technicalName') }}</v-list-item-title>
                            <v-list-item-subtitle>{{ role.name }}</v-list-item-subtitle>
                        </v-list-item>

                        <v-list-item>
                            <template v-slot:prepend>
                                <v-icon>mdi-calendar</v-icon>
                            </template>
                            <v-list-item-title>{{ t('roles.createdAt') }}</v-list-item-title>
                            <v-list-item-subtitle>{{ role.created_at }}</v-list-item-subtitle>
                        </v-list-item>
                    </v-list>
                </div>

                <v-divider class="mb-4"></v-divider>

                <!-- الصلاحيات -->
                <h3 class="text-h6 mb-3">
                    <v-icon class="me-2">mdi-key</v-icon>
                    {{ t('roles.permissions') }}
                </h3>

                <v-card variant="outlined" v-if="role.permissions && role.permissions.length > 0">
                    <v-list>
                        <template v-for="(group, groupName) in groupedPermissions" :key="groupName">
                            <v-list-subheader>
                                <v-icon :color="getGroupColor(groupName)" class="me-2">
                                    {{ getGroupIcon(groupName) }}
                                </v-icon>
                                {{ groupName }}
                            </v-list-subheader>

                            <v-list-item
                                v-for="permission in group"
                                :key="permission.id"
                                density="compact"
                            >
                                <template v-slot:prepend>
                                    <v-icon color="success" size="small">mdi-check-circle</v-icon>
                                </template>
                                <v-list-item-title>{{ permission.display_name }}</v-list-item-title>
                                <v-list-item-subtitle class="text-caption">
                                    {{ permission.name }}
                                </v-list-item-subtitle>
                            </v-list-item>

                            <v-divider></v-divider>
                        </template>
                    </v-list>
                </v-card>

                <v-alert
                    v-else
                    type="info"
                    variant="tonal"
                    density="compact"
                >
                    لا توجد صلاحيات محددة لهذا الدور
                </v-alert>
            </v-card-text>

            <v-divider></v-divider>

            <v-card-actions class="pa-4">
                <v-spacer></v-spacer>
                <v-btn
                    color="primary"
                    @click="$emit('update:modelValue', false)"
                >
                    {{ t('common.close') }}
                </v-btn>
            </v-card-actions>
        </v-card>
    </v-dialog>
</template>

<script setup>
import { computed } from 'vue';
import { useLanguage } from '../../composables/useLanguage';

const props = defineProps({
    modelValue: Boolean,
    role: Object,
});

defineEmits(['update:modelValue']);

const { t } = useLanguage();

// تجميع الصلاحيات
const groupedPermissions = computed(() => {
    if (!props.role?.permissions) return {};
    
    return props.role.permissions.reduce((groups, permission) => {
        const group = permission.group || 'أخرى';
        if (!groups[group]) {
            groups[group] = [];
        }
        groups[group].push(permission);
        return groups;
    }, {});
});

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

// ألوان وأيقونات المجموعات
const getGroupColor = (groupName) => {
    const colors = {
        'المستخدمين': 'primary',
        'الأدوار': 'info',
        'المنتجات': 'success',
        'الطلبات': 'warning',
        'التقارير': 'purple',
        'الإعدادات': 'orange',
    };
    return colors[groupName] || 'grey';
};

const getGroupIcon = (groupName) => {
    const icons = {
        'المستخدمين': 'mdi-account-group',
        'الأدوار': 'mdi-shield-account',
        'المنتجات': 'mdi-package-variant',
        'الطلبات': 'mdi-cart',
        'التقارير': 'mdi-chart-bar',
        'الإعدادات': 'mdi-cog',
    };
    return icons[groupName] || 'mdi-key';
};
</script>