<template>
    <v-dialog
        :model-value="modelValue"
        @update:model-value="$emit('update:modelValue', $event)"
        max-width="700"
        scrollable
    >
        <v-card v-if="role" class="role-view-dialog">
            <v-card-title class="bg-primary pa-4">
                <div class="d-flex align-center">
                    <v-icon color="white" :class="isRTL ? 'ms-2' : 'me-2'">mdi-shield-account</v-icon>
                    <span class="text-h5 text-white">
                        {{ t('roles.roleDetails') }}
                    </span>
                </div>
            </v-card-title>

            <v-divider></v-divider>

            <!-- المحتوى القابل للـ Scroll -->
            <v-card-text class="pa-6" style="max-height: 60vh; overflow-y: auto;">
                <!-- المعلومات الأساسية -->
                <div class="mb-6">
                    <v-chip
                        :color="getRoleColor(role.name)"
                        size="large"
                        label
                        class="mb-4"
                    >
                        <v-icon :start="!isRTL" :end="isRTL">mdi-shield</v-icon>
                        {{ role.display_name }}
                    </v-chip>

                    <div class="d-flex gap-4 mb-4 flex-wrap">
                        <v-chip variant="tonal">
                            <v-icon :start="!isRTL" :end="isRTL">mdi-key</v-icon>
                            {{ role.permissions_count || role.permissions?.length || 0 }} {{ t('roles.permissions') }}
                        </v-chip>
                        <v-chip variant="tonal">
                            <v-icon :start="!isRTL" :end="isRTL">mdi-account-group</v-icon>
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
                            <v-list-item-subtitle>{{ formatDate(role.created_at) }}</v-list-item-subtitle>
                        </v-list-item>
                    </v-list>
                </div>

                <v-divider class="mb-4"></v-divider>

                <!-- الصلاحيات -->
                <h3 class="text-h6 mb-3">
                    <v-icon :class="isRTL ? 'ms-2' : 'me-2'">mdi-key</v-icon>
                    {{ t('roles.permissions') }}
                </h3>

                <v-card variant="outlined" v-if="role.permissions && role.permissions.length > 0" class="permissions-card">
                    <v-list>
                        <template v-for="(group, groupName) in groupedPermissions" :key="groupName">
                            <v-list-subheader class="permission-group-header">
                                <v-icon :color="getGroupColor(groupName)" :class="isRTL ? 'ms-2' : 'me-2'">
                                    {{ getGroupIcon(groupName) }}
                                </v-icon>
                                {{ groupName }}
                            </v-list-subheader>

                            <v-list-item
                                v-for="permission in group"
                                :key="permission.id"
                                density="compact"
                                class="permission-item"
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
                    {{ t('roles.noPermissions') }}
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

const { t, currentLocale } = useLanguage();

const isRTL = computed(() => currentLocale.value.dir === 'rtl');

// تجميع الصلاحيات
const groupedPermissions = computed(() => {
    if (!props.role?.permissions) return {};
    
    return props.role.permissions.reduce((groups, permission) => {
        const group = permission.group || t('roles.other');
        if (!groups[group]) {
            groups[group] = [];
        }
        groups[group].push(permission);
        return groups;
    }, {});
});

// تنسيق التاريخ
const formatDate = (date) => {
    if (!date) return '';
    const locale = currentLocale.value.code === 'ar' ? 'ar-EG' : 'en-US';
    return new Date(date).toLocaleDateString(locale, {
        year: 'numeric',
        month: 'long',
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit',
    });
};

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
        'Users': 'primary',
        'الأدوار': 'info',
        'Roles': 'info',
        'المنتجات': 'success',
        'Products': 'success',
        'الطلبات': 'warning',
        'Orders': 'warning',
        'التقارير': 'purple',
        'Reports': 'purple',
        'الإعدادات': 'orange',
        'Settings': 'orange',
    };
    return colors[groupName] || 'grey';
};

const getGroupIcon = (groupName) => {
    const icons = {
        'المستخدمين': 'mdi-account-group',
        'Users': 'mdi-account-group',
        'الأدوار': 'mdi-shield-account',
        'Roles': 'mdi-shield-account',
        'المنتجات': 'mdi-package-variant',
        'Products': 'mdi-package-variant',
        'الطلبات': 'mdi-cart',
        'Orders': 'mdi-cart',
        'التقارير': 'mdi-chart-bar',
        'Reports': 'mdi-chart-bar',
        'الإعدادات': 'mdi-cog',
        'Settings': 'mdi-cog',
    };
    return icons[groupName] || 'mdi-key';
};
</script>

<style scoped>
/* Dialog RTL */
.role-view-dialog {
    direction: inherit;
}

/* Scrollbar Styling */
.v-card-text::-webkit-scrollbar {
    width: 8px;
}

.v-card-text::-webkit-scrollbar-track {
    background: rgba(0, 0, 0, 0.05);
    border-radius: 4px;
}

.v-card-text::-webkit-scrollbar-thumb {
    background: rgba(0, 0, 0, 0.2);
    border-radius: 4px;
}

.v-card-text::-webkit-scrollbar-thumb:hover {
    background: rgba(0, 0, 0, 0.3);
}

/* Permissions Card */
.permissions-card {
    max-height: none;
}

/* Permission Group Header RTL */
[dir="rtl"] .permission-group-header {
    text-align: right;
}

[dir="ltr"] .permission-group-header {
    text-align: left;
}

/* Permission Item RTL */
[dir="rtl"] .permission-item :deep(.v-list-item__prepend) {
    margin-inline-start: 0;
    margin-inline-end: 16px;
}

[dir="ltr"] .permission-item :deep(.v-list-item__prepend) {
    margin-inline-end: 0;
    margin-inline-start: 16px;
}

/* List Item Subtitle RTL */
[dir="rtl"] .v-list-item-subtitle {
    text-align: right;
}

[dir="ltr"] .v-list-item-subtitle {
    text-align: left;
}

/* Card Actions RTL */
[dir="rtl"] .v-card-actions {
    flex-direction: row-reverse;
}

/* Chips Gap */
.d-flex.gap-4 {
    gap: 1rem;
}

/* Smooth Scroll */
.v-card-text {
    scroll-behavior: smooth;
}
</style>