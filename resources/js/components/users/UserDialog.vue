<!-- resources/js/components/users/UserDialog.vue -->
<template>
    <!-- 
        Dialog Component
        
        v-model: للتحكم في إظهار/إخفاء النموذج
        persistent: لمنع الإغلاق بالضغط خارج النموذج
        max-width: أقصى عرض للنموذج
    -->
    <v-dialog
        :model-value="modelValue"
        @update:model-value="$emit('update:modelValue', $event)"
        max-width="600px"
        persistent
    >
        <v-card>
            <!-- Header -->
            <v-card-title class="d-flex align-center justify-space-between">
                <span class="text-h5">
                    {{ isEditMode ? 'تعديل مستخدم' : 'إضافة مستخدم جديد' }}
                </span>
                <v-btn
                    icon="mdi-close"
                    variant="text"
                    @click="closeDialog"
                ></v-btn>
            </v-card-title>

            <v-divider></v-divider>

            <!-- Form -->
            <v-card-text class="pt-6">
                <v-form ref="formRef" @submit.prevent="handleSubmit">
                    <!-- الاسم -->
                    <v-text-field
                        v-model="form.name"
                        label="الاسم الكامل *"
                        prepend-inner-icon="mdi-account"
                        :rules="nameRules"
                        :error-messages="errors.name"
                        variant="outlined"
                        required
                    ></v-text-field>

                    <!-- البريد الإلكتروني -->
                    <v-text-field
                        v-model="form.email"
                        label="البريد الإلكتروني *"
                        prepend-inner-icon="mdi-email"
                        type="email"
                        :rules="emailRules"
                        :error-messages="errors.email"
                        variant="outlined"
                        required
                    ></v-text-field>

                    <!-- كلمة المرور (للإضافة فقط أو اختيارية للتعديل) -->
                    <v-text-field
                        v-model="form.password"
                        :label="isEditMode ? 'كلمة المرور الجديدة (اختياري)' : 'كلمة المرور *'"
                        prepend-inner-icon="mdi-lock"
                        :type="showPassword ? 'text' : 'password'"
                        :append-inner-icon="showPassword ? 'mdi-eye' : 'mdi-eye-off'"
                        @click:append-inner="showPassword = !showPassword"
                        :rules="passwordRules"
                        :error-messages="errors.password"
                        variant="outlined"
                        :required="!isEditMode"
                    ></v-text-field>

                    <!-- تأكيد كلمة المرور -->
                    <v-text-field
                        v-if="form.password"
                        v-model="form.password_confirmation"
                        label="تأكيد كلمة المرور *"
                        prepend-inner-icon="mdi-lock-check"
                        :type="showPasswordConfirmation ? 'text' : 'password'"
                        :append-inner-icon="showPasswordConfirmation ? 'mdi-eye' : 'mdi-eye-off'"
                        @click:append-inner="showPasswordConfirmation = !showPasswordConfirmation"
                        :rules="passwordConfirmationRules"
                        variant="outlined"
                        required
                    ></v-text-field>

                    <!-- الأدوار -->
                    <v-select
                        v-model="form.roles"
                        :items="roles"
                        item-title="name"
                        item-value="id"
                        label="الأدوار"
                        prepend-inner-icon="mdi-shield-account"
                        :error-messages="errors.roles"
                        variant="outlined"
                        multiple
                        chips
                        closable-chips
                    >
                        <!-- عرض عدد الأدوار المحددة -->
                        <template v-slot:selection="{ item, index }">
                            <v-chip v-if="index < 2" size="small">
                                {{ item.title }}
                            </v-chip>
                            <span
                                v-if="index === 2"
                                class="text-caption text-medium-emphasis"
                            >
                                (+{{ form.roles.length - 2 }} أخرى)
                            </span>
                        </template>
                    </v-select>

                    <!-- رسالة خطأ عامة -->
                    <v-alert
                        v-if="generalError"
                        type="error"
                        variant="tonal"
                        class="mb-4"
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
import { useUserStore } from '@/stores/user';

/**
 * Props
 * 
 * modelValue: حالة إظهار/إخفاء النموذج
 * user: المستخدم المراد تعديله (null للإضافة)
 * roles: قائمة الأدوار المتاحة
 */
