<template>
    <v-container fluid class="fill-height pa-0">
        <v-row no-gutters class="fill-height">
            <!-- الجزء الأيسر - الصورة -->
            <v-col cols="12" md="6" class="d-none d-md-flex">
                <v-img
                    src="https://images.unsplash.com/photo-1607082348824-0a96f2a4b9da?w=800"
                    cover
                    class="fill-height"
                >
                    <div class="fill-height d-flex flex-column justify-center align-center pa-12" 
                         style="background: linear-gradient(135deg, rgba(25, 118, 210, 0.9) 0%, rgba(21, 101, 192, 0.95) 100%);">
                        <v-icon size="100" color="white" class="mb-8">mdi-shopping</v-icon>
                        <h1 class="text-h2 text-white font-weight-bold mb-4 text-center">
                            {{ t('app.title') }}
                        </h1>
                        <p class="text-h6 text-white text-center opacity-90" style="max-width: 450px;">
                            منصة التجارة الإلكترونية الأفضل في المنطقة
                        </p>
                        <div class="mt-8 d-flex gap-4">
                            <v-chip color="white" variant="flat" size="large">
                                <v-icon start>mdi-security</v-icon>
                                آمن ومضمون
                            </v-chip>
                            <v-chip color="white" variant="flat" size="large">
                                <v-icon start>mdi-lightning-bolt</v-icon>
                                سريع وسهل
                            </v-chip>
                        </div>
                    </div>
                </v-img>
            </v-col>

            <!-- الجزء الأيمن - نموذج تسجيل الدخول -->
            <v-col cols="12" md="6">
                <v-container class="fill-height">
                    <v-row justify="center" align="center">
                        <v-col cols="12" sm="10" md="9" lg="7">
                            <div class="text-center mb-8">
                                <v-avatar color="primary" size="80" class="mb-6 elevation-4">
                                    <v-icon size="50" color="white">mdi-lock-outline</v-icon>
                                </v-avatar>
                                <h2 class="text-h3 font-weight-bold mb-3">
                                    {{ t('auth.login.title') }}
                                </h2>
                                <p class="text-h6 text-medium-emphasis">
                                    {{ t('auth.login.subtitle') }}
                                </p>
                            </div>

                            <v-card elevation="8" rounded="xl" class="pa-6">
                                <v-form @submit.prevent="handleLogin" ref="form">
                                    <!-- البريد الإلكتروني -->
                                    <v-text-field
                                        v-model="credentials.email"
                                        :label="t('auth.login.email')"
                                        :rules="emailRules"
                                        type="email"
                                        prepend-inner-icon="mdi-email-outline"
                                        variant="outlined"
                                        color="primary"
                                        class="mb-4"
                                        :error-messages="errors.email"
                                        density="comfortable"
                                    ></v-text-field>

                                    <!-- كلمة المرور -->
                                    <v-text-field
                                        v-model="credentials.password"
                                        :label="t('auth.login.password')"
                                        :rules="passwordRules"
                                        :type="showPassword ? 'text' : 'password'"
                                        prepend-inner-icon="mdi-lock-outline"
                                        :append-inner-icon="showPassword ? 'mdi-eye-off' : 'mdi-eye'"
                                        @click:append-inner="showPassword = !showPassword"
                                        variant="outlined"
                                        color="primary"
                                        class="mb-3"
                                        :error-messages="errors.password"
                                        density="comfortable"
                                    ></v-text-field>

                                    <!-- تذكرني و نسيت كلمة المرور -->
                                    <div class="d-flex justify-space-between align-center mb-6">
                                        <v-checkbox
                                            v-model="credentials.remember"
                                            :label="t('auth.login.remember')"
                                            color="primary"
                                            hide-details
                                            density="compact"
                                        ></v-checkbox>
                                        <v-btn
                                            variant="text"
                                            color="primary"
                                            size="small"
                                        >
                                            {{ t('auth.login.forgot') }}
                                        </v-btn>
                                    </div>

                                    <!-- رسالة خطأ عامة -->
                                    <v-alert
                                        v-if="errorMessage"
                                        type="error"
                                        variant="tonal"
                                        closable
                                        class="mb-4"
                                        @click:close="errorMessage = ''"
                                    >
                                        {{ errorMessage }}
                                    </v-alert>

                                    <!-- زر تسجيل الدخول -->
                                    <v-btn
                                        type="submit"
                                        color="primary"
                                        size="x-large"
                                        block
                                        :loading="loading"
                                        class="mb-4 text-h6"
                                        elevation="2"
                                    >
                                        <v-icon start size="large">mdi-login</v-icon>
                                        {{ t('auth.login.button') }}
                                    </v-btn>

                                    <!-- معلومات تجريبية -->
                                    <v-card variant="tonal" color="info" class="pa-4 mb-4">
                                        <div class="d-flex align-center mb-2">
                                            <v-icon color="info" class="me-2">mdi-information</v-icon>
                                            <span class="font-weight-bold">بيانات تجريبية:</span>
                                        </div>
                                        <v-divider class="my-2"></v-divider>
                                        <div class="text-body-2">
                                            <div class="mb-1">
                                                <v-icon size="small" class="me-1">mdi-email</v-icon>
                                                admin@example.com
                                            </div>
                                            <div>
                                                <v-icon size="small" class="me-1">mdi-key</v-icon>
                                                password
                                            </div>
                                        </div>
                                    </v-card>

                                    <!-- إنشاء حساب جديد -->
                                    <v-divider class="my-4"></v-divider>
                                    <div class="text-center">
                                        <span class="text-body-1 text-medium-emphasis">
                                            {{ t('auth.login.noAccount') }}
                                        </span>
                                        <v-btn
                                            variant="text"
                                            color="primary"
                                            class="ms-2"
                                            size="large"
                                        >
                                            {{ t('auth.login.register') }}
                                        </v-btn>
                                    </div>
                                </v-form>
                            </v-card>
                        </v-col>
                    </v-row>
                </v-container>
            </v-col>
        </v-row>
    </v-container>
