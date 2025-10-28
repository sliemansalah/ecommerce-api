<template>
    <div>
        <v-row justify="center">
            <v-col cols="12" md="10" lg="8">
                <v-card elevation="2" class="rounded-lg">
                    <v-card-title class="text-primary">
                        {{ t('about.title') }}
                    </v-card-title>

                    <v-card-text>
                        <p class="mb-4">
                            {{ t('about.description') }}
                        </p>

                        <v-divider class="my-4"></v-divider>

                        <h3 class="text-h6 mb-3 font-weight-bold">
                            {{ t('about.features') }}
                        </h3>
                        
                        <v-list class="bg-transparent">
                            <v-list-item class="px-0 mb-2">
                                <template #prepend>
                                    <v-avatar color="primary" size="40">
                                        <v-icon color="white" size="20">mdi-translate</v-icon>
                                    </v-avatar>
                                </template>
                                <v-list-item-title class="font-weight-bold">
                                    {{ t('about.feature_1') }}
                                </v-list-item-title>
                                <v-list-item-subtitle>
                                    {{ t('about.feature_1_desc') }}
                                </v-list-item-subtitle>
                            </v-list-item>

                            <v-list-item class="px-0 mb-2">
                                <template #prepend>
                                    <v-avatar color="success" size="40">
                                        <v-icon color="white" size="20">mdi-lightning-bolt</v-icon>
                                    </v-avatar>
                                </template>
                                <v-list-item-title class="font-weight-bold">
                                    {{ t('about.feature_2') }}
                                </v-list-item-title>
                                <v-list-item-subtitle>
                                    {{ t('about.feature_2_desc') }}
                                </v-list-item-subtitle>
                            </v-list-item>

                            <v-list-item class="px-0 mb-2">
                                <template #prepend>
                                    <v-avatar color="warning" size="40">
                                        <v-icon color="white" size="20">mdi-content-save</v-icon>
                                    </v-avatar>
                                </template>
                                <v-list-item-title class="font-weight-bold">
                                    {{ t('about.feature_3') }}
                                </v-list-item-title>
                                <v-list-item-subtitle>
                                    {{ t('about.feature_3_desc') }}
                                </v-list-item-subtitle>
                            </v-list-item>

                            <v-list-item class="px-0 mb-2">
                                <template #prepend>
                                    <v-avatar color="info" size="40">
                                        <v-icon color="white" size="20">mdi-format-textdirection-r-to-l</v-icon>
                                    </v-avatar>
                                </template>
                                <v-list-item-title class="font-weight-bold">
                                    {{ t('about.feature_4') }}
                                </v-list-item-title>
                                <v-list-item-subtitle>
                                    {{ t('about.feature_4_desc') }}
                                </v-list-item-subtitle>
                            </v-list-item>
                        </v-list>

                        <v-divider class="my-4"></v-divider>

                        <v-btn 
                            color="primary" 
                            rounded="lg"
                            elevation="1"
                            @click="$router.push('/')"
                        >
                            <v-icon start size="small">mdi-home</v-icon>
                            {{ t('about.back_home') }}
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