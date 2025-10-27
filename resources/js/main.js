// resources/js/main.js
import { createApp } from 'vue';
import { createPinia } from 'pinia';
import App from './App.vue';
import router from './router';
import vuetify from './plugins/vuetify';
import axios from './plugins/axios';

// إنشاء التطبيق
const app = createApp(App);

// إضافة Pinia
const pinia = createPinia();
app.use(pinia);

// إضافة Router
app.use(router);

// إضافة Vuetify
app.use(vuetify);

// جعل Axios متاح عالمياً
app.config.globalProperties.$axios = axios;

// تفعيل DevTools في وضع التطوير
app.config.performance = true;

// تركيب التطبيق
app.mount('#app');
