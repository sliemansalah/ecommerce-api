<!-- resources/js/components/roles/RoleDialog.vue -->
<template>
    <v-dialog
        :model-value="modelValue"
        @update:model-value="$emit('update:modelValue', $event)"
        max-width="800px"
        persistent
        scrollable
    >
        <v-card>
            <!-- Header -->
            <v-card-title class="d-flex align-center justify-space-between">
                <span class="text-h5">
                    {{ isEditMode ? 'تعديل دور' : 'إضافة دور جديد' }}
                </span>
                <v-btn
                    icon="mdi-close"
                    variant="text"
                    @click="closeDialog"
                ></v-btn>
            </v-card-title>

            <v-divider></v-divider>

            <!-- Form -->
            <v-card-text class="pt-6" style="max-height: 600px;">
                <v-form ref="formRef" @submit.prevent="handleSubmit">
                    <!-- اسم الدور -->
                    <v-text-field
                        v-model="form.name"
                        label="اسم الدور *"
                        prepend-inner-icon="mdi-shield-account"
                        :rules="nameRules"
                        :error-messages="errors.name"
                        variant="outlined"
                        required
                        class="mb-4"
                    ></v-text-field>

                    <v-divider class="mb-4"></v-divider>

                    <!-- الصلاحيات -->
                    <h3 class="text-h6 mb-4">
                        <v-icon icon="mdi-key" class="me-2"></v-icon>
                        اختر الصلاحيات
                    </h3>

                    <!-- Select All / Deselect All -->
                    <div class="d-flex ga-2 mb-4">
                        <v-btn
                            size="small"
                            variant="tonal"
                            color="primary"
                            prepend-icon="mdi-check-all"
                            @click="selectAllPermissions"
                        >
                            تحديد الكل
                        </v-btn>
                        <v-btn
                            size="small"
                            variant="tonal"
                            color="grey"
                            prepend-icon="mdi-close-box-multiple"
                            @click="deselectAllPermissions"
                        >
                            إلغاء الكل
                        </v-btn>
                    </div>

                    <!-- Permissions بحسب المجموعات -->
                    <v-expansion-panels
                        v-if="Object.keys(permissions).length > 0"
                        multiple
                        variant="accordion"
                    >
                        <v-expansion-panel
                            v-for="(perms, group) in permissions"
                            :key="group"
                        >
                            <v-expansion-panel-title>
                                <div class="d-flex align-center">
                                    <v-icon :icon="getGroupIcon(group)" class="me-3"></v-icon>
                                    <span class="font-weight-bold">{{ translateGroup(group) }}</span>
                                    <v-chip
                                        size="small"
                                        color="primary"
                                        variant="tonal"
                                        class="ms-3"
                                    >
                                        {{ getSelectedInGroup(perms) }}/{{ perms.length }}
                                    </v-chip>
                                </div>
                            </v-expansion-panel-title>

                            <v-expansion-panel-text>
                                <v-row>
                                    <v-col
                                        v-for="permission in perms"
                                        :key="permission.id"
                                        cols="12"
                                        sm="6"
                                    >
                                        <v-checkbox
                                            v-model="form.permissions"
                                            :value="permission.id"
                                            :label="formatPermissionName(permission.name)"
                                            color="primary"
                                            hide-details
                                        ></v-checkbox>
                                    </v-col>
                                </v-row>
                            </v-expansion-panel-text>
                        </v-expansion-panel>
                    </v-expansion-panels>

                    <p v-else class="text-center text-medium-emphasis mt-4">
                        لا توجد صلاحيات متاحة
                    </p>

                    <!-- رسالة خطأ عامة -->
                    <v-alert
                        v-if="generalError"
                        type="error"
                        variant="tonal"
                        class="mt-4"
                        closable
                        @click:close="generalError = ''"
                    >
                        {{ generalError }}
                    </v-alert>
                </v-form>
            </v-card-text>

            <v-divider></v-divider>

            <!-- Actions -->
            <v-card-actions class="pa-4">
                <v-chip
                    v-if="form.permissions.length > 0"
                    color="primary"
                    variant="tonal"
                >
                    <v-icon start>mdi-check-circle</v-icon>
                    {{ form.permissions.length }} صلاحية محددة
                </v-chip>
                <v-spacer></v-spacer>
                <v-btn
                    variant="text"
                    @click="closeDialog"
                    :disabled="loading"
                >
                    إلغاء
                </v-btn>
                <v-btn
                    color="primary"
                    variant="elevated"
                    @click="handleSubmit"
                    :loading="loading"
                >
                    {{ isEditMode ? 'حفظ التعديلات' : 'إضافة' }}
                </v-btn>
            </v-card-actions>
        </v-card>
    </v-dialog>
</template>

<script setup>
import { ref, computed, watch } from 'vue';
import { useRoleStore } from '@/stores/role';

/**
 * Props
 */
const props = defineProps({
    modelValue: {
        type: Boolean,
        required: true,
    },
    role: {
        type: Object,
        default: null,
    },
    permissions: {
        type: Object,
        default: () => ({}),
    },
});

/**
 * Emits
 */
const emit = defineEmits(['update:modelValue', 'saved']);

// ====================================
// Store
// ====================================
const roleStore = useRoleStore();

// ====================================
// Reactive Data
// ====================================
const formRef = ref(null);
const loading = ref(false);
const generalError = ref('');

// النموذج
const form = ref({
    name: '',
    permissions: [],
});

