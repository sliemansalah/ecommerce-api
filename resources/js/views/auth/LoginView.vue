<!-- resources/js/views/auth/LoginView.vue -->
<template>
    <v-container fluid class="fill-height login-container">
        <v-row align="center" justify="center">
            <v-col cols="12" sm="8" md="6" lg="4">
                <v-card elevation="8" class="rounded-lg">
                    <v-card-title class="text-center py-6">
                        <div class="d-flex flex-column align-center">
                            <v-icon icon="mdi-store" size="48" color="primary" class="mb-3"></v-icon>
                            <h2 class="text-h4 font-weight-bold">تسجيل الدخول</h2>
                            <p class="text-subtitle-1 text-medium-emphasis mt-2">
                                مرحباً بك في نظام التجارة الإلكترونية
                            </p>
                        </div>
                    </v-card-title>

                    <v-card-text class="pa-6">
                        <v-form ref="loginForm" @submit.prevent="handleLogin">
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

                            <!-- تذكرني -->
                            <v-checkbox
                                v-model="form.remember"
                                label="تذكرني"
                                color="primary"
                                hide-details
                            ></v-checkbox>

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

                            <!-- زر تسجيل الدخول -->
                            <v-btn
                                type="submit"
                                color="primary"
                                size="large"
                                block
                                :loading="loading"
                                class="mt-6"
                            >
                                <v-icon start>mdi-login</v-icon>
                                تسجيل الدخول
                            </v-btn>
                        </v-form>

                        <!-- رابط التسجيل -->
                        <div class="text-center mt-6">
                            <p class="text-body-2 text-medium-emphasis">
                                ليس لديك حساب؟
                                <router-link 
                                    :to="{ name: 'register' }" 
                                    class="text-primary text-decoration-none font-weight-bold"
                                >
                                    سجل الآن
                                </router-link>
                            </p>
                        </div>

                        <!-- نسيت كلمة المرور -->
                        <div class="text-center mt-2">
                            <a href="#" class="text-caption text-decoration-none">
                                نسيت كلمة المرور؟
                            </a>
                        </div>
                    </v-card-text>
                </v-card>

                <!-- معلومات تجريبية -->
                <v-card elevation="2" class="mt-4 rounded-lg">
                    <v-card-text>
                        <div class="text-center">
                            <p class="text-subtitle-2 font-weight-bold mb-2">
                                <v-icon icon="mdi-information" size="small" class="ml-1"></v-icon>
                                بيانات تجريبية
                            </p>
                            <v-divider class="mb-3"></v-divider>
                            <div class="text-caption text-right">
                                <p><strong>الإيميل:</strong> admin@test.com</p>
                                <p><strong>كلمة المرور:</strong> 12345678</p>
                            </div>
                        </div>
                    </v-card-text>
                </v-card>
            </v-col>
        </v-row>
    </v-container>
</template>

<script setup>
import { ref, reactive } from 'vue';
import { useRouter, useRoute } from 'vue-router';
import { useAuthStore } from '@/stores/auth';

const router = useRouter();
const route = useRoute();
const authStore = useAuthStore();

const loginForm = ref(null);
const showPassword = ref(false);
const loading = ref(false);
const errorMessage = ref('');

const form = reactive({
    email: '',
    password: '',
    remember: false,
});

const errors = reactive({
    email: '',
    password: '',
});

// قواعد التحقق
const emailRules = [
    v => !!v || 'البريد الإلكتروني مطلوب',
    v => /.+@.+\..+/.test(v) || 'يجب إدخال بريد إلكتروني صحيح',
];

const passwordRules = [
    v => !!v || 'كلمة المرور مطلوبة',
    v => v.length >= 8 || 'كلمة المرور يجب أن تكون 8 أحرف على الأقل',
];

// معالجة تسجيل الدخول
const handleLogin = async () => {
    // التحقق من صحة النموذج
    const { valid } = await loginForm.value.validate();
    
    if (!valid) return;

    loading.value = true;
    errorMessage.value = '';
    
    // مسح الأخطاء السابقة
    errors.email = '';
    errors.password = '';

    try {
        const result = await authStore.login({
            email: form.email,
            password: form.password,
        });

        if (result.success) {
            // إعادة التوجيه
            const redirect = route.query.redirect || '/dashboard';
            router.push(redirect);
        } else {
            errorMessage.value = result.message || 'فشل تسجيل الدخول';
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
.login-container {
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
