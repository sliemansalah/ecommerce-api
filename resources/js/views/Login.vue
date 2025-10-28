<template>
    <v-container fluid class="fill-height pa-0">
        <v-row no-gutters class="fill-height">
            <!-- الجزء الأيسر/الأيمن - الخلفية الجميلة (يتبدل حسب اللغة) -->
            <v-col 
                cols="12" 
                md="6" 
                class="d-none d-md-flex position-relative"
                :order="currentLocale.dir === 'rtl' ? 2 : 1"
            >
                <div class="login-left-section">
                    <div class="content-wrapper">
                        <!-- الشعار -->
                        <div class="text-center mb-8">
                            <div class="logo-wrapper">
                                <v-icon size="100" color="white">mdi-shopping</v-icon>
                            </div>
                        </div>

                        <!-- النص الترويجي -->
                        <h1 class="text-h2 text-white font-weight-black text-center mb-4">
                            {{ t('app.title') }}
                        </h1>
                        <p class="text-h6 text-white text-center mb-8 subtitle-text">
                            {{ t('auth.login.promo') }}
                        </p>

                        <!-- المميزات -->
                        <div class="features-grid">
                            <div v-for="(feature, index) in features" :key="index" class="feature-item">
                                <v-icon color="white" size="24" class="mb-2">{{ feature.icon }}</v-icon>
                                <div class="text-white text-body-1 font-weight-medium">{{ feature.title }}</div>
                                <div class="text-white text-body-2 mt-1" style="opacity: 0.9;">{{ feature.description }}</div>
                            </div>
                        </div>
                    </div>

                    <!-- الدوائر الزخرفية -->
                    <div class="circle circle-1"></div>
                    <div class="circle circle-2"></div>
                    <div class="circle circle-3"></div>
                </div>
            </v-col>

            <!-- الجزء الأيمن/الأيسر - النموذج (يتبدل حسب اللغة) -->
            <v-col 
                cols="12" 
                md="6" 
                class="login-form-section"
                :order="currentLocale.dir === 'rtl' ? 1 : 2"
            >
                <!-- زر اللغة - Dropdown -->
                <div class="language-button-wrapper">
                    <v-btn 
                        icon 
                        size="default"
                        variant="text"
                        @click.stop="toggleLanguageMenu" 
                        ref="languageMenuBtn"
                        class="language-btn"
                    >
                        <span style="font-size: 24px;">{{ currentLocale.flag }}</span>
                    </v-btn>
                </div>

                <v-container class="fill-height">
                    <v-row justify="center" align="center" class="fill-height">
                        <v-col cols="12" sm="10" md="9" lg="7" xl="6">
                            <!-- شعار الموبايل -->
                            <div class="text-center mb-8 d-md-none">
                                <v-icon size="70" color="primary" class="mb-2">mdi-shopping</v-icon>
                                <h2 class="text-h4 font-weight-bold primary--text">{{ t('app.title') }}</h2>
                            </div>

                            <!-- العنوان -->
                            <div class="mb-8">
                                <h2 class="text-h3 font-weight-black mb-2">
                                    {{ t('auth.login.title') }}
                                </h2>
                                <p class="text-h6 text-medium-emphasis">
                                    {{ t('auth.login.subtitle') }}
                                </p>
                            </div>

                            <!-- النموذج -->
                            <v-form @submit.prevent="handleLogin" ref="form">
                                <!-- البريد الإلكتروني -->
                                <div class="mb-5">
                                    <v-text-field
                                        v-model="credentials.email"
                                        :rules="emailRules"
                                        type="email"
                                        :label="t('auth.login.email')"
                                        prepend-inner-icon="mdi-email-outline"
                                        variant="outlined"
                                        color="primary"
                                        :error-messages="errors.email"
                                        placeholder="admin@example.com"
                                        class="modern-input"
                                        hide-details="auto"
                                        density="comfortable"
                                        :dir="currentLocale.dir"
                                    ></v-text-field>
                                </div>

                                <!-- كلمة المرور -->
                                <div class="mb-4">
                                    <v-text-field
                                        v-model="credentials.password"
                                        :rules="passwordRules"
                                        :type="showPassword ? 'text' : 'password'"
                                        :label="t('auth.login.password')"
                                        prepend-inner-icon="mdi-lock-outline"
                                        :append-inner-icon="showPassword ? 'mdi-eye-off-outline' : 'mdi-eye-outline'"
                                        @click:append-inner="showPassword = !showPassword"
                                        variant="outlined"
                                        color="primary"
                                        :error-messages="errors.password"
                                        placeholder="••••••••"
                                        class="modern-input"
                                        hide-details="auto"
                                        density="comfortable"
                                        :dir="currentLocale.dir"
                                    ></v-text-field>
                                </div>

                                <!-- تذكرني -->
                                <div class="mb-6">
                                    <v-checkbox
                                        v-model="credentials.remember"
                                        :label="t('auth.login.remember')"
                                        color="primary"
                                        hide-details
                                        density="compact"
                                        class="remember-checkbox"
                                    ></v-checkbox>
                                </div>

                                <!-- رسالة خطأ -->
                                <v-expand-transition>
                                    <v-alert
                                        v-if="errorMessage"
                                        type="error"
                                        variant="tonal"
                                        closable
                                        class="mb-5"
                                        @click:close="errorMessage = ''"
                                        rounded="lg"
                                    >
                                        <div class="d-flex align-center">
                                            <v-icon class="me-2">mdi-alert-circle-outline</v-icon>
                                            <span>{{ errorMessage }}</span>
                                        </div>
                                    </v-alert>
                                </v-expand-transition>

                                <!-- زر تسجيل الدخول -->
                                <v-btn
                                    type="submit"
                                    color="primary"
                                    size="x-large"
                                    block
                                    :loading="loading"
                                    class="text-h6 font-weight-bold login-btn"
                                    elevation="0"
                                    height="56"
                                >
                                    <v-icon start size="24">mdi-login</v-icon>
                                    {{ t('auth.login.button') }}
                                </v-btn>
                            </v-form>

                            <!-- Footer -->
                            <div class="text-center mt-8">
                                <v-chip
                                    variant="tonal"
                                    color="success"
                                    size="small"
                                    prepend-icon="mdi-shield-check"
                                >
                                    {{ t('auth.login.secure') }}
                                </v-chip>
                            </div>
                        </v-col>
                    </v-row>
                </v-container>
            </v-col>
        </v-row>

        <!-- Language Menu Dropdown -->
        <teleport to="body">
            <transition name="fade">
                <div 
                    v-if="showLanguageMenu" 
                    class="menu-overlay"
                    @click="showLanguageMenu = false"
                >
                    <v-card 
                        class="menu-card"
                        :style="{
                            position: 'fixed',
                            top: languageMenuTop + 'px',
                            right: currentLocale.dir === 'rtl' ? 'auto' : languageMenuRight + 'px',
                            left: currentLocale.dir === 'rtl' ? languageMenuLeft + 'px' : 'auto',
                            zIndex: 9999
                        }"
                        @click.stop
                        width="280"
                        elevation="12"
                        rounded="lg"
                    >
                        <v-card-title class="text-center py-3 bg-primary">
                            <div class="d-flex align-center justify-center text-white">
                                <v-icon color="white" class="me-2">mdi-translate</v-icon>
                                <span>{{ t('nav.language') }}</span>
                            </div>
                        </v-card-title>
                        
                        <v-list density="comfortable">
                            <v-list-item
                                v-for="lang in availableLocales"
                                :key="lang.code"
                                @click="selectLanguage(lang.code)"
                                :active="locale === lang.code"
                                class="menu-item"
                            >
                                <template v-slot:prepend>
                                    <span style="font-size: 24px; margin-inline-end: 12px;">
                                        {{ lang.flag }}
                                    </span>
                                </template>
                                <v-list-item-title>{{ lang.name }}</v-list-item-title>
                                <template v-slot:append>
                                    <v-icon v-if="locale === lang.code" color="primary">
                                        mdi-check-circle
                                    </v-icon>
                                </template>
                            </v-list-item>
                        </v-list>
                    </v-card>
                </div>
            </transition>
        </teleport>
    </v-container>
