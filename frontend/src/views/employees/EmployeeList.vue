<template>
    <div class="space-y-6">
        <div class="card bg-white dark:bg-surface-800 rounded-xl shadow-sm p-4">
            <DataTable v-model:filters="filters" :value="employees" :loading="loading" paginator :rows="10" 
                       :globalFilterFields="['name', 'email', 'roles.name']"
                       tableStyle="min-width: 50rem"
                       stripedRows :showGridlines="false" class="p-datatable-sm">
                <template #header>
                    <div class="flex justify-between items-center">
                        <h3 class="text-lg font-semibold text-surface-900 dark:text-surface-0 m-0">All Employees</h3>
                        <div class="flex gap-2">
                            <IconField iconPosition="left">
                                <InputIcon class="pi pi-search" />
                                <InputText v-model="filters['global'].value" placeholder="Search employees..." />
                            </IconField>
                            <Button label="Add Employee" icon="pi pi-plus" @click="router.push('/employees/create')" />
                        </div>
                    </div>
                </template>
                <Column field="name" header="Name" sortable></Column>
                <Column field="email" header="Email" sortable></Column>
                <Column header="Roles">
                    <template #body="slotProps">
                        <div class="flex gap-1">
                            <Tag v-for="role in slotProps.data.roles" :key="role.id" :value="role.name" severity="info" />
                        </div>
                    </template>
                </Column>
                <Column header="Actions">
                    <template #body="slotProps">
                        <div class="flex gap-2">
                            <Button icon="pi pi-eye" text rounded severity="secondary" @click="router.push(`/employees/${slotProps.data.id}`)" v-tooltip.top="'View'" />
                            <Button icon="pi pi-user" text rounded severity="success" v-tooltip="'Login as'" @click="impersonate(slotProps.data)" />
                            <Button icon="pi pi-pencil" text rounded severity="info" @click="router.push(`/employees/${slotProps.data.id}/edit`)" />
                            <Button icon="pi pi-trash" text rounded severity="danger" @click="confirmDelete(slotProps.data)" />
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
import { useAuthStore } from '../../stores/auth';
import Button from 'primevue/button';
import DataTable from 'primevue/datatable';
import Column from 'primevue/column';
import Tag from 'primevue/tag';
import InputText from 'primevue/inputtext';
import IconField from 'primevue/iconfield';
import InputIcon from 'primevue/inputicon';
import { FilterMatchMode } from '@primevue/core/api';

const router = useRouter();
const authStore = useAuthStore();
const employees = ref([]);
const loading = ref(true);
const filters = ref({
    global: { value: null, matchMode: FilterMatchMode.CONTAINS },
});

const fetchEmployees = async () => {
    try {
        const response = await api.get('/employees');
        // Filter out current user
        employees.value = response.data.filter(emp => emp.id !== authStore.user?.id);
    } catch (error) {
        console.error('Error fetching employees:', error);
    } finally {
        loading.value = false;
    }
};

const impersonate = async (employee) => {
    if (confirm(`Login as ${employee.name}?`)) {
        const success = await authStore.impersonate(employee.id);
        if (success) {
            router.push('/');
        }
    }
};

const confirmDelete = async (employee) => {
    if (confirm(`Are you sure you want to delete ${employee.name}?`)) {
        try {
            await api.delete(`/employees/${employee.id}`);
            await fetchEmployees();
        } catch (error) {
            console.error('Error deleting employee:', error);
        }
    }
};

onMounted(() => {
    fetchEmployees();
});
</script>
