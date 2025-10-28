<template>
    <v-app :rtl="true">
        <v-app-bar color="primary" prominent>
            <v-toolbar-title>مشروع تعليمي - Laravel + Vue</v-toolbar-title>
            <v-spacer></v-spacer>
            <v-btn icon>
                <v-icon>mdi-magnify</v-icon>
            </v-btn>
            <v-app-bar-nav-icon></v-app-bar-nav-icon>
        </v-app-bar>

        <v-main>
            <v-container>
                <router-view></router-view>
            </v-container>
        </v-main>

        <v-footer app>
            <v-spacer></v-spacer>
            <div>© 2024 - مشروع تعليمي</div>
            <v-spacer></v-spacer>
        </v-footer>
    </v-app>
</template>

<script setup>
import { useRtl } from 'vuetify';

// تفعيل RTL
const { isRtl } = useRtl();
isRtl.value = true;
</script>