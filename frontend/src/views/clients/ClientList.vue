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
                <Column field="email" header="File ID" sortable>
                    <template #body="slotProps">
                        <span v-if="slotProps.data.file_id">{{ slotProps.data.file_id }}</span>
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

        <!-- Client Notes Modal -->
        <Dialog v-model:visible="notesModalVisible" modal header="Client Notes" :style="{ width: '50rem' }">
            <template #header>
                <div class="flex align-items-center gap-2">
                    <i class="pi pi-file"></i>
                    <span>Notes for {{ selectedClient?.business_name }}</span>
                </div>
            </template>

            <div class="mb-4">
                <Button label="Add Note" icon="pi pi-plus" @click="openAddNoteDialog" />
            </div>

            <DataTable :value="clientNotes" :loading="notesLoading" stripedRows :showGridlines="false" class="p-datatable-sm">
                <template #empty>
                    <div class="text-center p-4">
                        <i class="pi pi-info-circle text-4xl text-surface-400 mb-4"></i>
                        <h3 class="text-xl font-semibold text-surface-700 dark:text-surface-300 mb-2">No Notes Found</h3>
                        <p class="text-surface-500 dark:text-surface-400">
                            No notes have been added for this client yet.
                        </p>
                    </div>
                </template>
                <Column field="reason" header="Reason" style="min-width: 200px"></Column>
                <Column field="user.name" header="User" style="min-width: 120px"></Column>
                <Column field="created_at" header="Last Contacted" style="min-width: 120px">
                    <template #body="slotProps">
                        {{ formatDate(slotProps.data.created_at) }}
                    </template>
                </Column>
            </DataTable>
        </Dialog>

        <!-- Add Note Dialog -->
        <Dialog v-model:visible="addNoteDialogVisible" modal header="Add Note" :style="{ width: '30rem' }">
            <div class="flex flex-column gap-4">
                <div>
                    <label for="reason" class="block text-sm font-medium mb-2">Reason *</label>
                    <Textarea id="reason" v-model="noteForm.reason" rows="4" class="w-full" />
                </div>
            </div>
            <template #footer>
                <Button label="Cancel" icon="pi pi-times" text @click="addNoteDialogVisible = false" />
                <Button label="Save" icon="pi pi-check" @click="saveNote" :loading="notesLoading" />
            </template>
        </Dialog>
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
import Dialog from 'primevue/dialog';
import Textarea from 'primevue/textarea';
import { FilterMatchMode } from '@primevue/core/api';

const router = useRouter();
const clients = ref([]);
const loading = ref(true);
const filters = ref({
    global: { value: null, matchMode: FilterMatchMode.CONTAINS },
});
const deletingId = ref(null);

// Notes modal data
const notesModalVisible = ref(false);
const selectedClient = ref(null);
const clientNotes = ref([]);
const notesLoading = ref(false);
const addNoteDialogVisible = ref(false);
const noteForm = ref({
    reason: ''
});

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

const openNotesModal = async (client) => {
    selectedClient.value = client;
    notesModalVisible.value = true;
    await fetchClientNotes(client.id);
};

const fetchClientNotes = async (clientId) => {
    notesLoading.value = true;
    try {
        const response = await api.get(`/clients/${clientId}/notes`);
        clientNotes.value = response.data;
    } catch (error) {
        console.error('Error fetching client notes:', error);
    } finally {
        notesLoading.value = false;
    }
};

const openAddNoteDialog = () => {
    noteForm.value = {
        reason: ''
    };
    addNoteDialogVisible.value = true;
};

const saveNote = async () => {
    try {
        await api.post(`/clients/${selectedClient.value.id}/notes`, noteForm.value);
        addNoteDialogVisible.value = false;
        await fetchClientNotes(selectedClient.value.id);
    } catch (error) {
        console.error('Error saving note:', error);
    }
};

const formatDate = (dateString) => {
    if (!dateString) return '-';
    const date = new Date(dateString);
    return date.toLocaleString('en-IN', {
        timeZone: 'Asia/Kolkata',
        year: 'numeric',
        month: 'short',
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit'
    });
};

onMounted(() => {
    fetchClients();
});
</script>
