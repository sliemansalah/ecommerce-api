<template>
    <v-dialog
        :model-value="modelValue"
        @update:model-value="$emit('update:modelValue', $event)"
        max-width="800"
        persistent
        scrollable
    >
        <v-card class="role-form-dialog">
            <v-card-title class="bg-primary pa-4">
                <div class="d-flex align-center">
                    <v-icon color="white" :class="isRTL ? 'ms-2' : 'me-2'">
                        {{ isEdit ? 'mdi-shield-edit' : 'mdi-shield-plus' }}
                    </v-icon>
                    <span class="text-h5 text-white">
                        {{ isEdit ? t('roles.editRole') : t('roles.addNew') }}
                    </span>
                </div>
            </v-card-title>

            <v-divider></v-divider>

            <!-- المحتوى القابل للـ Scroll -->
            <v-card-text class="pa-6" style="max-height: 60vh; overflow-y: auto;">
                <v-form ref="form" @submit.prevent="handleSubmit">
                    <v-row>
                        <!-- اسم الدور -->
                        <v-col cols="12">
                            <v-text-field
                                v-model="formData.name"
                                :label="t('roles.name') + ' *'"
                                :rules="nameRules"
                                prepend-inner-icon="mdi-shield"
                                variant="outlined"
                                density="comfortable"
                                :hint="t('roles.nameHint')"
                                persistent-hint
                            ></v-text-field>
                        </v-col>

                        <!-- الصلاحيات -->
                        <v-col cols="12">
                            <h3 class="text-h6 mb-3">
                                <v-icon :class="isRTL ? 'ms-2' : 'me-2'">mdi-key</v-icon>
                                {{ t('roles.permissions') }}
                            </h3>

                            <!-- البحث في الصلاحيات -->
                            <v-text-field
                                v-model="permissionsSearch"
                                :label="t('common.search')"
                                prepend-inner-icon="mdi-magnify"
                                variant="outlined"
                                density="compact"
                                clearable
                                class="mb-4"
                            ></v-text-field>

                            <!-- إحصائيات الصلاحيات -->
                            <v-alert
                                type="info"
                                variant="tonal"
                                density="compact"
                                class="mb-4"
                            >
                                {{ t('roles.selectedPermissions', { 
                                    selected: formData.permissions.length, 
                                    total: permissions.length 
                                }) }}
                            </v-alert>

                            <!-- الصلاحيات مجمعة -->
                            <v-card variant="outlined" class="permissions-card">
                                <v-list>
                                    <v-list-group
                                        v-for="(group, groupName) in filteredGroupedPermissions"
                                        :key="groupName"
                                    >
                                        <template v-slot:activator="{ props }">
                                            <v-list-item v-bind="props" class="group-item">
                                                <template v-slot:prepend>
                                                    <v-icon :color="getGroupColor(groupName)">
                                                        {{ getGroupIcon(groupName) }}
                                                    </v-icon>
                                                </template>
                                                <v-list-item-title class="font-weight-medium">
                                                    {{ getGroupDisplayName(groupName) }}
                                                </v-list-item-title>
                                                <template v-slot:append>
                                                    <v-chip size="small" variant="tonal" class="group-chip">
                                                        {{ selectedPermissionsInGroup(group).length }}/{{ group.length }}
                                                    </v-chip>
                                                </template>
                                            </v-list-item>
                                        </template>

                                        <v-list-item
                                            v-for="permission in group"
                                            :key="permission.id"
                                            density="compact"
                                            class="permission-checkbox-item"
                                        >
                                            <v-checkbox
                                                v-model="formData.permissions"
                                                :value="permission.name"
                                                :label="permission.display_name"
                                                color="primary"
                                                density="compact"
                                                hide-details
                                            >
                                                <template v-slot:label>
                                                    <div class="permission-label">
                                                        <div class="font-weight-medium">{{ permission.display_name }}</div>
                                                        <div class="text-caption text-medium-emphasis">{{ permission.name }}</div>
                                                    </div>
                                                </template>
                                            </v-checkbox>
                                        </v-list-item>

                                        <v-list-item class="group-actions">
                                            <v-btn
                                                size="small"
                                                variant="text"
                                                color="primary"
                                                @click="toggleGroupPermissions(group)"
                                            >
                                                {{ isGroupFullySelected(group) ? t('common.deselectAll') : t('common.selectAll') }}
                                            </v-btn>
                                        </v-list-item>
                                    </v-list-group>
                                </v-list>
                            </v-card>

                            <!-- رسالة عدم وجود نتائج -->
                            <v-alert
                                v-if="Object.keys(filteredGroupedPermissions).length === 0"
                                type="warning"
                                variant="tonal"
                                density="compact"
                                class="mt-4"
                            >
                                {{ t('common.noResults') }}
                            </v-alert>
                        </v-col>
                    </v-row>
                </v-form>
            </v-card-text>

            <v-divider></v-divider>

            <v-card-actions class="pa-4">
                <v-spacer></v-spacer>
                <v-btn @click="close" variant="text">
                    {{ t('common.cancel') }}
                </v-btn>
                <v-btn
                    color="primary"
                    :loading="loading"
                    @click="handleSubmit"
                >
                    <v-icon :start="!isRTL" :end="isRTL">
                        {{ isEdit ? 'mdi-content-save' : 'mdi-plus' }}
                    </v-icon>
                    {{ isEdit ? t('common.save') : t('common.create') }}
                </v-btn>
            </v-card-actions>
        </v-card>
    </v-dialog>
