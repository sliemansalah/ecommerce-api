<template>
    <v-container>
        <v-row>
            <v-col cols="12">
                <v-card elevation="2" rounded="lg">
                    <v-card-title class="text-h4 py-6 bg-primary text-white">
                        <v-icon size="large" class="me-3">mdi-account</v-icon>
                        {{ t('auth.profile.title') }}
                    </v-card-title>
                    
                    <v-card-text class="pa-6">
                        <v-row>
                            <!-- Avatar -->
                            <v-col cols="12" class="text-center mb-4">
                                <v-avatar color="primary" size="120">
                                    <span class="text-h3 text-white font-weight-bold">
                                        {{ user?.initials }}
                                    </span>
                                </v-avatar>
                            </v-col>

                            <!-- معلومات المستخدم -->
                            <v-col cols="12" md="6">
                                <v-text-field
                                    v-model="userData.name"
                                    :label="t('auth.profile.name')"
                                    prepend-inner-icon="mdi-account"
                                    variant="outlined"
                                    :readonly="!isEditing"
                                    :disabled="!isEditing"
                                ></v-text-field>
                            </v-col>

                            <v-col cols="12" md="6">
                                <v-text-field
                                    v-model="userData.email"
                                    :label="t('auth.profile.email')"
                                    prepend-inner-icon="mdi-email"
                                    variant="outlined"
                                    :readonly="!isEditing"
                                    :disabled="!isEditing"
                                ></v-text-field>
                            </v-col>

                            <v-col cols="12" md="6">
                                <v-text-field
                                    v-model="userData.phone"
                                    :label="t('auth.profile.phone')"
                                    prepend-inner-icon="mdi-phone"
                                    variant="outlined"
                                    :readonly="!isEditing"
                                    :disabled="!isEditing"
                                ></v-text-field>
                            </v-col>

                            <v-col cols="12" md="6">
                                <v-text-field
                                    :value="user?.last_login_at"
                                    :label="t('auth.profile.lastLogin')"
                                    prepend-inner-icon="mdi-clock"
                                    variant="outlined"
                                    readonly
                                    disabled
                                ></v-text-field>
                            </v-col>
                        </v-row>
                    </v-card-text>

                    <v-card-actions class="pa-6 pt-0">
                        <v-spacer></v-spacer>
                        <v-btn
                            v-if="!isEditing"
                            color="primary"
                            prepend-icon="mdi-pencil"
                            @click="startEdit"
                        >
                            {{ t('auth.profile.edit') }}
                        </v-btn>
                        <template v-else>
                            <v-btn
                                variant="text"
                                @click="cancelEdit"
                            >
                                {{ t('auth.profile.cancel') }}
                            </v-btn>
                            <v-btn
                                color="primary"
                                prepend-icon="mdi-content-save"
                                @click="saveProfile"
                                :loading="loading"
                            >
                                {{ t('auth.profile.save') }}
                            </v-btn>
                        </template>
                    </v-card-actions>
                </v-card>
            </v-col>
        </v-row>
    </v-container>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import { useLanguage } from '../composables/useLanguage';
import { useAuth } from '../composables/useAuth';
import { useSnackbar } from '../composables/useSnackbar';

const { t } = useLanguage();
const { user, loading, updateProfile } = useAuth();
const { showSuccess, showError } = useSnackbar();

const isEditing = ref(false);
const userData = ref({
    name: '',
    email: '',
    phone: '',
});

const startEdit = () => {
    userData.value = {
        name: user.value.name,
        email: user.value.email,
        phone: user.value.phone,
    };
    isEditing.value = true;
};

const cancelEdit = () => {
    isEditing.value = false;
    userData.value = {
        name: user.value.name,
        email: user.value.email,
        phone: user.value.phone,
    };
};

const saveProfile = async () => {
    try {
        await updateProfile(userData.value);
        isEditing.value = false;
        showSuccess('تم تحديث الملف الشخصي بنجاح');
    } catch (error) {
        console.error('Update profile error:', error);
        showError('فشل تحديث الملف الشخصي');
    }
};

onMounted(() => {
    if (user.value) {
        userData.value = {
            name: user.value.name,
            email: user.value.email,
            phone: user.value.phone,
        };
    }
});
</script>