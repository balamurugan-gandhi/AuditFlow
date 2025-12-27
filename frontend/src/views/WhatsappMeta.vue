<template>
    <div class="max-w-4xl mx-auto py-8">
        <Button icon="pi pi-arrow-left" label="Back to Plugins" class="mb-12" @click="goBack" />

        <h2 class="text-2xl font-bold mb-6 flex items-center gap-2">
            <i class="pi pi-whatsapp text-success text-2xl"></i>
            WhatsApp Meta API Settings
        </h2>
        <div class="card bg-white dark:bg-surface-800 rounded-xl shadow-sm p-6">
            <p class="mb-4 text-surface-700 dark:text-surface-300">Configure your WhatsApp Meta API credentials below.</p>
            <!-- Reuse the WhatsApp settings UI from Settings.vue -->
            <div class="space-y-4">
                <div class="space-y-2">
                    <label class="block text-sm font-medium text-surface-700 dark:text-surface-300">Access Token</label>
                    <InputText v-model="settings.whatsapp_access_token" class="w-full" />
                </div>
                <div class="space-y-2">
                    <label class="block text-sm font-medium text-surface-700 dark:text-surface-300">Phone Number ID</label>
                    <InputText v-model="settings.whatsapp_phone_number_id" class="w-full" />
                </div>
                <div class="space-y-2">
                    <label class="block text-sm font-medium text-surface-700 dark:text-surface-300">Business Account ID</label>
                    <InputText v-model="settings.whatsapp_business_account_id" class="w-full" />
                </div>
                <div class="pt-2">
                    <Button label="Save Configuration" icon="pi pi-save" :loading="saving" @click="saveSettings" />
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { useRouter } from 'vue-router';
import { ref, onMounted } from 'vue';
import api from '../api/axios';
import Button from 'primevue/button';
import InputText from 'primevue/inputtext';

const router = useRouter();
function goBack() {
    router.push('/plugins');
}

const saving = ref(false);
const settings = ref({
    whatsapp_access_token: '',
    whatsapp_phone_number_id: '',
    whatsapp_business_account_id: ''
});

const fetchSettings = async () => {
    try {
        const response = await api.get('/settings');
        const data = response.data;
        settings.value = {
            whatsapp_access_token: data.whatsapp_access_token || '',
            whatsapp_phone_number_id: data.whatsapp_phone_number_id || '',
            whatsapp_business_account_id: data.whatsapp_business_account_id || ''
        };
    } catch (error) {
        console.error('Error fetching settings:', error);
    }
};

const saveSettings = async () => {
    saving.value = true;
    try {
        await api.post('/settings', { settings: settings.value });
        // Optionally show a toast here
    } catch (error) {
        console.error('Error saving settings:', error);
    } finally {
        saving.value = false;
    }
};

onMounted(() => {
    fetchSettings();
});
</script>
