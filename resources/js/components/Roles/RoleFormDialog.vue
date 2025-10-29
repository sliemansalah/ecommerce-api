<template>
    <v-dialog
        :model-value="modelValue"
        @update:model-value="$emit('update:modelValue', $event)"
        max-width="800"
        persistent
        scrollable
    >
        <v-card>
            <v-card-title class="bg-primary">
                <span class="text-h5 text-white">
                    <v-icon color="white" class="me-2">
                        {{ isEdit ? 'mdi-shield-edit' : 'mdi-shield-plus' }}
                    </v-icon>
                    {{ isEdit ? t('roles.editRole') : t('roles.addNew') }}
                </span>
            </v-card-title>

            <v-divider></v-divider>

            <v-card-text class="pa-6">
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
                                hint="مثال: sales-manager"
                                persistent-hint
                                :dir="currentLocale.dir"
                            ></v-text-field>
                        </v-col>

                        <!-- الصلاحيات -->
                        <v-col cols="12">
                            <h3 class="text-h6 mb-3">
                                <v-icon class="me-2">mdi-key</v-icon>
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
                                تم اختيار {{ formData.permissions.length }} من أصل {{ permissions.length }} صلاحية
                            </v-alert>

                            <!-- الصلاحيات مجمعة -->
                            <v-card variant="outlined">
                                <v-list>
                                    <v-list-group
                                        v-for="(group, groupName) in groupedPermissions"
                                        :key="groupName"
                                    >
                                        <template v-slot:activator="{ props }">
                                            <v-list-item v-bind="props">
                                                <template v-slot:prepend>
                                                    <v-icon :color="getGroupColor(groupName)">
                                                        {{ getGroupIcon(groupName) }}
                                                    </v-icon>
                                                </template>
                                                <v-list-item-title class="font-weight-medium">
                                                    {{ getGroupDisplayName(groupName) }}
                                                </v-list-item-title>
                                                <template v-slot:append>
                                                    <v-chip size="small" variant="tonal">
                                                        {{ selectedPermissionsInGroup(group).length }}/{{ group.length }}
                                                    </v-chip>
                                                </template>
                                            </v-list-item>
                                        </template>

                                        <v-list-item
                                            v-for="permission in group"
                                            :key="permission.id"
                                            density="compact"
                                        >
                                            <v-checkbox
                                                v-model="formData.permissions"
                                                :value="permission.name"
                                                :label="permission.display_name"
                                                color="primary"
                                                density="compact"
                                                hide-details
                                            ></v-checkbox>
                                        </v-list-item>

                                        <v-list-item>
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
    permissions: Array,
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

// تجميع الصلاحيات
const groupedPermissions = computed(() => {
    if (!props.permissions) return {};
    
    return props.permissions.reduce((groups, permission) => {
        const group = permission.group || 'أخرى';
        if (!groups[group]) {
            groups[group] = [];
        }
        groups[group].push(permission);
        return groups;
    }, {});
});

// القواعد
const nameRules = [
    v => !!v || t('validation.required'),
    v => /^[a-z0-9-]+$/.test(v) || 'يجب أن يحتوي على أحرف إنجليزية صغيرة وأرقام وشرطات فقط',
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
    emit('update:modelValue', false);
};

// مراقبة تغيير الدور
watch(() => props.role, (newRole) => {
    if (newRole) {
        formData.value = {
            name: newRole.name,
            permissions: newRole.permissions?.map(p => p.name) || [],
        };
    }
}, { immediate: true });
</script>