</template>

<script setup>
import { ref, computed, watch } from 'vue';
import { useRoles } from '../../composables/useRoles';
import { useLanguage } from '../../composables/useLanguage';

const props = defineProps({
    modelValue: Boolean,
    role: Object,
    permissions: {
        type: Array,
        default: () => []
    },
});

const emit = defineEmits(['update:modelValue', 'saved']);

const { t, currentLocale } = useLanguage();
const { createRole, updateRole, loading } = useRoles();

const form = ref(null);
const permissionsSearch = ref('');

const formData = ref({
    name: '',
    permissions: [],
});

const isEdit = computed(() => !!props.role);
const isRTL = computed(() => currentLocale.value.dir === 'rtl');

// تجميع الصلاحيات
const groupedPermissions = computed(() => {
    if (!props.permissions || props.permissions.length === 0) return {};
    
    return props.permissions.reduce((groups, permission) => {
        const group = permission.group || t('roles.other');
        if (!groups[group]) {
            groups[group] = [];
        }
        groups[group].push(permission);
        return groups;
    }, {});
});

// الصلاحيات المفلترة
const filteredGroupedPermissions = computed(() => {
    if (!permissionsSearch.value) return groupedPermissions.value;
    
    const search = permissionsSearch.value.toLowerCase();
    const filtered = {};
    
    Object.keys(groupedPermissions.value).forEach(groupName => {
        const filteredGroup = groupedPermissions.value[groupName].filter(permission => 
            permission.display_name.toLowerCase().includes(search) ||
            permission.name.toLowerCase().includes(search)
        );
        
        if (filteredGroup.length > 0) {
            filtered[groupName] = filteredGroup;
        }
    });
    
    return filtered;
});

// القواعد
const nameRules = [
    v => !!v || t('validation.required'),
    v => (v && v.length >= 3) || t('validation.minLength', { length: 3 }),
    v => /^[a-z0-9-_]+$/.test(v) || t('validation.slugFormat'),
];

// الصلاحيات المحددة في المجموعة
const selectedPermissionsInGroup = (group) => {
    return group.filter(p => formData.value.permissions.includes(p.name));
};

// هل المجموعة محددة بالكامل
const isGroupFullySelected = (group) => {
    return group.every(p => formData.value.permissions.includes(p.name));
};

