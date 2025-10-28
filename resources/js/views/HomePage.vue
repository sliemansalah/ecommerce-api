<template>
    <div>
        <v-row justify="center">
            <v-col cols="12" md="10" lg="8">
                <v-card elevation="2" class="rounded-lg">
                    <v-card-title class="text-center text-primary">
                        {{ t('app.welcome') }}
                    </v-card-title>
                    
                    <v-card-subtitle class="text-center mt-1 mb-3">
                        {{ t('app.subtitle') }}
                    </v-card-subtitle>

                    <v-card-text>
                        <v-alert 
                            type="success" 
                            variant="tonal" 
                            density="comfortable"
                            class="mb-4"
                        >
                            {{ t('app.success_message') }}
                        </v-alert>

                        <v-divider class="my-4"></v-divider>

                        <v-list class="bg-transparent">
                            <v-list-item class="px-0 mb-2">
                                <template #prepend>
                                    <v-avatar color="primary" size="40">
                                        <v-icon color="white" size="20">mdi-vuejs</v-icon>
                                    </v-avatar>
                                </template>
                                <v-list-item-title class="font-weight-bold">
                                    {{ t('home.vue_ready') }}
                                </v-list-item-title>
                                <v-list-item-subtitle>
                                    {{ t('home.vue_desc') }}
                                </v-list-item-subtitle>
                            </v-list-item>

                            <v-list-item class="px-0 mb-2">
                                <template #prepend>
                                    <v-avatar color="success" size="40">
                                        <v-icon color="white" size="20">mdi-material-design</v-icon>
                                    </v-avatar>
                                </template>
                                <v-list-item-title class="font-weight-bold">
                                    {{ t('home.vuetify_ready') }}
                                </v-list-item-title>
                                <v-list-item-subtitle>
                                    {{ t('home.vuetify_desc') }}
                                </v-list-item-subtitle>
                            </v-list-item>

                            <v-list-item class="px-0 mb-2">
                                <template #prepend>
                                    <v-avatar color="error" size="40">
                                        <v-icon color="white" size="20">mdi-laravel</v-icon>
                                    </v-avatar>
                                </template>
                                <v-list-item-title class="font-weight-bold">
                                    {{ t('home.laravel_ready') }}
                                </v-list-item-title>
                                <v-list-item-subtitle>
                                    {{ t('home.laravel_desc') }}
                                </v-list-item-subtitle>
                            </v-list-item>
                        </v-list>

                        <v-divider class="my-4"></v-divider>

                        <v-btn 
                            color="primary" 
                            block 
                            rounded="lg"
                            elevation="1"
                            @click="$router.push('/about')"
                        >
                            <v-icon start size="small">mdi-information</v-icon>
                            {{ t('home.go_to_about') }}
                        </v-btn>
                    </v-card-text>
                </v-card>
            </v-col>
        </v-row>
    </div>
</template>

<script setup>
import { useI18n } from 'vue-i18n';

const { t } = useI18n();
</script>