<template>
  <v-app>
    <v-app-bar color="primary" prominent>
      <v-app-bar-nav-icon @click="drawer = !drawer"></v-app-bar-nav-icon>

      <v-toolbar-title>{{ t("app.title") }}</v-toolbar-title>

      <v-spacer></v-spacer>

      <!-- Language Menu -->
      <v-menu>
        <template v-slot:activator="{ props }">
          <v-btn icon v-bind="props">
            <span style="font-size: 24px">{{ currentLocale.flag }}</span>
          </v-btn>
        </template>
        <v-list>
          <v-list-item
            v-for="lang in availableLocales"
            :key="lang.code"
            @click="changeLocale(lang.code)"
            :class="{ 'v-list-item--active': locale === lang.code }"
          >
            <template v-slot:prepend>
              <span style="font-size: 20px; margin-inline-end: 12px">
                {{ lang.flag }}
              </span>
            </template>
            <v-list-item-title>{{ lang.name }}</v-list-item-title>
          </v-list-item>
        </v-list>
      </v-menu>

      <v-btn icon>
        <v-icon>mdi-magnify</v-icon>
      </v-btn>
    </v-app-bar>

    <!-- Navigation Drawer -->
    <v-navigation-drawer v-model="drawer" temporary>
      <v-list>
        <v-list-item prepend-icon="mdi-home" :title="t('nav.home')" to="/">
        </v-list-item>
        <v-list-item
          prepend-icon="mdi-information"
          :title="t('nav.about')"
          to="/about"
        >
        </v-list-item>
      </v-list>
    </v-navigation-drawer>

    <v-main>
      <v-container>
        <router-view></router-view>
      </v-container>
    </v-main>

    <v-footer app>
      <v-spacer></v-spacer>
      <div>{{ t("app.copyright") }}</div>
      <v-spacer></v-spacer>
    </v-footer>
  </v-app>
</template>

<script setup>
import { ref, onMounted } from "vue";
import { useLanguage } from "./composables/useLanguage";

const drawer = ref(false);

const {
  locale,
  t,
  availableLocales,
  currentLocale,
  changeLocale,
  initDirection,
} = useLanguage();

// تهيئة الاتجاه عند تحميل التطبيق
onMounted(() => {
  initDirection();
});
</script>
