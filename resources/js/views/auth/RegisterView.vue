<!-- resources/js/views/auth/RegisterView.vue -->
<template>
    <v-container fluid class="fill-height register-container">
        <v-row align="center" justify="center">
            <v-col cols="12" sm="8" md="6" lg="5">
                <v-card elevation="8" class="rounded-lg">
                    <v-card-title class="text-center py-6">
                        <div class="d-flex flex-column align-center">
                            <v-icon icon="mdi-store" size="48" color="primary" class="mb-3"></v-icon>
                            <h2 class="text-h4 font-weight-bold">إنشاء حساب جديد</h2>
                            <p class="text-subtitle-1 text-medium-emphasis mt-2">
                                انضم إلى نظام التجارة الإلكترونية
                            </p>
                        </div>
                    </v-card-title>

                    <v-card-text class="pa-6">
                        <v-form ref="registerForm" @submit.prevent="handleRegister">
                            <!-- الاسم -->
                            <v-text-field
                                v-model="form.name"
                                :rules="nameRules"
                                label="الاسم الكامل"
                                prepend-inner-icon="mdi-account"
                                placeholder="أدخل اسمك الكامل"
                                :error-messages="errors.name"
                                required
                            ></v-text-field>

                            <!-- البريد الإلكتروني -->
                            <v-text-field
                                v-model="form.email"
                                :rules="emailRules"
                                label="البريد الإلكتروني"
                                prepend-inner-icon="mdi-email"
                                type="email"
                                placeholder="example@domain.com"
                                :error-messages="errors.email"
                                required
                            ></v-text-field>

                            <!-- كلمة المرور -->
                            <v-text-field
                                v-model="form.password"
                                :rules="passwordRules"
                                label="كلمة المرور"
                                prepend-inner-icon="mdi-lock"
                                :type="showPassword ? 'text' : 'password'"
                                :append-inner-icon="showPassword ? 'mdi-eye' : 'mdi-eye-off'"
                                @click:append-inner="showPassword = !showPassword"
                                placeholder="••••••••"
                                :error-messages="errors.password"
                                required
                            ></v-text-field>

                            <!-- تأكيد كلمة المرور -->
                            <v-text-field
                                v-model="form.password_confirmation"
                                :rules="passwordConfirmationRules"
                                label="تأكيد كلمة المرور"
                                prepend-inner-icon="mdi-lock-check"
                                :type="showPasswordConfirmation ? 'text' : 'password'"
                                :append-inner-icon="showPasswordConfirmation ? 'mdi-eye' : 'mdi-eye-off'"
                                @click:append-inner="showPasswordConfirmation = !showPasswordConfirmation"
                                placeholder="••••••••"
                                required
                            ></v-text-field>

                            <!-- رسالة الخطأ العامة -->
                            <v-alert
                                v-if="errorMessage"
                                type="error"
                                variant="tonal"
                                class="mt-4"
                                closable
                                @click:close="errorMessage = ''"
                            >
                                {{ errorMessage }}
                            </v-alert>

                            <!-- زر التسجيل -->
                            <v-btn
                                type="submit"
                                color="primary"
                                size="large"
                                block
                                :loading="loading"
                                class="mt-6"
                            >
                                <v-icon start>mdi-account-plus</v-icon>
                                إنشاء حساب
                            </v-btn>
                        </v-form>

                        <!-- رابط تسجيل الدخول -->
                        <div class="text-center mt-6">
                            <p class="text-body-2 text-medium-emphasis">
                                لديك حساب بالفعل؟
                                <router-link 
                                    :to="{ name: 'login' }" 
                                    class="text-primary text-decoration-none font-weight-bold"
                                >
                                    سجل دخول
                                </router-link>
                            </p>
                        </div>
                    </v-card-text>
                </v-card>
            </v-col>
        </v-row>
    </v-container>
</template>

<script setup>
import { ref, reactive, computed } from 'vue';
import { useRouter } from 'vue-router';
import { useAuthStore } from '@/stores/auth';

const router = useRouter();
const authStore = useAuthStore();

const registerForm = ref(null);
const showPassword = ref(false);
const showPasswordConfirmation = ref(false);
const loading = ref(false);
const errorMessage = ref('');

const form = reactive({
    name: '',
    email: '',
    password: '',
    password_confirmation: '',
});

const errors = reactive({
    name: '',
    email: '',
    password: '',
});

// قواعد التحقق
const nameRules = [
    v => !!v || 'الاسم مطلوب',
    v => v.length >= 3 || 'الاسم يجب أن يكون 3 أحرف على الأقل',
];

const emailRules = [
    v => !!v || 'البريد الإلكتروني مطلوب',
    v => /.+@.+\..+/.test(v) || 'يجب إدخال بريد إلكتروني صحيح',
];

const passwordRules = [
    v => !!v || 'كلمة المرور مطلوبة',
    v => v.length >= 8 || 'كلمة المرور يجب أن تكون 8 أحرف على الأقل',
];

const passwordConfirmationRules = [
    v => !!v || 'تأكيد كلمة المرور مطلوب',
    v => v === form.password || 'كلمة المرور غير متطابقة',
];

// معالجة التسجيل
const handleRegister = async () => {
    // التحقق من صحة النموذج
    const { valid } = await registerForm.value.validate();
    
    if (!valid) return;

    loading.value = true;
    errorMessage.value = '';
    
    // مسح الأخطاء السابقة
    errors.name = '';
    errors.email = '';
    errors.password = '';

    try {
        const result = await authStore.register({
            name: form.name,
            email: form.email,
            password: form.password,
            password_confirmation: form.password_confirmation,
        });

        if (result.success) {
            // إعادة التوجيه للوحة التحكم
            router.push('/dashboard');
        } else {
            errorMessage.value = result.message || 'فشل إنشاء الحساب';
            
            // عرض أخطاء الحقول
            if (result.errors) {
                Object.keys(result.errors).forEach(key => {
                    errors[key] = result.errors[key][0];
                });
            }
        }
    } catch (error) {
        if (error.response?.data?.errors) {
            // أخطاء التحقق
            Object.keys(error.response.data.errors).forEach(key => {
                errors[key] = error.response.data.errors[key][0];
            });
        } else {
            errorMessage.value = 'حدث خطأ غير متوقع. يرجى المحاولة مرة أخرى.';
        }
    } finally {
        loading.value = false;
    }
};
</script>

<style scoped>
.register-container {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    min-height: 100vh;
}

.v-card {
    overflow: hidden;
}

.v-card-title {
    background: linear-gradient(to bottom, rgba(var(--v-theme-primary), 0.05), transparent);
}

a {
    transition: all 0.3s ease;
}

a:hover {
    opacity: 0.8;
}
</style>
