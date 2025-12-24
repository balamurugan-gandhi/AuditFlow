<template>
    <div class="space-y-6">
        <Toast />
        <div class="flex justify-end items-center">
            <Button label="Back" icon="pi pi-arrow-left" text @click="router.back()" />
        </div>

        <div v-if="loading" class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- Basic Information Skeleton -->
            <div class="lg:col-span-2 space-y-6">
                <div class="card bg-white dark:bg-surface-800 rounded-xl shadow-sm p-6 border border-surface-200 dark:border-surface-700">
                    <div class="flex justify-between items-start mb-6">
                        <div>
                            <Skeleton width="16rem" height="2rem" class="mb-2"></Skeleton>
                            <Skeleton width="8rem" height="1rem"></Skeleton>
                        </div>
                        <Skeleton width="6rem" height="2rem" borderRadius="16px"></Skeleton>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div class="info-item" v-for="i in 4" :key="i">
                            <Skeleton width="6rem" height="1rem" class="mb-2"></Skeleton>
                            <Skeleton width="100%" height="1.5rem"></Skeleton>
                        </div>
                        <div class="info-item md:col-span-2">
                            <Skeleton width="6rem" height="1rem" class="mb-2"></Skeleton>
                            <Skeleton width="100%" height="1.5rem"></Skeleton>
                        </div>
                    </div>
                </div>

                <!-- Files Information Skeleton -->
                <div class="card bg-white dark:bg-surface-800 rounded-xl shadow-sm p-6 border border-surface-200 dark:border-surface-700">
                    <Skeleton width="10rem" height="1.5rem" class="mb-4"></Skeleton>
                    <div class="space-y-4">
                        <Skeleton width="100%" height="3rem" v-for="i in 3" :key="i"></Skeleton>
                    </div>
                </div>
            </div>

            <!-- Contact Section Skeleton -->
            <div class="space-y-6">
                <div class="card bg-white dark:bg-surface-800 rounded-xl shadow-sm p-6 border border-surface-200 dark:border-surface-700">
                    <Skeleton width="10rem" height="1.5rem" class="mb-4"></Skeleton>
                    
                    <div class="space-y-4">
                        <div class="flex items-center gap-3 p-3 bg-surface-50 dark:bg-surface-900 rounded-lg" v-for="i in 3" :key="i">
                            <Skeleton shape="circle" size="2.5rem"></Skeleton>
                            <div class="flex-1">
                                <Skeleton width="5rem" height="0.8rem" class="mb-1"></Skeleton>
                                <Skeleton width="80%" height="1rem"></Skeleton>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div v-else-if="client" class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- Basic Information -->
            <div class="lg:col-span-2 space-y-6">
                <div class="card bg-white dark:bg-surface-800 rounded-xl shadow-sm p-6 border border-surface-200 dark:border-surface-700">
                    <div class="flex justify-between items-start mb-6">
                        <div>
                            <h3 class="text-xl font-bold text-surface-900 dark:text-surface-0 m-0">{{ client.business_name }}</h3>
                            <p class="text-surface-500 dark:text-surface-400 mt-1">{{ client.business_type || 'Business Type N/A' }}</p>
                        </div>
                        <Tag :value="client.filing_cycle || 'Cycle N/A'" severity="info" />
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div class="info-item">
                            <label class="block text-sm font-medium text-surface-500 dark:text-surface-400 mb-1">Contact Person</label>
                            <div class="text-surface-900 dark:text-surface-0 font-medium">{{ client.contact_person || 'N/A' }}</div>
                        </div>
                        <div class="info-item">
                            <label class="block text-sm font-medium text-surface-500 dark:text-surface-400 mb-1">PAN Number</label>
                            <div class="text-surface-900 dark:text-surface-0 font-medium">{{ client.pan_number || 'N/A' }}</div>
                        </div>
                        <div class="info-item">
                            <label class="block text-sm font-medium text-surface-500 dark:text-surface-400 mb-1">GST Number</label>
                            <div class="text-surface-900 dark:text-surface-0 font-medium">{{ client.gst_number || 'N/A' }}</div>
                        </div>
                        <div class="info-item">
                            <label class="block text-sm font-medium text-surface-500 dark:text-surface-400 mb-1">TIN Number</label>
                            <div class="text-surface-900 dark:text-surface-0 font-medium">{{ client.tin_number || 'N/A' }}</div>
                        </div>
                        <div class="info-item md:col-span-2">
                            <label class="block text-sm font-medium text-surface-500 dark:text-surface-400 mb-1">Address</label>
                            <div class="text-surface-900 dark:text-surface-0 font-medium">{{ client.address || 'N/A' }}</div>
                        </div>
                    </div>
                </div>

                <!-- Files Information -->
                <div class="card bg-white dark:bg-surface-800 rounded-xl shadow-sm p-6 border border-surface-200 dark:border-surface-700">
                    <div class="flex justify-between items-center mb-4">
                        <h3 class="text-lg font-bold text-surface-900 dark:text-surface-0 m-0">File History</h3>
                        <Dropdown v-model="selectedYear" :options="assessmentYears" placeholder="Select Year" class="w-48" />
                    </div>
                    <DataTable :value="filteredFiles" stripedRows class="p-datatable-sm" paginator :rows="5" :rowsPerPageOptions="[5, 10, 20]">
                        <template #empty>
                            <div class="text-center p-4">No files found for this client.</div>
                        </template>
                        <Column field="file_type" header="File Type"></Column>
                        <Column field="assessment_year" header="Year"></Column>
                        <Column field="status" header="Status">
                            <template #body="slotProps">
                                <Tag :value="slotProps.data.status" :severity="getStatusSeverity(slotProps.data.status)" />
                            </template>
                        </Column>
                        <Column field="assignee.name" header="Assigned To">
                            <template #body="slotProps">
                                {{ slotProps.data.assignee?.name || 'Unassigned' }}
                            </template>
                        </Column>
                        <Column header="Actions" style="width: 4rem">
                            <template #body="slotProps">
                                <Button icon="pi pi-eye" text rounded severity="secondary" @click="router.push(`/files/${slotProps.data.id}`)" />
                            </template>
                        </Column>
                    </DataTable>
                </div>
            </div>

            <!-- Contact Section -->
            <div class="space-y-6">
                <div class="card bg-white dark:bg-surface-800 rounded-xl shadow-sm p-6 border border-surface-200 dark:border-surface-700">
                    <h3 class="text-lg font-bold text-surface-900 dark:text-surface-0 mb-4">Contact Information</h3>
                    
                    <div class="space-y-4">
                        <div class="flex items-center gap-3 p-3 bg-surface-50 dark:bg-surface-900 rounded-lg">
                            <div class="w-10 h-10 rounded-full bg-blue-100 dark:bg-blue-900/30 flex items-center justify-center text-blue-600 dark:text-blue-400">
                                <i class="pi pi-phone text-lg"></i>
                            </div>
                            <div>
                                <label class="block text-xs font-medium text-surface-500 dark:text-surface-400">Phone Number</label>
                                <a :href="`tel:${client.phone}`" class="text-surface-900 dark:text-surface-0 font-medium hover:text-primary-500 transition-colors">
                                    {{ client.phone || 'N/A' }}
                                </a>
                            </div>
                        </div>

                        <div class="flex items-center gap-3 p-3 bg-surface-50 dark:bg-surface-900 rounded-lg">
                            <div class="w-10 h-10 rounded-full bg-green-100 dark:bg-green-900/30 flex items-center justify-center text-green-600 dark:text-green-400">
                                <i class="pi pi-whatsapp text-lg"></i>
                            </div>
                            <div>
                                <label class="block text-xs font-medium text-surface-500 dark:text-surface-400">WhatsApp</label>
                                <a v-if="client.whatsapp_number || client.phone" :href="`https://wa.me/${(client.whatsapp_number || client.phone).replace(/\D/g,'')}`" target="_blank" class="text-surface-900 dark:text-surface-0 font-medium hover:text-primary-500 transition-colors">
                                    {{ client.whatsapp_number || client.phone || 'N/A' }}
                                </a>
                                <span v-else class="text-surface-900 dark:text-surface-0 font-medium">N/A</span>
                            </div>
                        </div>

                        <div class="flex items-center gap-3 p-3 bg-surface-50 dark:bg-surface-900 rounded-lg">
                            <div class="w-10 h-10 rounded-full bg-orange-100 dark:bg-orange-900/30 flex items-center justify-center text-orange-600 dark:text-orange-400">
                                <i class="pi pi-envelope text-lg"></i>
                            </div>
                            <div>
                                <label class="block text-xs font-medium text-surface-500 dark:text-surface-400">Email Address</label>
                                <a :href="`mailto:${client.email}`" class="text-surface-900 dark:text-surface-0 font-medium hover:text-primary-500 transition-colors">
                                    {{ client.email || 'N/A' }}
                                </a>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Notification Settings -->
                <div class="card bg-white dark:bg-surface-800 rounded-xl shadow-sm p-6 border border-surface-200 dark:border-surface-700">
                    <h3 class="text-lg font-bold text-surface-900 dark:text-surface-0 mb-4">Notification Settings</h3>
                    
                    <div class="space-y-4">
                        <div class="flex items-center justify-between p-3 bg-surface-50 dark:bg-surface-900 rounded-lg">
                            <div class="flex items-center gap-3">
                                <div class="w-10 h-10 rounded-full bg-green-100 dark:bg-green-900/30 flex items-center justify-center text-green-600 dark:text-green-400">
                                    <i class="pi pi-whatsapp text-lg"></i>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-surface-900 dark:text-surface-0">WhatsApp Notifications</label>
                                    <span class="block text-xs text-surface-500 dark:text-surface-400">Receive updates via WhatsApp</span>
                                </div>
                            </div>
                            <InputSwitch v-model="client.whatsapp_notification_enabled" @change="updateNotificationSettings" />
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, onMounted, computed, nextTick } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import api from '../../api/axios';
import Button from 'primevue/button';
import Tag from 'primevue/tag';
import DataTable from 'primevue/datatable';
import Column from 'primevue/column';
import Skeleton from 'primevue/skeleton';
import Dropdown from 'primevue/dropdown';
import InputSwitch from 'primevue/inputswitch';
import Toast from 'primevue/toast';
import { useToast } from 'primevue/usetoast';