</template>

<script setup>
import { ref, computed, onMounted, nextTick } from 'vue';
import { useRouter } from 'vue-router';
import { useLanguage } from '../composables/useLanguage';
import { useAuth } from '../composables/useAuth';
import { useSnackbar } from '../composables/useSnackbar';

const { t, locale, availableLocales, currentLocale, changeLocale, initDirection } = useLanguage();
const router = useRouter();
const { login, loading } = useAuth();
const { showSuccess } = useSnackbar();

const form = ref(null);
const showPassword = ref(false);
const errorMessage = ref('');
const errors = ref({});
const showLanguageMenu = ref(false);
const languageMenuBtn = ref(null);
const languageMenuTop = ref(0);
const languageMenuRight = ref(0);
const languageMenuLeft = ref(0);

const credentials = ref({
    email: '',
    password: '',
    remember: false,
});

const features = computed(() => [
    {
        icon: 'mdi-security',
        title: currentLocale.value.code === 'ar' ? 'آمن' : 'Secure',
        description: currentLocale.value.code === 'ar' ? 'حماية متقدمة' : 'Advanced Protection'
    },
    {
        icon: 'mdi-flash',
        title: currentLocale.value.code === 'ar' ? 'سريع' : 'Fast',
        description: currentLocale.value.code === 'ar' ? 'أداء عالي' : 'High Performance'
    },
    {
        icon: 'mdi-heart',
        title: currentLocale.value.code === 'ar' ? 'سهل' : 'Easy',
        description: currentLocale.value.code === 'ar' ? 'واجهة بسيطة' : 'Simple Interface'
    }
]);

