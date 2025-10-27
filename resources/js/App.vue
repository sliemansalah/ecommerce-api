<!-- resources/js/App.vue -->
<template>
    <v-app>
        <router-view />
    </v-app>
</template>

<script setup>
import { onMounted } from 'vue';
import { useAuthStore } from '@/stores/auth';

const authStore = useAuthStore();

// جلب بيانات المستخدم عند بدء التطبيق
onMounted(() => {
    if (authStore.isAuthenticated) {
        authStore.fetchUser();
    }
});
</script>

<style>
* {
    margin: 0;
    padding: 0;
    box-sizing: border-box;
}

html {
    direction: rtl;
}

body {
    font-family: 'Tajawal', 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
}

/* تخصيص Scrollbar */
::-webkit-scrollbar {
    width: 8px;
    height: 8px;
}

::-webkit-scrollbar-track {
    background: #f1f1f1;
}

::-webkit-scrollbar-thumb {
    background: #888;
    border-radius: 4px;
}

::-webkit-scrollbar-thumb:hover {
    background: #555;
}
</style>
