<!-- resources/js/views/dashboard/DashboardView.vue -->
<template>
    <dashboard-layout>
        <v-container fluid>
            <!-- Header -->
            <v-row class="mb-4">
                <v-col cols="12">
                    <h1 class="text-h4 font-weight-bold">لوحة التحكم</h1>
                    <p class="text-subtitle-1 text-medium-emphasis">
                        مرحباً بك، {{ user?.name }}
                    </p>
                </v-col>
            </v-row>

            <!-- Stats Cards -->
            <v-row>
                <v-col cols="12" sm="6" md="3">
                    <v-card>
                        <v-card-text>
                            <div class="d-flex justify-space-between align-center">
                                <div>
                                    <p class="text-subtitle-2 text-medium-emphasis">المنتجات</p>
                                    <h3 class="text-h4 font-weight-bold">0</h3>
                                </div>
                                <v-avatar color="primary" size="56">
                                    <v-icon icon="mdi-package-variant-closed" size="32"></v-icon>
                                </v-avatar>
                            </div>
                        </v-card-text>
                    </v-card>
                </v-col>

                <v-col cols="12" sm="6" md="3">
                    <v-card>
                        <v-card-text>
                            <div class="d-flex justify-space-between align-center">
                                <div>
                                    <p class="text-subtitle-2 text-medium-emphasis">الفئات</p>
                                    <h3 class="text-h4 font-weight-bold">0</h3>
                                </div>
                                <v-avatar color="success" size="56">
                                    <v-icon icon="mdi-folder-multiple" size="32"></v-icon>
                                </v-avatar>
                            </div>
                        </v-card-text>
                    </v-card>
                </v-col>

                <v-col cols="12" sm="6" md="3">
                    <v-card>
                        <v-card-text>
                            <div class="d-flex justify-space-between align-center">
                                <div>
                                    <p class="text-subtitle-2 text-medium-emphasis">الطلبات</p>
                                    <h3 class="text-h4 font-weight-bold">0</h3>
                                </div>
                                <v-avatar color="info" size="56">
                                    <v-icon icon="mdi-cart" size="32"></v-icon>
                                </v-avatar>
                            </div>
                        </v-card-text>
                    </v-card>
                </v-col>

                <v-col cols="12" sm="6" md="3">
                    <v-card>
                        <v-card-text>
                            <div class="d-flex justify-space-between align-center">
                                <div>
                                    <p class="text-subtitle-2 text-medium-emphasis">المستخدمين</p>
                                    <h3 class="text-h4 font-weight-bold">4</h3>
                                </div>
                                <v-avatar color="warning" size="56">
                                    <v-icon icon="mdi-account-multiple" size="32"></v-icon>
                                </v-avatar>
                            </div>
                        </v-card-text>
                    </v-card>
                </v-col>
            </v-row>

            <!-- Welcome Card -->
            <v-row class="mt-4">
                <v-col cols="12">
                    <v-card color="primary" dark>
                        <v-card-text class="pa-6">
                            <v-row align="center">
                                <v-col cols="12" md="8">
                                    <h2 class="text-h5 font-weight-bold mb-2">
                                        🎉 مرحباً بك في نظام التجارة الإلكترونية
                                    </h2>
                                    <p class="text-body-1">
                                        هذا هو الدرس الأول. المشروع الآن يعمل بنجاح!
                                    </p>
                                    <p class="text-body-2 mt-2">
                                        الخطوات القادمة: إضافة المنتجات، الفئات، والطلبات.
                                    </p>
                                </v-col>
                                <v-col cols="12" md="4" class="text-center">
                                    <v-icon icon="mdi-check-circle" size="100"></v-icon>
                                </v-col>
                            </v-row>
                        </v-card-text>
                    </v-card>
                </v-col>
            </v-row>
        </v-container>
    </dashboard-layout>
</template>

<script setup>
import { computed } from 'vue';
import { useAuthStore } from '@/stores/auth';
import DashboardLayout from '@/layouts/DashboardLayout.vue';

const authStore = useAuthStore();
const user = computed(() => authStore.currentUser);
</script>