const emailRules = [
    v => !!v || t('validation.required'),
    v => /.+@.+\..+/.test(v) || t('validation.email'),
];

const passwordRules = [
    v => !!v || t('validation.required'),
    v => v.length >= 6 || t('validation.minLength', { length: 6 }),
];

// Language Menu Functions
const toggleLanguageMenu = () => {
    if (!showLanguageMenu.value) {
        calculateLanguageMenuPosition();
    }
    showLanguageMenu.value = !showLanguageMenu.value;
};

const calculateLanguageMenuPosition = () => {
    nextTick(() => {
        if (languageMenuBtn.value) {
            const btnElement = languageMenuBtn.value.$el;
            const rect = btnElement.getBoundingClientRect();
            
            languageMenuTop.value = rect.bottom + 8;
            languageMenuRight.value = window.innerWidth - rect.right;
            languageMenuLeft.value = rect.left;
        }
    });
};

const selectLanguage = (langCode) => {
    changeLocale(langCode);
    showLanguageMenu.value = false;
};

const handleLogin = async () => {
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

onMounted(() => {
    initDirection();
});
</script>

<style scoped>
.fill-height {
    min-height: 100vh;
}

/* زر اللغة */
.language-button-wrapper {
    position: absolute;
    top: 20px;
    z-index: 10;
}

[dir="rtl"] .language-button-wrapper {
    left: 20px;
}

[dir="ltr"] .language-button-wrapper {
    right: 20px;
}

.language-btn {
    background: rgba(0, 0, 0, 0.05);
    transition: all 0.3s ease;
}

.language-btn:hover {
    background: rgba(0, 0, 0, 0.1);
    transform: scale(1.05);
}

/* القسم الأيسر */
.login-left-section {
    position: relative;
    width: 100%;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    overflow: hidden;
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 3rem;
}

.content-wrapper {
    position: relative;
    z-index: 2;
    max-width: 500px;
}

/* الشعار */
.logo-wrapper {
    width: 140px;
    height: 140px;
    margin: 0 auto;
    background: rgba(255, 255, 255, 0.2);
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    backdrop-filter: blur(10px);
    border: 3px solid rgba(255, 255, 255, 0.3);
    animation: pulse-slow 3s ease-in-out infinite;
}

@keyframes pulse-slow {
    0%, 100% {
        transform: scale(1);
        box-shadow: 0 0 0 0 rgba(255, 255, 255, 0.4);
    }
    50% {
        transform: scale(1.05);
        box-shadow: 0 0 0 20px rgba(255, 255, 255, 0);
    }
}

.subtitle-text {
    opacity: 0.95;
    line-height: 1.6;
}

/* المميزات */
.features-grid {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 1.5rem;
    margin-top: 3rem;
}

.feature-item {
    text-align: center;
    padding: 1.5rem 1rem;
    background: rgba(255, 255, 255, 0.1);
    border-radius: 16px;
    backdrop-filter: blur(10px);
    border: 1px solid rgba(255, 255, 255, 0.2);
    transition: all 0.3s ease;
}

.feature-item:hover {
    background: rgba(255, 255, 255, 0.2);
    transform: translateY(-5px);
}

/* الدوائر الزخرفية */
.circle {
    position: absolute;
    border-radius: 50%;
    background: rgba(255, 255, 255, 0.1);
    backdrop-filter: blur(20px);
}

.circle-1 {
    width: 300px;
    height: 300px;
    top: -100px;
    animation: float-1 20s ease-in-out infinite;
}

[dir="rtl"] .circle-1 {
    left: -100px;
}

[dir="ltr"] .circle-1 {
    right: -100px;
}

.circle-2 {
    width: 200px;
    height: 200px;
    bottom: -50px;
    animation: float-2 15s ease-in-out infinite;
}

[dir="rtl"] .circle-2 {
    right: -50px;
}

[dir="ltr"] .circle-2 {
    left: -50px;
}

.circle-3 {
    width: 150px;
    height: 150px;
    top: 50%;
    animation: float-3 18s ease-in-out infinite;
}

[dir="rtl"] .circle-3 {
    right: 10%;
}

[dir="ltr"] .circle-3 {
    left: 10%;
}

@keyframes float-1 {
    0%, 100% { transform: translate(0, 0) rotate(0deg); }
    50% { transform: translate(-30px, 30px) rotate(180deg); }
}

@keyframes float-2 {
    0%, 100% { transform: translate(0, 0) rotate(0deg); }
    50% { transform: translate(30px, -30px) rotate(-180deg); }
}

@keyframes float-3 {
    0%, 100% { transform: translate(0, 0) scale(1); }
    50% { transform: translate(20px, 20px) scale(1.1); }
}

/* القسم الأيمن */
.login-form-section {
    background: #ffffff;
    position: relative;
}

/* الحقول */
.modern-input :deep(.v-field) {
    border-radius: 14px;
    font-size: 16px;
    transition: all 0.3s ease;
}

.modern-input :deep(.v-field--focused) {
    box-shadow: 0 0 0 4px rgba(102, 126, 234, 0.1);
}

.modern-input :deep(.v-field__overlay) {
    background: transparent;
}

/* تحسينات RTL للحقول */
[dir="rtl"] .modern-input :deep(.v-field__prepend-inner) {
    margin-inline-start: 0;
    margin-inline-end: 8px;
}

[dir="rtl"] .modern-input :deep(.v-field__append-inner) {
    margin-inline-start: 8px;
    margin-inline-end: 0;
}

[dir="rtl"] .modern-input :deep(.v-field__input) {
    text-align: right;
}

[dir="ltr"] .modern-input :deep(.v-field__prepend-inner) {
    margin-inline-end: 8px;
    margin-inline-start: 0;
}

[dir="ltr"] .modern-input :deep(.v-field__append-inner) {
    margin-inline-end: 0;
    margin-inline-start: 8px;
}

[dir="ltr"] .modern-input :deep(.v-field__input) {
    text-align: left;
}

/* Checkbox */
.remember-checkbox :deep(.v-label) {
    font-size: 15px;
    font-weight: 500;
}

/* زر تسجيل الدخول */
.login-btn {
    border-radius: 14px;
    text-transform: none;
    letter-spacing: 0.5px;
    transition: all 0.3s ease;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%) !important;
}

.login-btn:hover {
    transform: translateY(-2px);
    box-shadow: 0 10px 25px rgba(102, 126, 234, 0.3);
}

/* Menu Overlay */
.menu-overlay {
    position: fixed;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    z-index: 9998;
    background: transparent;
}

.menu-card {
    box-shadow: 0 8px 24px rgba(0, 0, 0, 0.15) !important;
}

.menu-item {
    cursor: pointer;
    transition: background-color 0.2s ease;
}

.menu-item:hover {
    background-color: rgba(102, 126, 234, 0.08);
}

/* Animation */
.fade-enter-active,
.fade-leave-active {
    transition: opacity 0.15s ease;
}

.fade-enter-from,
.fade-leave-to {
    opacity: 0;
}

.fade-enter-active .menu-card {
    animation: slideDown 0.2s ease-out;
}

@keyframes slideDown {
    from {
        opacity: 0;
        transform: translateY(-10px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

/* Responsive */
@media (max-width: 960px) {
    .features-grid {
        grid-template-columns: 1fr;
        gap: 1rem;
    }
    
    .feature-item {
        padding: 1rem;
    }
}
</style>