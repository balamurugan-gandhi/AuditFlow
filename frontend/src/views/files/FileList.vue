<template>
    <ConfirmDialog />
    <div class="space-y-6">
        <div class="flex justify-between items-center gap-4">
            <h2 class="text-2xl font-bold text-surface-900 dark:text-surface-0 m-0">All Files</h2>
            <div class="flex gap-3">
                <IconField iconPosition="left">
                    <InputIcon class="pi pi-search" />
                    <InputText v-model="searchQuery" placeholder="Search files..." style="width: 300px;" />
                </IconField>
                <Button v-if="isAdminOrManager" label="Add File" icon="pi pi-plus" @click="router.push('/files/create')" />
            </div>
        </div>

        <div class="card bg-white dark:bg-surface-800 rounded-xl shadow-sm p-4">
            <DataTable :value="filteredFiles" :loading="loading" paginator :rows="10" tableStyle="min-width: 50rem"
                       stripedRows :showGridlines="false" class="p-datatable-sm">
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
                            <Button v-if="isAdminOrManager" icon="pi pi-trash" text rounded severity="danger" @click="confirmDelete(slotProps.data)" />
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
import InputText from 'primevue/inputtext';
import IconField from 'primevue/iconfield';
import InputIcon from 'primevue/inputicon';
import { useConfirm } from 'primevue/useconfirm';
import ConfirmDialog from 'primevue/confirmdialog';

const confirm = useConfirm();

const router = useRouter();
const route = useRoute();
const authStore = useAuthStore();
const files = ref([]);
const loading = ref(false);
const searchQuery = ref('');

const isAdminOrManager = computed(() => {
    return authStore.user?.roles?.some(role => role.name === 'admin' || role.name === 'manager');
});

const filteredFiles = computed(() => {
    if (!searchQuery.value) {
        return files.value;
    }
    
    const query = searchQuery.value.toLowerCase();
    return files.value.filter(file => {
        return (
            file.file_type?.toLowerCase().includes(query) ||
            file.client?.business_name?.toLowerCase().includes(query) ||
            file.assessment_year?.toLowerCase().includes(query) ||
            file.financial_year?.toLowerCase().includes(query) ||
            file.status?.toLowerCase().includes(query)
        );
    });
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
        
        // Sort files in descending order (newest first)
        files.value.sort((a, b) => b.id - a.id);
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

const confirmDelete = (file) => {
    confirm.require({
        message: `Are you sure you want to delete this file (${file.file_type} - ${file.assessment_year})?`,
        header: 'Confirm Delete',
        icon: 'pi pi-exclamation-triangle',
        acceptClass: 'p-button-danger',
        accept: async () => {
            try {
                await api.delete(`/files/${file.id}`);
                await fetchFiles();
            } catch (error) {
                console.error('Error deleting file:', error);
            }
        }
    });
};

onMounted(() => {
    fetchFiles();
});
</script>
