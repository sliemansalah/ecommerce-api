<template>
    <v-app>
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
const { initDirection } = useLanguage();
const { snackbar } = useSnackbar();

onMounted(() => {
    initDirection();
});
</script>

<style scoped>
.auth-app-bar {
    background: transparent !important;
}
</style>