<template>
    <div class="work-logs">
        <div class="card bg-white dark:bg-surface-800 rounded-xl shadow-sm p-4">
            <DataTable :value="workLogs" :loading="loading" paginator :rows="10" 
                       responsiveLayout="scroll" stripedRows :showGridlines="false" class="p-datatable-sm">
                <template #header>
                    <div class="flex justify-between items-center">
                        <h3 class="text-lg font-semibold text-surface-900 dark:text-surface-0 m-0">All Work Logs</h3>
                    </div>
                </template>
                <Column field="id" header="ID" sortable style="width: 80px"></Column>
                <Column field="file.client.business_name" header="Client" sortable></Column>
                <Column field="file.file_type" header="File Type" sortable></Column>
                <Column field="user.name" header="Employee" sortable></Column>
                <Column field="hours_worked" header="Hours" sortable style="width: 100px"></Column>
                <Column field="date" header="Date" sortable>
                    <template #body="slotProps">
                        {{ formatDate(slotProps.data.date) }}
                    </template>
                </Column>
                <Column field="description" header="Description"></Column>
            </DataTable>
        </div>
    </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import { useRouter } from 'vue-router';
import DataTable from 'primevue/datatable';
import Column from 'primevue/column';
import Button from 'primevue/button';
import api from '../../api/axios';

const router = useRouter();
const workLogs = ref([]);
const loading = ref(false);

const fetchWorkLogs = async () => {
    loading.value = true;
    try {
        const response = await api.get('/work-logs');
        workLogs.value = response.data.data || [];
    } catch (error) {
        console.error('Error fetching work logs:', error);
    } finally {
        loading.value = false;
    }
};

const formatDate = (date) => {
    return new Date(date).toLocaleDateString('en-US', {
        year: 'numeric',
        month: 'short',
        day: 'numeric'
    });
};

onMounted(() => {
    fetchWorkLogs();
});
</script>

