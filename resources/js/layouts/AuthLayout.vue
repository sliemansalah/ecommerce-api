<template>
    <v-app>
        <!-- Language Selector فقط -->
        <v-app-bar 
            color="transparent" 
            elevation="0" 
            density="compact"
            class="auth-app-bar"
        >
            <v-spacer></v-spacer>
            
            <!-- Language Dialog -->
            <v-dialog v-model="languageDialog" max-width="300">
                <template v-slot:activator="{ props }">
                    <v-btn 
                        icon 
                        size="small"
                        v-bind="props"
                        variant="outlined"
                    >
                        <span style="font-size: 20px;">{{ currentLocale.flag }}</span>
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
        </v-app-bar>

        <!-- المحتوى -->
        <v-main>
            <slot></slot>
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
    </v-app>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import { useLanguage } from '../composables/useLanguage';
import { useSnackbar } from '../composables/useSnackbar';

const languageDialog = ref(false);

const { locale, t, availableLocales, currentLocale, changeLocale, initDirection } = useLanguage();
const { snackbar } = useSnackbar();

const selectLanguage = (langCode) => {
    changeLocale(langCode);
    languageDialog.value = false;
};

onMounted(() => {
    initDirection();
});
</script>

<style scoped>
.auth-app-bar {
    background: transparent !important;
}
</style>