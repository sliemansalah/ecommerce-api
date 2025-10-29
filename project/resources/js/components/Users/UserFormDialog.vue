<template>
    <v-dialog
        :model-value="modelValue"
        @update:model-value="$emit('update:modelValue', $event)"
        max-width="700"
        persistent
        scrollable
    >
        <v-card>
            <v-card-title class="bg-primary">
                <div class="d-flex align-center">
                    <v-icon color="white" :class="isRTL ? 'ms-2' : 'me-2'">
                        {{ isEdit ? 'mdi-account-edit' : 'mdi-account-plus' }}
                    </v-icon>
                    <span class="text-h5 text-white">
                        {{ isEdit ? t('users.editUser') : t('users.addNew') }}
                    </span>
                </div>
            </v-card-title>

            <v-divider></v-divider>

            <v-card-text class="pa-6">
                <v-form ref="form" @submit.prevent="handleSubmit">
                    <v-row>
                        <!-- الاسم -->
                        <v-col cols="12">
                            <v-text-field
                                v-model="formData.name"
                                :label="t('users.name') + ' *'"
                                :rules="nameRules"
                                prepend-inner-icon="mdi-account"
                                variant="outlined"
                                density="comfortable"
                            ></v-text-field>
                        </v-col>

                        <!-- البريد الإلكتروني -->
                        <v-col cols="12" md="6">
                            <v-text-field
                                v-model="formData.email"
                                :label="t('users.email') + ' *'"
                                :rules="emailRules"
                                type="email"
                                prepend-inner-icon="mdi-email"
                                variant="outlined"
                                density="comfortable"
                            ></v-text-field>
                        </v-col>

                        <!-- رقم الهاتف -->
                        <v-col cols="12" md="6">
                            <v-text-field
                                v-model="formData.phone"
                                :label="t('users.phone')"
                                prepend-inner-icon="mdi-phone"
                                variant="outlined"
                                density="comfortable"
                            ></v-text-field>
                        </v-col>

                        <!-- كلمة المرور -->
                        <v-col cols="12" md="6">
                            <v-text-field
                                v-model="formData.password"
                                :label="(isEdit ? t('users.newPassword') : t('users.password')) + (isEdit ? '' : ' *')"
                                :rules="isEdit ? [] : passwordRules"
                                :type="showPassword ? 'text' : 'password'"
                                prepend-inner-icon="mdi-lock"
                                :append-inner-icon="showPassword ? 'mdi-eye-off' : 'mdi-eye'"
                                @click:append-inner="showPassword = !showPassword"
                                variant="outlined"
                                density="comfortable"
                                :hint="isEdit ? t('users.leaveBlankToKeep') : ''"
                                persistent-hint
                            ></v-text-field>
                        </v-col>

                        <!-- تأكيد كلمة المرور -->
                        <v-col cols="12" md="6">
                            <v-text-field
                                v-model="formData.password_confirmation"
                                :label="t('users.confirmPassword') + (isEdit ? '' : ' *')"
                                :rules="isEdit ? [] : confirmPasswordRules"
                                :type="showPassword ? 'text' : 'password'"
                                prepend-inner-icon="mdi-lock-check"
                                variant="outlined"
                                density="comfortable"
                            ></v-text-field>
                        </v-col>

                        <!-- الأدوار -->
                        <v-col cols="12">
                            <v-select
                                v-model="formData.roles"
                                :label="t('users.roles') + ' *'"
                                :items="roles"
                                item-title="display_name"
                                item-value="name"
                                :rules="rolesRules"
                                prepend-inner-icon="mdi-shield-account"
                                variant="outlined"
                                density="comfortable"
                                multiple
                                chips
                                closable-chips
                            >
                                <template v-slot:chip="{ item, props }">
                                    <v-chip
                                        v-bind="props"
                                        :color="getRoleColor(item.value)"
                                        :class="isRTL ? 'ms-1' : 'me-1'"
                                    >
                                        {{ item.title }}
                                    </v-chip>
                                </template>
                            </v-select>
                        </v-col>

                        <!-- الحالة -->
                        <v-col cols="12">
                            <div class="d-flex align-center" :class="isRTL ? 'flex-row-reverse' : ''">
                                <v-switch
                                    v-model="formData.is_active"
                                    color="success"
                                    hide-details
                                    class="flex-shrink-0"
                                ></v-switch>
                                <span :class="isRTL ? 'me-3' : 'ms-3'" class="text-body-1">
                                    {{ t('users.isActive') }}
                                </span>
                            </div>
                        </v-col>
                    </v-row>
                </v-form>
            </v-card-text>

            <v-divider></v-divider>

            <v-card-actions class="pa-4">
                <v-spacer></v-spacer>
                <v-btn
                    @click="close"
                    variant="text"
                >
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
import { useUsers } from '../../composables/useUsers';
import { useLanguage } from '../../composables/useLanguage';

