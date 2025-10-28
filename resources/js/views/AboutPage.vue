<template>
    <div>
        <v-card>
            <v-card-title>حول المشروع</v-card-title>
            <v-card-text>
                <p>هذا مشروع تعليمي لتعلم Laravel مع Vue 3</p>
                
                <v-btn color="primary" @click="$router.push('/')">
                    العودة للرئيسية
                </v-btn>
            </v-card-text>
        </v-card>
    </div>
</template>