<template>
  <div>
    <v-row>
      <v-col cols="12">
        <v-card class="mx-auto" max-width="800">
          <v-card-title class="text-h4 text-center">
            {{ t("app.welcome") }}
          </v-card-title>
          <v-card-text>
            <p class="text-h6 text-center">{{ t("app.subtitle") }}</p>
            <v-divider class="my-4"></v-divider>

            <v-alert type="success" variant="tonal" class="mb-4">
              {{ t("app.success_message") }}
            </v-alert>

            <v-list>
              <v-list-item>
                <template #prepend>
                  <v-icon color="primary">mdi-vuejs</v-icon>
                </template>
                <v-list-item-title>
                  {{ t("home.vue_ready") }}
                </v-list-item-title>
              </v-list-item>

              <v-list-item>
                <template #prepend>
                  <v-icon color="success">mdi-material-design</v-icon>
                </template>
                <v-list-item-title>
                  {{ t("home.vuetify_ready") }}
                </v-list-item-title>
              </v-list-item>

              <v-list-item>
                <template #prepend>
                  <v-icon color="error">mdi-laravel</v-icon>
                </template>
                <v-list-item-title>
                  {{ t("home.laravel_ready") }}
                </v-list-item-title>
              </v-list-item>
            </v-list>

            <v-btn
              color="primary"
              block
              class="mt-4"
              @click="$router.push('/about')"
            >
              {{ t("home.go_to_about") }}
            </v-btn>
          </v-card-text>
        </v-card>
      </v-col>
    </v-row>
  </div>
</template>

<script setup>
import { useI18n } from "vue-i18n";

const { t } = useI18n();
</script>
