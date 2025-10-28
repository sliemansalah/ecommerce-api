<template>
    <v-app>
        <v-app-bar color="primary" elevation="2" density="default">
            <v-app-bar-nav-icon @click="drawer = !drawer"></v-app-bar-nav-icon>
            
            <v-toolbar-title class="font-weight-bold">
                {{ t('app.title') }}
            </v-toolbar-title>
            
            <v-spacer></v-spacer>

            <!-- Language Dialog -->
            <v-dialog v-model="languageDialog" max-width="300">
                <template v-slot:activator="{ props }">
                    <v-btn icon size="default" v-bind="props">
                        <span style="font-size: 24px;">{{ currentLocale.flag }}</span>
                    </v-btn>
                </template>
                
                <v-card>
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
            </v-dialog>

            <!-- User Menu -->
            <v-menu v-if="isAuthenticated" offset-y>
                <template v-slot:activator="{ props }">
                    <v-btn icon v-bind="props">
                        <v-avatar color="white" size="36">
                            <span class="primary--text font-weight-bold">
                                {{ user?.initials }}
                            </span>
                        </v-avatar>
                    </v-btn>
                </template>
                
                <v-card min-width="250">
                    <v-list>
                        <v-list-item class="pa-3">
                            <template v-slot:prepend>
                                <v-avatar color="primary" size="40">
                                    <span class="text-white font-weight-bold">
                                        {{ user?.initials }}
                                    </span>
                                </v-avatar>
                            </template>
                            <v-list-item-title class="font-weight-bold">
                                {{ user?.name }}
                            </v-list-item-title>
                            <v-list-item-subtitle>
                                {{ user?.email }}
                            </v-list-item-subtitle>
                        </v-list-item>
                    </v-list>
                    
                    <v-divider></v-divider>
                    
                    <v-list density="compact">
                        <v-list-item prepend-icon="mdi-account" to="/profile">
                            <v-list-item-title>{{ t('nav.profile') }}</v-list-item-title>
                        </v-list-item>
                        
                        <v-list-item 
                            prepend-icon="mdi-logout" 
                            @click="logoutDialog = true"
                        >
                            <v-list-item-title>{{ t('nav.logout') }}</v-list-item-title>
                        </v-list-item>
                    </v-list>
                </v-card>
            </v-menu>

            <!-- Login Button -->
            <v-btn 
                v-else
                variant="text" 
                to="/login"
                prepend-icon="mdi-login"
            >
                {{ t('nav.login') }}
            </v-btn>
        </v-app-bar>

        <!-- Logout Confirmation Dialog -->
        <v-dialog v-model="logoutDialog" max-width="400">
            <v-card>
                <v-card-title class="text-h5 text-center py-4">
                    {{ t('auth.logout.title') }}
                </v-card-title>
                <v-card-text class="text-center pb-0">
                    <v-icon size="64" color="warning" class="mb-4">
                        mdi-logout
                    </v-icon>
                    <p class="text-body-1">{{ t('auth.logout.message') }}</p>
                </v-card-text>
                <v-card-actions class="pa-4">
                    <v-btn 
                        variant="text" 
                        @click="logoutDialog = false"
                        block
                    >
                        {{ t('auth.logout.cancel') }}
                    </v-btn>
                    <v-btn 
                        color="error" 
                        variant="flat"
                        @click="handleLogout"
                        :loading="loading"
                        block
                    >
                        {{ t('auth.logout.confirm') }}
                    </v-btn>
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
                            <v-icon color="primary" size="24">
                                {{ isAuthenticated ? 'mdi-account' : 'mdi-account-circle' }}
                            </v-icon>
                        </v-avatar>
                    </template>
                    <v-list-item-title class="text-white font-weight-bold">
                        {{ isAuthenticated ? user?.first_name : t('app.title') }}
                    </v-list-item-title>
                    <v-list-item-subtitle class="text-white text-caption">
                        {{ isAuthenticated ? user?.email : 'مشروع تعليمي' }}
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
                    v-if="isAuthenticated"
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
                    <v-chip 
                        :color="isAuthenticated ? 'success' : 'primary'" 
                        variant="tonal"
                        size="small"
                    >
                        <v-icon start size="small">
                            {{ isAuthenticated ? 'mdi-check-circle' : 'mdi-information' }}
                        </v-icon>
                        {{ isAuthenticated ? 'متصل' : 'غير متصل' }}
                    </v-chip>
                </div>
            </template>
        </v-navigation-drawer>

        <v-main class="bg-grey-lighten-4">
            <v-container class="py-6" fluid>
                <router-view></router-view>
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
                <v-btn variant="text" @click="snackbar.show = false">
                    إغلاق
                </v-btn>
            </template>
        </v-snackbar>

        <v-footer app class="bg-grey-darken-3" height="auto">
            <v-row justify="center" no-gutters class="py-2">
                <v-col class="text-center" cols="12">
                    <span class="text-white font-weight-medium">
                        {{ t('app.copyright') }}
                    </span>
                </v-col>
            </v-row>
        </v-footer>
    </v-app>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import { useLanguage } from './composables/useLanguage';
import { useAuth } from './composables/useAuth';
import { useSnackbar } from './composables/useSnackbar';

const drawer = ref(true);
const languageDialog = ref(false);
const logoutDialog = ref(false);

const { locale, t, availableLocales, currentLocale, changeLocale, initDirection } = useLanguage();
const { user, isAuthenticated, loading, logout } = useAuth();
const { snackbar, showSuccess } = useSnackbar();

const selectLanguage = (langCode) => {
    changeLocale(langCode);
    languageDialog.value = false;
};

const handleLogout = async () => {
    try {
        await logout();
        logoutDialog.value = false;
        showSuccess(t('auth.logout.success'));
    } catch (error) {
        console.error('Logout error:', error);
    }
};

onMounted(() => {
    initDirection();
});
</script>

<style scoped>
/* تحسين مظهر الـ Drawer */
.v-navigation-drawer {
    border-color: rgba(0, 0, 0, 0.08) !important;
}

/* تحسين الأزرار النشطة */
.v-list-item--active {
    background-color: rgb(var(--v-theme-primary)) !important;
    color: white !important;
}

.v-list-item--active .v-icon {
    color: white !important;
}

/* تحسين Dialog */
.v-dialog .v-card {
    border-radius: 16px !important;
    overflow: hidden;
}

.v-dialog .v-list-item {
    cursor: pointer;
    transition: all 0.2s ease;
}

.v-dialog .v-list-item:hover {
    background-color: rgba(25, 118, 210, 0.08) !important;
}

.v-dialog .v-list-item--active {
    background-color: rgba(25, 118, 210, 0.12) !important;
}
</style>