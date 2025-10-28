import axios from "axios";
import { createPinia } from "pinia";
import { createApp } from "vue";
import App from "./App.vue";
import i18n from "./plugins/i18n";
import vuetify from "./plugins/vuetify";
import router from "./router";

// إعداد Axios
window.axios = axios;
window.axios.defaults.headers.common["X-Requested-With"] = "XMLHttpRequest";
window.axios.defaults.baseURL = "/api";

// CSRF Token
const token = document.head.querySelector("meta[name=\"csrf-token\"]");
if (token) {
  window.axios.defaults.headers.common["X-CSRF-TOKEN"] = token.content;
}

const app = createApp(App);
const pinia = createPinia();

app.use(pinia);
app.use(router);
app.use(vuetify);
app.use(i18n); // ✅ إضافة i18n

app.mount("#app");
