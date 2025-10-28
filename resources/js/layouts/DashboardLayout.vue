<template>
  <v-app>
    <v-app-bar color="primary" elevation="2" density="default">
      <v-app-bar-nav-icon @click="drawer = !drawer"></v-app-bar-nav-icon>

      <v-toolbar-title class="font-weight-bold">
        {{ t("app.title") }}
      </v-toolbar-title>

      <v-spacer></v-spacer>

      <!-- Language Dropdown Button -->
      <v-btn
        icon
        @click.stop="toggleLanguageMenu"
        ref="languageMenuBtn"
        class="me-2"
      >
        <span style="font-size: 24px">{{ currentLocale.flag }}</span>
      </v-btn>

      <!-- User Menu Button -->
      <v-btn icon @click.stop="toggleUserMenu" ref="userMenuBtn">
        <v-avatar color="white" size="36">
          <span class="text-primary font-weight-bold">
            {{ user?.initials }}
          </span>
        </v-avatar>
      </v-btn>
    </v-app-bar>

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
              right: languageMenuRight + 'px',
              zIndex: 9999,
            }"
            @click.stop
            width="280"
            elevation="12"
            rounded="lg"
          >
            <v-card-title class="text-center py-3 bg-primary">
              <div class="d-flex align-center justify-center text-white">
                <v-icon color="white" class="me-2">mdi-translate</v-icon>
                <span>{{ t("nav.language") }}</span>
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
                  <span style="font-size: 24px; margin-inline-end: 12px">
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

    <!-- User Menu Dropdown -->
    <teleport to="body">
      <transition name="fade">
        <div
          v-if="showUserMenu"
          class="menu-overlay"
          @click="showUserMenu = false"
        >
          <v-card
            class="menu-card"
            :style="{
              position: 'fixed',
              top: userMenuTop + 'px',
              right: userMenuRight + 'px',
              zIndex: 9999,
            }"
            @click.stop
            width="300"
            elevation="12"
            rounded="lg"
          >
            <v-list>
              <v-list-item class="pa-4">
                <template v-slot:prepend>
                  <v-avatar color="primary" size="50">
                    <span class="text-white font-weight-bold text-h6">
                      {{ user?.initials }}
                    </span>
                  </v-avatar>
                </template>
                <v-list-item-title class="font-weight-bold text-h6">
                  {{ user?.name }}
                </v-list-item-title>
                <v-list-item-subtitle class="text-body-2">
                  {{ user?.email }}
                </v-list-item-subtitle>
              </v-list-item>
            </v-list>

            <v-divider></v-divider>

            <v-list density="comfortable" class="py-2">
              <v-list-item
                prepend-icon="mdi-account"
                @click="goToProfile"
                class="menu-item"
              >
                <v-list-item-title>{{ t("nav.profile") }}</v-list-item-title>
              </v-list-item>

              <v-list-item
                prepend-icon="mdi-logout"
                @click="openLogoutDialog"
                class="menu-item"
              >
                <v-list-item-title>{{ t("nav.logout") }}</v-list-item-title>
              </v-list-item>
            </v-list>
          </v-card>
        </div>
      </transition>
    </teleport>

    <!-- Logout Confirmation Dialog - محسّن -->
    <v-dialog v-model="logoutDialog" max-width="450" persistent>
      <v-card rounded="xl" elevation="8">
        <!-- Header بدون لون -->
        <v-card-text class="text-center pt-8 pb-4">
          <!-- أيقونة جميلة -->
          <div class="mb-4">
            <v-avatar color="error" size="80" class="elevation-4">
              <v-icon size="50" color="white">mdi-logout-variant</v-icon>
            </v-avatar>
          </div>

          <!-- العنوان -->
          <h2 class="text-h4 font-weight-bold mb-3">
            {{ t("auth.logout.title") }}
          </h2>

          <!-- الرسالة -->
          <p class="text-h6 text-medium-emphasis mb-2">
            {{ t("auth.logout.message") }}
          </p>

          <!-- معلومات إضافية -->
          <v-card variant="tonal" color="info" class="mt-4 pa-3">
            <div class="d-flex align-center justify-center">
              <v-icon color="info" class="me-2">mdi-information</v-icon>
              <span class="text-body-2">سيتم إنهاء جلستك الحالية</span>
            </div>
          </v-card>
        </v-card-text>

        <!-- الأزرار -->
        <v-card-actions class="pa-6 pt-2">
          <v-row no-gutters>
            <v-col cols="6" class="pe-2">
              <v-btn
                variant="outlined"
                color="grey-darken-1"
                @click="logoutDialog = false"
                size="large"
                block
              >
                <v-icon start>mdi-close</v-icon>
                {{ t("auth.logout.cancel") }}
              </v-btn>
            </v-col>
            <v-col cols="6" class="ps-2">
              <v-btn
                color="error"
                variant="flat"
                @click="handleLogout"
                :loading="loading"
                size="large"
                block
                elevation="2"
              >
                <v-icon start>mdi-logout</v-icon>
                {{ t("auth.logout.confirm") }}
              </v-btn>
            </v-col>
          </v-row>
        </v-card-actions>
      </v-card>
    </v-dialog>

    <!-- Navigation Drawer -->
    <v-navigation-drawer
      v-model="drawer"
      permanent
      width="280"
      :location="currentLocale.dir === 'rtl' ? 'right' : 'left'"
      elevation="1"
    >
      <!-- Header -->
      <template v-slot:prepend>
        <v-list-item class="pa-3 bg-primary">
          <template v-slot:prepend>
            <v-avatar color="white" size="42">
              <v-icon color="primary" size="24">mdi-account</v-icon>
            </v-avatar>
          </template>
          <v-list-item-title class="text-white font-weight-bold">
            {{ user?.first_name }}
          </v-list-item-title>
          <v-list-item-subtitle class="text-white text-caption">
            {{ user?.email }}
          </v-list-item-subtitle>
        </v-list-item>
      </template>

      <!-- Navigation Items -->
      <v-list nav density="comfortable" class="pa-3">
        <v-list-item
          prepend-icon="mdi-home"
          :title="t('nav.home')"
          to="/"
          rounded="lg"
          color="primary"
          class="mb-2"
          active-class="bg-primary text-white"
        >
        </v-list-item>

        <v-list-item
          prepend-icon="mdi-information"
          :title="t('nav.about')"
          to="/about"
          rounded="lg"
          color="primary"
          class="mb-2"
          active-class="bg-primary text-white"
        >
        </v-list-item>

        <v-list-item
          prepend-icon="mdi-account"
          :title="t('nav.profile')"
          to="/profile"
          rounded="lg"
          color="primary"
          class="mb-2"
          active-class="bg-primary text-white"
        >
        </v-list-item>
      </v-list>

      <template v-slot:append>
        <v-divider></v-divider>
        <div class="pa-3 text-center">
          <v-chip color="success" variant="tonal" size="small">
            <v-icon start size="small">mdi-check-circle</v-icon>
            متصل
          </v-chip>
        </div>
      </template>
    </v-navigation-drawer>

    <v-main class="bg-grey-lighten-4">
      <v-container class="py-6" fluid>
        <slot></slot>
      </v-container>
    </v-main>

    <!-- Snackbar -->
    <v-snackbar
      v-model="snackbar.show"
      :color="snackbar.color"
      :timeout="snackbar.timeout"
      location="top"
    >
      {{ snackbar.message }}
      <template v-slot:actions>
        <v-btn variant="text" @click="snackbar.show = false"> إغلاق </v-btn>
      </template>
    </v-snackbar>

    <v-footer app class="bg-grey-darken-3" height="auto">
      <v-row justify="center" no-gutters class="py-2">
        <v-col class="text-center" cols="12">
          <span class="text-white font-weight-medium">
            {{ t("app.copyright") }}
          </span>
        </v-col>
      </v-row>
    </v-footer>
  </v-app>
