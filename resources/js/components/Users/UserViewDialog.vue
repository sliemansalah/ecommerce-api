<template>
    <v-dialog
        :model-value="modelValue"
        @update:model-value="$emit('update:modelValue', $event)"
        max-width="600"
    >
        <v-card v-if="user">
            <v-card-title class="bg-primary">
                <span class="text-h5 text-white">
                    <v-icon color="white" class="me-2">mdi-account-details</v-icon>
                    {{ t('users.userDetails') }}
                </span>
            </v-card-title>

            <v-divider></v-divider>

            <v-card-text class="pa-6">
                <!-- الصورة والمعلومات الأساسية -->
                <div class="text-center mb-6">
                    <v-avatar
                        :color="user.avatar ? '' : 'primary'"
                        size="100"
                        class="mb-4"
                    >
                        <v-img v-if="user.avatar" :src="user.avatar"></v-img>
                        <span v-else class="text-h3 text-white">{{ user.initials }}</span>
                    </v-avatar>
                    <h2 class="text-h5 mb-2">{{ user.name }}</h2>
                    <v-chip
                        :color="user.is_active ? 'success' : 'error'"
                        size="small"
                    >
                        {{ user.is_active ? t('common.active') : t('common.inactive') }}
                    </v-chip>
                </div>

                <!-- المعلومات التفصيلية -->
                <v-list lines="two">
                    <v-list-item>
                        <template v-slot:prepend>
                            <v-icon color="primary">mdi-email</v-icon>
                        </template>
                        <v-list-item-title>{{ t('users.email') }}</v-list-item-title>
                        <v-list-item-subtitle>{{ user.email }}</v-list-item-subtitle>
                    </v-list-item>

                    <v-list-item v-if="user.phone">
                        <template v-slot:prepend>
                            <v-icon color="primary">mdi-phone</v-icon>
                        </template>
                        <v-list-item-title>{{ t('users.phone') }}</v-list-item-title>
                        <v-list-item-subtitle>{{ user.phone }}</v-list-item-subtitle>
                    </v-list-item>

                    <v-list-item>
                        <template v-slot:prepend>
                            <v-icon color="primary">mdi-shield-account</v-icon>
                        </template>
                        <v-list-item-title>{{ t('users.roles') }}</v-list-item-title>
                        <v-list-item-subtitle>
                            <v-chip
                                v-for="role in user.roles"
                                :key="role.id"
                                size="small"
                                class="me-1 mt-1"
                                :color="getRoleColor(role.name)"
                            >
                                {{ role.display_name }}
                            </v-chip>
                        </v-list-item-subtitle>
                    </v-list-item>

                    <v-list-item>
                        <template v-slot:prepend>
                            <v-icon color="primary">mdi-clock-outline</v-icon>
                        </template>
                        <v-list-item-title>{{ t('users.lastLogin') }}</v-list-item-title>
                        <v-list-item-subtitle>
                            {{ user.last_login_at || t('users.neverLoggedIn') }}
                        </v-list-item-subtitle>
                    </v-list-item>

                    <v-list-item>
                        <template v-slot:prepend>
                            <v-icon color="primary">mdi-calendar</v-icon>
                        </template>
                        <v-list-item-title>{{ t('users.createdAt') }}</v-list-item-title>
                        <v-list-item-subtitle>{{ user.created_at }}</v-list-item-subtitle>
                    </v-list-item>
                </v-list>

                <!-- الصلاحيات المباشرة (إن وجدت) -->
                <div v-if="user.permissions && user.permissions.length > 0" class="mt-4">
                    <v-divider class="mb-4"></v-divider>
                    <h3 class="text-h6 mb-3">
                        <v-icon class="me-2">mdi-key</v-icon>
                        {{ t('users.directPermissions') }}
                    </h3>
                    <v-chip
                        v-for="permission in user.permissions"
                        :key="permission.id"
                        size="small"
                        class="me-1 mb-1"
                        color="info"
                    >
                        {{ permission.display_name }}
                    </v-chip>
                </div>
            </v-card-text>

            <v-divider></v-divider>

            <v-card-actions class="pa-4">
                <v-spacer></v-spacer>
                <v-btn
                    color="primary"
                    @click="$emit('update:modelValue', false)"
                >
                    {{ t('common.close') }}
                </v-btn>
            </v-card-actions>
        </v-card>
    </v-dialog>
</template>

<script setup>
import { useLanguage } from '../../composables/useLanguage';

defineProps({
    modelValue: Boolean,
    user: Object,
});

defineEmits(['update:modelValue']);

const { t } = useLanguage();

// ألوان الأدوار
const getRoleColor = (roleName) => {
    const colors = {
        'super-admin': 'error',
        'admin': 'warning',
        'manager': 'info',
        'employee': 'success',
        'customer': 'primary',
    };
    return colors[roleName] || 'default';
};
</script>