// أخطاء الحقول
const errors = ref({
    name: '',
    permissions: '',
});

// ====================================
// Computed
// ====================================

/**
 * هل النموذج في وضع التعديل؟
 */
const isEditMode = computed(() => !!props.role?.id);

/**
 * قواعد التحقق - اسم الدور
 */
const nameRules = [
    v => !!v || 'اسم الدور مطلوب',
    v => (v && v.length >= 3) || 'اسم الدور يجب أن يكون 3 أحرف على الأقل',
];

// ====================================
// Methods
// ====================================

/**
 * إعادة تعيين النموذج
 */
function resetForm() {
    form.value = {
        name: '',
        permissions: [],
    };
    
    errors.value = {
        name: '',
        permissions: '',
    };
    
    generalError.value = '';
    
    if (formRef.value) {
        formRef.value.resetValidation();
    }
}

/**
 * ملء النموذج ببيانات الدور (للتعديل)
 */
function fillForm() {
    if (props.role) {
        form.value.name = props.role.name;
        form.value.permissions = props.role.permissions?.map(p => p.id) || [];
    }
}

/**
 * تحديد جميع الصلاحيات
 */
function selectAllPermissions() {
    const allIds = [];
    Object.values(props.permissions).forEach(perms => {
        perms.forEach(p => allIds.push(p.id));
    });
    form.value.permissions = allIds;
}

/**
 * إلغاء تحديد جميع الصلاحيات
 */
function deselectAllPermissions() {
    form.value.permissions = [];
}

/**
 * عدد الصلاحيات المحددة في مجموعة
 */
function getSelectedInGroup(perms) {
    return perms.filter(p => form.value.permissions.includes(p.id)).length;
}

/**
 * ترجمة اسم المجموعة
 */
function translateGroup(group) {
    const translations = {
        'products': 'المنتجات',
        'categories': 'الفئات',
        'orders': 'الطلبات',
        'users': 'المستخدمين',
        'roles': 'الأدوار',
        'permissions': 'الصلاحيات',
    };
    return translations[group] || group;
}

/**
 * أيقونة المجموعة
 */
function getGroupIcon(group) {
    const icons = {
        'products': 'mdi-package-variant-closed',
        'categories': 'mdi-folder-multiple',
        'orders': 'mdi-cart',
        'users': 'mdi-account-multiple',
        'roles': 'mdi-shield-account',
        'permissions': 'mdi-key',
    };
    return icons[group] || 'mdi-folder';
}

/**
 * تنسيق اسم الصلاحية للعرض
 */
function formatPermissionName(permissionName) {
    const translations = {
        'products.view': 'عرض',
        'products.create': 'إضافة',
        'products.edit': 'تعديل',
        'products.delete': 'حذف',
        'categories.view': 'عرض',
        'categories.create': 'إضافة',
        'categories.edit': 'تعديل',
        'categories.delete': 'حذف',
        'orders.view': 'عرض',
        'orders.create': 'إضافة',
        'orders.edit': 'تعديل',
        'orders.delete': 'حذف',
        'users.view': 'عرض',
        'users.create': 'إضافة',
        'users.edit': 'تعديل',
        'users.delete': 'حذف',
        'roles.view': 'عرض',
        'roles.create': 'إضافة',
        'roles.edit': 'تعديل',
        'roles.delete': 'حذف',
        'permissions.view': 'عرض',
        'permissions.create': 'إضافة',
        'permissions.edit': 'تعديل',
        'permissions.delete': 'حذف',
    };

    return translations[permissionName] || permissionName;
}

/**
 * معالجة إرسال النموذج
 */
async function handleSubmit() {
    // التحقق من صحة النموذج
    const { valid } = await formRef.value.validate();
    
    if (!valid) return;

    loading.value = true;
    generalError.value = '';
    
    // مسح الأخطاء السابقة
    errors.value = {
        name: '',
        permissions: '',
    };

    try {
        const data = {
            name: form.value.name,
            permissions: form.value.permissions,
        };
        
        let result;
        
        // إضافة أو تعديل
        if (isEditMode.value) {
            result = await roleStore.updateRole(props.role.id, data);
        } else {
            result = await roleStore.createRole(data);
        }

        if (result.success) {
            // النجاح
            emit('saved');
            closeDialog();
        } else {
            // فشل
            generalError.value = result.message || 'فشلت العملية';
            
            // عرض أخطاء الحقول
            if (result.errors) {
                Object.keys(result.errors).forEach(key => {
                    if (errors.value.hasOwnProperty(key)) {
                        errors.value[key] = result.errors[key][0];
                    }
                });
            }
        }
    } catch (error) {
        generalError.value = 'حدث خطأ غير متوقع';
    } finally {
        loading.value = false;
    }
}

/**
 * إغلاق النموذج
 */
function closeDialog() {
    resetForm();
    emit('update:modelValue', false);
}

// ====================================
// Watchers
// ====================================

/**
 * مراقبة تغيير الدور المحدد
 */
watch(() => props.role, (newRole) => {
    if (newRole) {
        fillForm();
    } else {
        resetForm();
    }
}, { immediate: true });

/**
 * مراقبة فتح/إغلاق النموذج
 */
watch(() => props.modelValue, (newValue) => {
    if (newValue) {
        if (props.role) {
            fillForm();
        } else {
            resetForm();
        }
    }
});
</script>

<style scoped>
/* Styles مخصصة */
</style>