</template>

<script setup>
import { ref, onMounted, nextTick } from "vue";
import { useRouter } from "vue-router";
import { useLanguage } from "../composables/useLanguage";
import { useAuth } from "../composables/useAuth";
import { useSnackbar } from "../composables/useSnackbar";

const router = useRouter();
const drawer = ref(true);
const showLanguageMenu = ref(false);
const showUserMenu = ref(false);
const logoutDialog = ref(false);
const languageMenuBtn = ref(null);
const userMenuBtn = ref(null);
const languageMenuTop = ref(0);
const languageMenuRight = ref(0);
const userMenuTop = ref(0);
const userMenuRight = ref(0);

const {
  locale,
  t,
  availableLocales,
  currentLocale,
  changeLocale,
  initDirection,
} = useLanguage();
const { user, loading, logout } = useAuth();
const { snackbar, showSuccess } = useSnackbar();

// Language Menu Functions
const toggleLanguageMenu = () => {
  if (!showLanguageMenu.value) {
    calculateLanguageMenuPosition();
  }
  showLanguageMenu.value = !showLanguageMenu.value;
  // إغلاق قائمة المستخدم إذا كانت مفتوحة
  if (showLanguageMenu.value) {
    showUserMenu.value = false;
  }
};

const calculateLanguageMenuPosition = () => {
  nextTick(() => {
    if (languageMenuBtn.value) {
      const btnElement = languageMenuBtn.value.$el;
      const rect = btnElement.getBoundingClientRect();

      languageMenuTop.value = rect.bottom + 8;
      languageMenuRight.value = window.innerWidth - rect.right;
    }
  });
};

const selectLanguage = (langCode) => {
  changeLocale(langCode);
  showLanguageMenu.value = false;
};

// User Menu Functions
const toggleUserMenu = () => {
  if (!showUserMenu.value) {
    calculateUserMenuPosition();
  }
  showUserMenu.value = !showUserMenu.value;
  // إغلاق قائمة اللغة إذا كانت مفتوحة
  if (showUserMenu.value) {
    showLanguageMenu.value = false;
  }
};

const calculateUserMenuPosition = () => {
  nextTick(() => {
    if (userMenuBtn.value) {
      const btnElement = userMenuBtn.value.$el;
      const rect = btnElement.getBoundingClientRect();

      userMenuTop.value = rect.bottom + 8;
      userMenuRight.value = window.innerWidth - rect.right;
    }
  });
};

const goToProfile = () => {
  showUserMenu.value = false;
  router.push("/profile");
};

const openLogoutDialog = () => {
  showUserMenu.value = false;
  logoutDialog.value = true;
};

const handleLogout = async () => {
  try {
    await logout();
    logoutDialog.value = false;
    showSuccess(t("auth.logout.success"));
  } catch (error) {
    console.error("Logout error:", error);
  }
};

onMounted(() => {
  initDirection();
});
</script>

<style scoped>
.v-navigation-drawer {
  border-color: rgba(0, 0, 0, 0.08) !important;
}

.v-list-item--active {
  background-color: rgb(var(--v-theme-primary)) !important;
  color: white !important;
}

.v-list-item--active .v-icon {
  color: white !important;
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
  background-color: rgba(25, 118, 210, 0.08);
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
</style>