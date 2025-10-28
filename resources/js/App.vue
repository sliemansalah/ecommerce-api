<template>
    <v-app>
        <v-app-bar color="primary" elevation="2" density="default">
            <v-app-bar-nav-icon @click="drawer = !drawer"></v-app-bar-nav-icon>
            
            <v-toolbar-title class="font-weight-bold">
                {{ t('app.title') }}
            </v-toolbar-title>
            
            <v-spacer></v-spacer>

            <!-- Language Button with Dialog -->
            <v-dialog v-model="languageDialog" max-width="300">
                <template v-slot:activator="{ props }">
                    <v-btn 
                        icon 
                        size="default"
                        v-bind="props"
                    >
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
                                <v-icon 
                                    v-if="locale === lang.code" 
                                    color="primary"
                                >
                                    mdi-check-circle
                                </v-icon>
                            </template>
                        </v-list-item>
                    </v-list>
                </v-card>
            </v-dialog>

            <v-btn icon size="default">
                <v-icon>mdi-magnify</v-icon>
            </v-btn>
        </v-app-bar>

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
                            <v-icon color="primary" size="24">mdi-account-circle</v-icon>
                        </v-avatar>
                    </template>
                    <v-list-item-title class="text-white font-weight-bold">
                        {{ t('app.title') }}
                    </v-list-item-title>
                    <v-list-item-subtitle class="text-white text-caption">
                        مشروع تعليمي
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
            </v-list>

            <template v-slot:append>
                <v-divider></v-divider>
                <div class="pa-3 text-center">
                    <v-chip 
                        color="primary" 
                        variant="tonal"
                        size="small"
                    >
                        <v-icon start size="small">mdi-check-circle</v-icon>
                        متصل
                    </v-chip>
                </div>
            </template>
        </v-navigation-drawer>

        <v-main class="bg-grey-lighten-4">
            <v-container class="py-6" fluid>
                <router-view></router-view>
            </v-container>
        </v-main>

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

const drawer = ref(true);
const languageDialog = ref(false);

const {
    locale,
    t,
    availableLocales,
    currentLocale,
    changeLocale,
    initDirection,
} = useLanguage();

// تحديد اللغة وإغلاق Dialog
const selectLanguage = (langCode) => {
    changeLocale(langCode);
    languageDialog.value = false;
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