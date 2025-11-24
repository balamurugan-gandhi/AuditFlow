<template>
    <div class="space-y-6">
        <div class="card bg-white dark:bg-surface-800 rounded-xl shadow-sm p-4 border border-surface-200 dark:border-surface-700">
            <DataTable v-model:filters="filters" :value="clients" :loading="loading" paginator :rows="10" 
                       :globalFilterFields="['business_name', 'contact_person', 'email', 'pan_number', 'phone']"
                       tableStyle="min-width: 50rem"
                       stripedRows :showGridlines="false" class="p-datatable-sm">
                <template #header>
                    <div class="flex justify-between items-center mb-4">
                        <h3 class="text-xl font-bold text-surface-900 dark:text-surface-0 m-0">Clients</h3>
                        <div class="flex gap-2">
                            <IconField iconPosition="left">
                                <InputIcon class="pi pi-search" />
                                <InputText v-model="filters['global'].value" placeholder="Search clients..." />
                            </IconField>
                            <Button label="Add Client" icon="pi pi-plus" @click="router.push('/clients/create')" severity="primary" />
                        </div>
                    </div>
                </template>
                <template #empty>
                    <div class="text-center p-4">No clients found.</div>
                </template>
                <Column field="business_name" header="Business Name" sortable class="font-medium"></Column>
                <Column field="pan_number" header="PAN Number" sortable></Column>
                <Column field="contact_person" header="Contact Person" sortable></Column>
                <Column field="phone" header="Phone"></Column>
                <Column field="email" header="Email" sortable>
                    <template #body="slotProps">
                        <span v-if="slotProps.data.email">{{ slotProps.data.email }}</span>
                        <span v-else class="text-surface-400 italic">N/A</span>
                    </template>
                </Column>
                <Column header="Actions" :exportable="false" style="min-width: 8rem">
                    <template #body="slotProps">
                        <div class="flex gap-2">
                            <Button icon="pi pi-eye" text rounded severity="secondary" @click="router.push(`/clients/${slotProps.data.id}`)" v-tooltip.top="'View'" />
                            <Button icon="pi pi-pencil" text rounded severity="info" @click="router.push(`/clients/${slotProps.data.id}/edit`)" v-tooltip.top="'Edit'" />
                            <Button icon="pi pi-trash" text rounded severity="danger" @click="confirmDelete(slotProps.data)" v-tooltip.top="'Delete'" :loading="deletingId === slotProps.data.id" :disabled="deletingId === slotProps.data.id" />
                        </div>
                    </template>
                </Column>
            </DataTable>
        </div>
    </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import { useRouter } from 'vue-router';
import api from '../../api/axios';
import Button from 'primevue/button';
import DataTable from 'primevue/datatable';
import Column from 'primevue/column';
import InputText from 'primevue/inputtext';
import IconField from 'primevue/iconfield';
import InputIcon from 'primevue/inputicon';
import { FilterMatchMode } from '@primevue/core/api';

const router = useRouter();
const clients = ref([]);
const loading = ref(true);
const filters = ref({
    global: { value: null, matchMode: FilterMatchMode.CONTAINS },
});
const deletingId = ref(null);

const fetchClients = async () => {
    try {
        const response = await api.get('/clients');
        clients.value = response.data;
    } catch (error) {
        console.error('Error fetching clients:', error);
    } finally {
        loading.value = false;
    }
};

const confirmDelete = async (client) => {
    if (!confirm(`Are you sure you want to delete ${client.business_name}?`)) {
        return;
    }

    deletingId.value = client.id;

    try {
        await api.delete(`/clients/${client.id}`);
        await fetchClients();
    } catch (error) {
        console.error('Error deleting client:', error);
    } finally {
        deletingId.value = null;
    }
};

onMounted(() => {
    fetchClients();
});
</script>