const props = defineProps({
    modelValue: Boolean,
    user: Object,
    roles: Array,
});

const emit = defineEmits(['update:modelValue', 'saved']);

const { t, currentLocale } = useLanguage();
const { createUser, updateUser, loading } = useUsers();

const form = ref(null);
const showPassword = ref(false);

const formData = ref({
    name: '',
    email: '',
    phone: '',
    password: '',
    password_confirmation: '',
    roles: [],
    is_active: true,
});

const isEdit = computed(() => !!props.user);
const isRTL = computed(() => currentLocale.value.dir === 'rtl');

// القواعد
const nameRules = [
    v => !!v || t('validation.required'),
    v => (v && v.length >= 3) || t('validation.minLength', { length: 3 }),
];

const emailRules = [
    v => !!v || t('validation.required'),
    v => /.+@.+\..+/.test(v) || t('validation.email'),
];

const passwordRules = [
    v => !!v || t('validation.required'),
    v => (v && v.length >= 6) || t('validation.minLength', { length: 6 }),
];

const confirmPasswordRules = [
    v => !!v || t('validation.required'),
    v => v === formData.value.password || t('validation.passwordMatch'),
];

const rolesRules = [
    v => (v && v.length > 0) || t('validation.required'),
];

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

// الإرسال
const handleSubmit = async () => {
    const { valid } = await form.value.validate();
    if (!valid) return;

    try {
        if (isEdit.value) {
            // إزالة الحقول الفارغة في التعديل
            const updateData = { ...formData.value };
            if (!updateData.password) {
                delete updateData.password;
                delete updateData.password_confirmation;
            }
            await updateUser(props.user.id, updateData);
        } else {
            await createUser(formData.value);
        }
        emit('saved');
        close();
    } catch (error) {
        console.error('Error saving user:', error);
    }
};

// إغلاق
const close = () => {
    form.value?.reset();
    formData.value = {
        name: '',
        email: '',
        phone: '',
        password: '',
        password_confirmation: '',
        roles: [],
        is_active: true,
    };
    emit('update:modelValue', false);
};

// مراقبة تغيير المستخدم
watch(() => props.user, (newUser) => {
    if (newUser) {
        formData.value = {
            name: newUser.name,
            email: newUser.email,
            phone: newUser.phone || '',
            password: '',
            password_confirmation: '',
            roles: newUser.roles?.map(r => r.name) || [],
            is_active: newUser.is_active !== undefined ? newUser.is_active : true,
        };
    }
}, { immediate: true });

// مراقبة إغلاق الـ Dialog
watch(() => props.modelValue, (newValue) => {
    if (!newValue) {
        // عند إغلاق الـ Dialog، امسح البيانات
        setTimeout(() => {
            form.value?.reset();
            formData.value = {
                name: '',
                email: '',
                phone: '',
                password: '',
                password_confirmation: '',
                roles: [],
                is_active: true,
            };
        }, 300);
    }
});
</script>

<style scoped>
/* RTL Support للـ Dialog */
[dir="rtl"] .v-card-title {
    text-align: right;
}

[dir="rtl"] .v-card-actions {
    flex-direction: row-reverse;
}

/* Chips في Select */
[dir="rtl"] .v-chip {
    margin-left: 0.25rem;
    margin-right: 0;
}

[dir="ltr"] .v-chip {
    margin-right: 0.25rem;
    margin-left: 0;
}

/* Switch alignment */
.flex-shrink-0 {
    flex-shrink: 0;
}
</style>