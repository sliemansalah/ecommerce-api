<template>
    <div>
        <v-card>
            <v-card-title class="text-h4">{{ t('about.title') }}</v-card-title>
            <v-card-text>
                <p class="text-body-1 mb-4">{{ t('about.description') }}</p>

                <v-divider class="my-4"></v-divider>

                <h3 class="text-h6 mb-3">{{ t('about.features') }}</h3>
                
                <v-list>
                    <v-list-item>
                        <template #prepend>
                            <v-icon color="primary">mdi-translate</v-icon>
                        </template>
                        <v-list-item-title>
                            {{ t('about.feature_1') }}
                        </v-list-item-title>
                    </v-list-item>

                    <v-list-item>
                        <template #prepend>
                            <v-icon color="success">mdi-lightning-bolt</v-icon>
                        </template>
                        <v-list-item-title>
                            {{ t('about.feature_2') }}
                        </v-list-item-title>
                    </v-list-item>

                    <v-list-item>
                        <template #prepend>
                            <v-icon color="warning">mdi-content-save</v-icon>
                        </template>
                        <v-list-item-title>
                            {{ t('about.feature_3') }}
                        </v-list-item-title>
                    </v-list-item>

                    <v-list-item>
                        <template #prepend>
                            <v-icon color="info">mdi-format-textdirection-r-to-l</v-icon>
                        </template>
                        <v-list-item-title>
                            {{ t('about.feature_4') }}
                        </v-list-item-title>
                    </v-list-item>
                </v-list>

                <v-btn 
                    color="primary" 
                    class="mt-4"
                    @click="$router.push('/')"
                >
                    {{ t('about.back_home') }}
                </v-btn>
            </v-card-text>
        </v-card>
    </div>
</template>

<script setup>
import { useI18n } from 'vue-i18n';

const { t } = useI18n();
</script>