<template>
    <div>
        <v-row>
            <v-col cols="12">
                <v-card class="mx-auto" max-width="800">
                    <v-card-title class="text-h4 text-center">
                        مرحباً بك في المشروع التعليمي
                    </v-card-title>
                    <v-card-text>
                        <p class="text-h6 text-center">Laravel + Vue 3 + Vuetify</p>
                        <v-divider class="my-4"></v-divider>
                        
                        <v-alert type="success" variant="tonal" class="mb-4">
                            تم تشغيل المشروع بنجاح! 🎉
                        </v-alert>

                        <v-list>
                            <v-list-item>
                                <template #prepend>
                                    <v-icon color="primary">mdi-vuejs</v-icon>
                                </template>
                                <v-list-item-title>Vue 3 - جاهز</v-list-item-title>
                            </v-list-item>

                            <v-list-item>
                                <template #prepend>
                                    <v-icon color="success">mdi-material-design</v-icon>
                                </template>
                                <v-list-item-title>Vuetify 3 - جاهز</v-list-item-title>
                            </v-list-item>

                            <v-list-item>
                                <template #prepend>
                                    <v-icon color="error">mdi-laravel</v-icon>
                                </template>
                                <v-list-item-title>Laravel 11 - جاهز</v-list-item-title>
                            </v-list-item>
                        </v-list>

                        <v-btn 
                            color="primary" 
                            block 
                            class="mt-4"
                            @click="$router.push('/about')"
                        >
                            الذهاب لصفحة About
                        </v-btn>
                    </v-card-text>
                </v-card>
            </v-col>
        </v-row>
    </div>
</template>

<script setup>
// Logic هنا
</script>