// تبديل صلاحيات المجموعة
const toggleGroupPermissions = (group) => {
    const isFullySelected = isGroupFullySelected(group);
    
    if (isFullySelected) {
        // إزالة كل صلاحيات المجموعة
        formData.value.permissions = formData.value.permissions.filter(
            p => !group.map(g => g.name).includes(p)
        );
    } else {
        // إضافة كل صلاحيات المجموعة
        const groupPermissions = group.map(p => p.name);
        formData.value.permissions = [
            ...new Set([...formData.value.permissions, ...groupPermissions])
        ];
    }
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

const getGroupDisplayName = (groupName) => {
    return groupName;
};

// الإرسال
const handleSubmit = async () => {
    const { valid } = await form.value.validate();
    if (!valid) return;

    try {
        if (isEdit.value) {
            await updateRole(props.role.id, formData.value);
        } else {
            await createRole(formData.value);
        }
        emit('saved');
        close();
    } catch (error) {
        console.error('Error saving role:', error);
    }
};

// إغلاق
const close = () => {
    form.value?.reset();
    formData.value = {
        name: '',
        permissions: [],
    };
    permissionsSearch.value = '';
    emit('update:modelValue', false);
};

// مراقبة تغيير الدور
watch(() => props.role, (newRole) => {
    if (newRole) {
        formData.value = {
            name: newRole.name,
            permissions: newRole.permissions?.map(p => p.name) || [],
        };
    } else {
        formData.value = {
            name: '',
            permissions: [],
        };
    }
}, { immediate: true });

// مراقبة إغلاق الـ Dialog
watch(() => props.modelValue, (newValue) => {
    if (!newValue) {
        setTimeout(() => {
            form.value?.reset();
            formData.value = {
                name: '',
                permissions: [],
            };
            permissionsSearch.value = '';
        }, 300);
    }
});
</script>

<style scoped>
/* Dialog RTL */
.role-form-dialog {
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

/* Group Item RTL */
[dir="rtl"] .group-item :deep(.v-list-item__prepend) {
    margin-inline-start: 0;
    margin-inline-end: 16px;
}

[dir="ltr"] .group-item :deep(.v-list-item__prepend) {
    margin-inline-end: 0;
    margin-inline-start: 16px;
}

[dir="rtl"] .group-item :deep(.v-list-item__append) {
    margin-inline-start: 16px;
    margin-inline-end: 0;
}

[dir="ltr"] .group-item :deep(.v-list-item__append) {
    margin-inline-start: 0;
    margin-inline-end: 16px;
}

/* Group Chip */
.group-chip {
    font-size: 0.75rem;
}

/* Permission Checkbox RTL */
[dir="rtl"] .permission-checkbox-item :deep(.v-checkbox) {
    flex-direction: row-reverse;
}

[dir="rtl"] .permission-checkbox-item :deep(.v-selection-control__wrapper) {
    margin-inline-start: 8px;
    margin-inline-end: 0;
}

[dir="ltr"] .permission-checkbox-item :deep(.v-selection-control__wrapper) {
    margin-inline-end: 8px;
    margin-inline-start: 0;
}

/* Permission Label */
.permission-label {
    flex: 1;
}

[dir="rtl"] .permission-label {
    text-align: right;
}

[dir="ltr"] .permission-label {
    text-align: left;
}

/* Group Actions */
[dir="rtl"] .group-actions {
    text-align: right;
}

[dir="ltr"] .group-actions {
    text-align: left;
}

/* Card Actions RTL */
[dir="rtl"] .v-card-actions {
    flex-direction: row-reverse;
}

/* List Group RTL */
[dir="rtl"] :deep(.v-list-group__items) {
    padding-inline-start: 0;
    padding-inline-end: 16px;
}

[dir="ltr"] :deep(.v-list-group__items) {
    padding-inline-start: 16px;
    padding-inline-end: 0;
}

/* Smooth Scroll */
.v-card-text {
    scroll-behavior: smooth;
}
</style>