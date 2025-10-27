// resources/js/main.js

import { createApp } from 'vue';
import { createPinia } from 'pinia';
import App from './App.vue';
import router from './router';
import vuetify from './plugins/vuetify';
import axios from './plugins/axios';

// إنشاء التطبيق
const app = createApp(App);
const pinia = createPinia();

// تثبيت Plugins
app.use(pinia);
app.use(router);
app.use(vuetify);

// جعل axios متاح في كل التطبيق
app.config.globalProperties.$axios = axios;

// تهيئة Auth Store قبل mount
import { useAuthStore } from '@/stores/auth';
const authStore = useAuthStore();
authStore.initAuth();

// تثبيت التطبيق
app.mount('#app');