const route = useRoute();
const router = useRouter();
const toast = useToast();
const client = ref(null);
const loading = ref(true);
const currentYear = new Date().getFullYear();
const assessmentYears = ref([
    'All Years',
    `${currentYear}-${currentYear + 1}`,
    `${currentYear - 1}-${currentYear}`,
    `${currentYear - 2}-${currentYear - 1}`
]);
const selectedYear = ref('All Years');

const filteredFiles = computed(() => {
    if (!client.value || !client.value.files) return [];
    if (selectedYear.value === 'All Years') return client.value.files;
    return client.value.files.filter(file => file.assessment_year === selectedYear.value);
});

const fetchClient = async () => {
    try {
        const response = await api.get(`/clients/${route.params.id}`);
        client.value = response.data;
        // Ensure boolean type for switch
        if (client.value) {
            client.value.whatsapp_notification_enabled = !!client.value.whatsapp_notification_enabled;
        }
    } catch (error) {
        console.error('Error fetching client:', error);
        toast.add({ severity: 'error', summary: 'Error', detail: 'Failed to load client details', life: 3000 });
    } finally {
        loading.value = false;
    }
};

const updateNotificationSettings = async () => {
    if (client.value.whatsapp_notification_enabled && !client.value.whatsapp_number && !client.value.phone) {
        // Revert the toggle immediately in UI - usually needs a small delay for the component to register the v-model change first
        setTimeout(() => {
            client.value.whatsapp_notification_enabled = false;
        }, 200);
        
        toast.add({ 
            severity: 'warn', 
            summary: 'Missing Contact Info', 
            detail: 'Please add WhatsApp number to enable this notification setting', 
            life: 3000 
        });
        return;
    }

    try {
        await api.put(`/clients/${client.value.id}`, {
            whatsapp_notification_enabled: client.value.whatsapp_notification_enabled
        });
        toast.add({ severity: 'success', summary: 'Success', detail: 'Notification settings updated', life: 3000 });
    } catch (error) {
        console.error('Error updating settings:', error);
        // Revert change on error
        client.value.whatsapp_notification_enabled = !client.value.whatsapp_notification_enabled;
        toast.add({ severity: 'error', summary: 'Error', detail: 'Failed to update settings', life: 3000 });
    }
};

const getStatusSeverity = (status) => {
    const severityMap = {
        'received': 'secondary',
        'assigned': 'info',
        'in-progress': 'info',
        'ready-to-file': 'info',
        'filed': 'success',
        'completed': 'success'
    };
    return severityMap[status] || 'secondary';
};

onMounted(() => {
    fetchClient();
});
</script>
