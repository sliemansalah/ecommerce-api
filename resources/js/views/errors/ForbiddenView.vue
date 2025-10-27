<!-- resources/js/views/errors/ForbiddenView.vue -->
<template>
    <v-container fluid class="fill-height">
        <v-row align="center" justify="center">
            <v-col cols="12" sm="8" md="6" class="text-center">
                <v-icon icon="mdi-lock" size="120" color="error" class="mb-6"></v-icon>
                <h1 class="text-h3 font-weight-bold mb-4">403 - غير مصرح</h1>
                <p class="text-h6 text-medium-emphasis mb-6">
                    عذراً، ليس لديك صلاحية للوصول لهذه الصفحة
                </p>
                <v-btn color="primary" size="large" to="/dashboard">
                    <v-icon start>mdi-home</v-icon>
                    العودة للرئيسية
                </v-btn>
            </v-col>
        </v-row>
    </v-container>
</template>