const props = defineProps({
    modelValue: {
        type: Boolean,
        required: true,
    },
    user: {
        type: Object,
        default: null,
    },
    roles: {
        type: Array,
        default: () => [],
    },
});

/**
 * Emits
 * 
 * update:modelValue: لتحديث حالة النموذج
 * saved: عند حفظ المستخدم بنجاح
 */
const emit = defineEmits(['update:modelValue', 'saved']);

// ====================================
// Store
// ====================================
const userStore = useUserStore();

// ====================================
// Reactive Data
// ====================================
const formRef = ref(null);
const loading = ref(false);
const showPassword = ref(false);
const showPasswordConfirmation = ref(false);
const generalError = ref('');

// النموذج
const form = ref({
    name: '',
    email: '',
    password: '',
    password_confirmation: '',
    roles: [],
});

// أخطاء الحقول
const errors = ref({
    name: '',
    email: '',
    password: '',
    roles: '',
});

// ====================================
// Computed
// ====================================

/**
 * هل النموذج في وضع التعديل؟
 */
const isEditMode = computed(() => !!props.user?.id);

/**
 * قواعد التحقق - الاسم
 */
const nameRules = [
    v => !!v || 'الاسم مطلوب',
    v => (v && v.length >= 3) || 'الاسم يجب أن يكون 3 أحرف على الأقل',
];

/**
 * قواعد التحقق - البريد الإلكتروني
 */
const emailRules = [
    v => !!v || 'البريد الإلكتروني مطلوب',
    v => /.+@.+\..+/.test(v) || 'يجب إدخال بريد إلكتروني صحيح',
];

/**
 * قواعد التحقق - كلمة المرور
 */
const passwordRules = computed(() => {
    // في وضع التعديل، كلمة المرور اختيارية
    if (isEditMode.value) {
        return [
            v => !v || v.length >= 8 || 'كلمة المرور يجب أن تكون 8 أحرف على الأقل',
        ];
    }
    // في وضع الإضافة، كلمة المرور إجبارية
    return [
        v => !!v || 'كلمة المرور مطلوبة',
        v => v.length >= 8 || 'كلمة المرور يجب أن تكون 8 أحرف على الأقل',
    ];
});

/**
 * قواعد التحقق - تأكيد كلمة المرور
 */
const passwordConfirmationRules = [
    v => !!v || 'تأكيد كلمة المرور مطلوب',
    v => v === form.value.password || 'كلمة المرور غير متطابقة',
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
        email: '',
        password: '',
        password_confirmation: '',
        roles: [],
    };
    
    errors.value = {
        name: '',
        email: '',
        password: '',
        roles: '',
    };
    
    generalError.value = '';
    
    if (formRef.value) {
        formRef.value.resetValidation();
    }
}

/**
 * ملء النموذج ببيانات المستخدم (للتعديل)
 */
function fillForm() {
    if (props.user) {
        form.value.name = props.user.name;
        form.value.email = props.user.email;
        form.value.password = '';
        form.value.password_confirmation = '';
        form.value.roles = props.user.roles?.map(role => role.id) || [];
    }
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
        email: '',
        password: '',
        roles: '',
    };

    try {
        let result;
        
        // تجهيز البيانات للإرسال
        const data = {
            name: form.value.name,
            email: form.value.email,
            roles: form.value.roles,
        };
        
        // إضافة كلمة المرور فقط إذا تم إدخالها
        if (form.value.password) {
            data.password = form.value.password;
        }
        
        // إضافة أو تعديل
        if (isEditMode.value) {
            result = await userStore.updateUser(props.user.id, data);
        } else {
            result = await userStore.createUser(data);
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
 * مراقبة تغيير المستخدم المحدد
 * عند فتح النموذج للتعديل، يتم ملء الحقول
 */
watch(() => props.user, (newUser) => {
    if (newUser) {
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
        if (props.user) {
            fillForm();
        } else {
            resetForm();
        }
    }
});
</script>

<style scoped>
/* Styles مخصصة إذا لزم الأمر */
</style>