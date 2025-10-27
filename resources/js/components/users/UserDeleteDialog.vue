<!-- resources/js/components/users/UserDeleteDialog.vue -->
<template>
    <v-dialog
        :model-value="modelValue"
        @update:model-value="$emit('update:modelValue', $event)"
        max-width="500px"
    >
        <v-card>
            <!-- Header -->
            <v-card-title class="d-flex align-center bg-error">
                <v-icon icon="mdi-alert-circle" class="me-2" color="white"></v-icon>
                <span class="text-white">تأكيد الحذف</span>
            </v-card-title>

            <!-- Content -->
            <v-card-text class="pt-6">
                <p class="text-h6 mb-4">
                    هل أنت متأكد من حذف المستخدم؟
                </p>
                
                <v-alert type="warning" variant="tonal" class="mb-4">
                    <div class="d-flex align-center">
                        <v-icon icon="mdi-account" class="me-3"></v-icon>
                        <div>
                            <div class="font-weight-bold">{{ user?.name }}</div>
                            <div class="text-caption">{{ user?.email }}</div>
                        </div>
                    </div>
                </v-alert>

                <v-alert type="error" variant="tonal">
                    <p class="mb-0">
                        <v-icon icon="mdi-alert" class="me-2"></v-icon>
                        <strong>تحذير:</strong> لا يمكن التراجع عن هذا الإجراء!
                    </p>
                </v-alert>

                <!-- رسالة خطأ -->
                <v-alert
                    v-if="error"
                    type="error"
                    variant="tonal"
                    class="mt-4"
                    closable
                    @click:close="error = ''"
                >
                    {{ error }}
                </v-alert>
            </v-card-text>

            <v-divider></v-divider>

            <!-- Actions -->
            <v-card-actions class="pa-4">
                <v-spacer></v-spacer>
                <v-btn
                    variant="text"
                    @click="closeDialog"
                    :disabled="loading"
                >
                    إلغاء
                </v-btn>
                <v-btn
                    color="error"
                    variant="elevated"
                    @click="handleDelete"
                    :loading="loading"
                >
                    <v-icon start>mdi-delete</v-icon>
                    حذف
                </v-btn>
            </v-card-actions>
        </v-card>
    </v-dialog>
</template>

<script setup>
import { ref } from 'vue';
import { useUserStore } from '@/stores/user';

/**
 * Props
 */
const props = defineProps({
    modelValue: {
        type: Boolean,
        required: true,
    },
    user: {
        type: Object,
        default: null,
    },
});

/**
 * Emits
 */
const emit = defineEmits(['update:modelValue', 'deleted']);

// ====================================
// Store
// ====================================
const userStore = useUserStore();

// ====================================
// Reactive Data
// ====================================
const loading = ref(false);
const error = ref('');

// ====================================
// Methods
// ====================================

/**
 * معالجة الحذف
 */
async function handleDelete() {
    if (!props.user?.id) return;

    loading.value = true;
    error.value = '';

    try {
        const result = await userStore.deleteUser(props.user.id);

        if (result.success) {
            // النجاح
            emit('deleted');
            closeDialog();
        } else {
            // فشل
            error.value = result.message || 'فشل حذف المستخدم';
        }
    } catch (err) {
        error.value = 'حدث خطأ غير متوقع';
    } finally {
        loading.value = false;
    }
}

/**
 * إغلاق النموذج
 */
function closeDialog() {
    error.value = '';
    emit('update:modelValue', false);
}
</script>