<template>
    <div class="space-y-6">
        <div class="card bg-white dark:bg-surface-800 rounded-xl shadow-sm p-4">
            <DataTable :value="files" :loading="loading" paginator :rows="10" tableStyle="min-width: 50rem"
                       stripedRows :showGridlines="false" class="p-datatable-sm">
                <template #header>
                    <div class="flex justify-between items-center">
                        <h3 class="text-lg font-semibold text-surface-900 dark:text-surface-0 m-0">All Files</h3>
                        <Button v-if="isAdminOrManager" label="Add File" icon="pi pi-plus" @click="router.push('/files/create')" />
                    </div>
                </template>
                <Column field="file_type" header="File Type" sortable></Column>
                <Column field="client.business_name" header="Client" sortable></Column>
                <Column field="assessment_year" header="Assessment Year" sortable></Column>
                <Column field="financial_year" header="Financial Year" sortable></Column>
                <Column field="payment_request_date" header="Payment Req. Date" sortable>
                    <template #body="slotProps">
                        {{ slotProps.data.payment_request_date || '-' }}
                    </template>
                </Column>
                <Column header="Status">
                    <template #body="slotProps">
                        <Tag :value="slotProps.data.status" :severity="getStatusSeverity(slotProps.data.status)" />
                    </template>
                </Column>
                <Column header="Assignee">
                    <template #body="slotProps">
                        <span v-if="slotProps.data.assignee">{{ slotProps.data.assignee.name }}</span>
                        <span v-else class="text-surface-400 italic">Unassigned</span>
                    </template>
                </Column>
                <Column header="Actions">
                    <template #body="slotProps">
                        <div class="flex gap-2">
                            <Button v-if="isAdminOrManager" icon="pi pi-pencil" text rounded severity="info" @click="router.push(`/files/${slotProps.data.id}/edit`)" />
                            <Button icon="pi pi-eye" text rounded severity="secondary" @click="router.push(`/files/${slotProps.data.id}`)" />
                        </div>
                    </template>
                </Column>
            </DataTable>
        </div>
    </div>
</template>

<script setup>
import { ref, onMounted, computed } from 'vue';
import { useRouter, useRoute } from 'vue-router';
import { useAuthStore } from '../../stores/auth';
import api from '../../api/axios';
import Button from 'primevue/button';
import DataTable from 'primevue/datatable';
import Column from 'primevue/column';
import Tag from 'primevue/tag';

const router = useRouter();
const route = useRoute();
const authStore = useAuthStore();
const files = ref([]);
const loading = ref(false);

const isAdminOrManager = computed(() => {
    return authStore.user?.roles?.some(role => role.name === 'admin' || role.name === 'manager');
});

const fetchFiles = async () => {
    loading.value = true;
    try {
        const response = await api.get('/files');
        let fetchedFiles = response.data;
        
        // Apply filters from query parameters
        const statusFilter = route.query.status;
        const yearFilter = route.query.year;
        
        if (statusFilter && statusFilter !== 'all') {
            if (statusFilter === 'payment_received') {
                // Filter by payment_id not null
                fetchedFiles = fetchedFiles.filter(file => file.payment_id !== null);
            } else if (statusFilter === 'pending') {
                // Pending includes both 'received' (unassigned) and 'pending-info' statuses
                fetchedFiles = fetchedFiles.filter(file => 
                    file.status === 'received' || file.status === 'pending-info'
                );
            } else {
                // Filter by exact status
                fetchedFiles = fetchedFiles.filter(file => file.status === statusFilter);
            }
        }
        
        if (yearFilter) {
            fetchedFiles = fetchedFiles.filter(file => file.assessment_year === yearFilter);
        }
        
        files.value = fetchedFiles;
    } catch (error) {
        console.error('Error fetching files:', error);
    } finally {
        loading.value = false;
    }
};

const getStatusSeverity = (status) => {
    switch (status) {
        case 'completed': return 'success';
        case 'filed': return 'success';
        case 'ready-to-file': return 'info';
        case 'in-progress': return 'info';
        case 'pending-info': return 'warning';
        case 'assigned': return 'secondary';
        case 'received': return 'secondary';
        default: return 'secondary';
    }
};

onMounted(() => {
    fetchFiles();
});
</script>