</template>

<script setup>
import { ref } from 'vue';
import { useRouter } from 'vue-router';
import { useLanguage } from '../composables/useLanguage';
import { useAuth } from '../composables/useAuth';
import { useSnackbar } from '../composables/useSnackbar';

const { t } = useLanguage();
const router = useRouter();
const { login, loading } = useAuth();
const { showSuccess, showError } = useSnackbar();

const form = ref(null);
const showPassword = ref(false);
const errorMessage = ref('');
const errors = ref({});

const credentials = ref({
    email: '',
    password: '',
    remember: false,
});

// قواعد التحقق
const emailRules = [
    v => !!v || t('validation.required'),
    v => /.+@.+\..+/.test(v) || t('validation.email'),
];

const passwordRules = [
    v => !!v || t('validation.required'),
    v => v.length >= 6 || t('validation.minLength', { length: 6 }),
];

// معالجة تسجيل الدخول
const handleLogin = async () => {
    // التحقق من صحة النموذج
    const { valid } = await form.value.validate();
    if (!valid) return;

    errorMessage.value = '';
    errors.value = {};

    try {
        await login(credentials.value);
        showSuccess(t('auth.login.success'));
        router.push('/');
    } catch (error) {
        console.error('Login error:', error);
        
        if (error.response?.data?.errors) {
            errors.value = error.response.data.errors;
        }
        
        errorMessage.value = error.response?.data?.message 
            || error.response?.data?.errors?.email?.[0]
            || t('auth.login.error');
    }
};
</script>

<style scoped>
.fill-height {
    min-height: 100vh;
}

.gap-4 {
    gap: 1rem;
}

.opacity-90 {
    opacity: 0.9;
}
</style>