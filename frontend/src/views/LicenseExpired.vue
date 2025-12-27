<template>
    <div class="min-h-screen flex items-center justify-center bg-surface-50 dark:bg-surface-950 px-4">
        <div class="max-w-md w-full bg-white dark:bg-surface-900 rounded-2xl shadow-xl p-8 text-center border border-surface-200 dark:border-surface-800">
            <div class="w-20 h-20 bg-red-100 dark:bg-red-500/10 rounded-full flex items-center justify-center mx-auto mb-6">
                <i class="pi pi-lock text-red-600 dark:text-red-400 text-3xl"></i>
            </div>
            
            <h1 class="text-2xl font-bold text-surface-900 dark:text-surface-0 mb-2">Application Locked</h1>
            <p class="text-surface-500 dark:text-surface-400 mb-8">
                Your application license has expired or is invalid. Please contact your administrator to renew your license.
            </p>
            
            <div class="space-y-4">
                <div class="p-4 bg-surface-50 dark:bg-surface-800 rounded-xl border border-surface-100 dark:border-surface-700 text-left">
                    <label class="text-xs font-semibold text-surface-500 dark:text-surface-400 uppercase tracking-wider mb-2 block">
                        Update License Key
                    </label>
                    <div class="flex flex-col gap-3">
                        <textarea 
                            v-model="licenseKey" 
                            rows="4" 
                            class="w-full p-3 text-sm rounded-lg border border-surface-200 dark:border-surface-700 bg-white dark:bg-surface-900 text-surface-900 dark:text-surface-0 focus:ring-2 focus:ring-primary-500 outline-none transition-all"
                            placeholder="Paste your new license key here..."
                        ></textarea>
                        <button 
                            @click="updateLicense" 
                            :disabled="loading || !licenseKey"
                            class="w-full py-2.5 bg-primary-600 hover:bg-primary-700 disabled:opacity-50 text-white font-semibold rounded-lg transition-colors flex items-center justify-center gap-2"
                        >
                            <i v-if="loading" class="pi pi-spin pi-spinner"></i>
                            {{ loading ? 'Updating...' : 'Update License' }}
                        </button>
                    </div>
                </div>
                
                <button @click="logout" class="text-sm text-surface-500 hover:text-surface-700 dark:hover:text-surface-300 transition-colors">
                    Back to Login
                </button>
            </div>
        </div>
        <Toast />
    </div>
</template>

<script setup>
import { ref } from 'vue';
import { useRouter } from 'vue-router';
import api from '../api/axios';
import { useToast } from 'primevue/usetoast';
import Toast from 'primevue/toast';

const router = useRouter();
const toast = useToast();
const licenseKey = ref('');
const loading = ref(false);

const updateLicense = async () => {
    loading.value = true;
    try {
        await api.post('/license/update', { license_key: licenseKey.value });
        toast.add({ severity: 'success', summary: 'Success', detail: 'License updated successfully. Refreshing...', life: 3000 });
        setTimeout(() => {
            window.location.href = '/';
        }, 2000);
    } catch (error) {
        console.error('Update failed:', error);
        toast.add({ severity: 'error', summary: 'Error', detail: error.response?.data?.message || 'Failed to update license key.', life: 3000 });
    } finally {
        loading.value = false;
    }
};

const logout = () => {
    localStorage.removeItem('token');
    localStorage.removeItem('user');
    router.push('/login');
};
</script>
