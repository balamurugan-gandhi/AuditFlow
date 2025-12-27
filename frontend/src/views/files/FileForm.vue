<template>
    <div class="max-w-2xl mx-auto space-y-6">

        <div class="card bg-white dark:bg-surface-800 rounded-xl shadow-sm p-6">
            <!-- Loading Skeleton -->
            <div v-if="fetchingData" class="space-y-6">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div v-for="i in 7" :key="i" class="flex flex-col gap-2">
                        <Skeleton width="6rem" height="1rem"></Skeleton>
                        <Skeleton width="100%" height="2.5rem"></Skeleton>
                    </div>
                </div>
                <div class="flex flex-col gap-2">
                    <Skeleton width="4rem" height="1rem"></Skeleton>
                    <Skeleton width="100%" height="4rem"></Skeleton>
                </div>
            </div>

            <!-- Actual Form -->
            <form v-else @submit.prevent="handleSubmit" class="space-y-6">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-x-8 gap-y-4">
                    <div class="flex flex-col gap-2">
                        <label for="client" class="text-sm font-medium text-surface-700 dark:text-surface-200">Client</label>
                        <Dropdown id="client" v-model="form.client_id" :options="clients" optionLabel="business_name" optionValue="id" placeholder="Select Client" filter required class="w-full" />
                    </div>
                    <div class="flex flex-col gap-2">
                        <label for="file_type" class="text-sm font-medium text-surface-700 dark:text-surface-200">File Type</label>
                        <Dropdown id="file_type" v-model="form.file_type" :options="serviceTypes" placeholder="Select Type" required class="w-full" />
                    </div>
                        <div class="flex flex-col gap-2">
                            <label for="turnover" class="text-sm font-medium text-surface-700 dark:text-surface-200">Turnover *</label>
                            <Dropdown id="turnover" v-model="form.turnover" :options="turnoverOptions" optionLabel="label" optionValue="key" placeholder="Select Turnover" required class="w-full" />
                            <span v-if="errorMessages.turnover" class="text-red-600 text-xs mt-1">{{ errorMessages.turnover }}</span>
                        </div>
                    <div class="flex flex-col gap-2" v-if="isEditing">
                        <label for="assignee" class="text-sm font-medium text-surface-700 dark:text-surface-200">Assignee</label>
                        <template v-if="form.assigned_to">
                            <div class="flex items-center gap-2">
                                <span class="px-3 py-1 bg-surface-200 dark:bg-surface-700 rounded text-surface-900 dark:text-surface-100">
                                    {{ employees.find(e => e.id === form.assigned_to)?.name || 'Unknown' }}
                                </span>
                                <Button icon="pi pi-times" size="small" text rounded @click="form.assigned_to = null" aria-label="Clear Assignee" />
                            </div>
                        </template>
                        <template v-else>
                            <Dropdown id="assignee" v-model="form.assigned_to" :options="employees" optionLabel="name" optionValue="id" placeholder="Select Employee" filter class="w-full" />
                        </template>
                    </div>
                    <div class="flex flex-col gap-2">
                        <label for="assessment_year" class="text-sm font-medium text-surface-700 dark:text-surface-200">Assessment Year</label>
                        <InputText id="assessment_year" v-model="form.assessment_year" placeholder="e.g. 2024-2025" required />
                    </div>
                    <div class="flex flex-col gap-2">
                        <label for="financial_year" class="text-sm font-medium text-surface-700 dark:text-surface-200">Financial Year</label>
                        <InputText id="financial_year" v-model="form.financial_year" placeholder="e.g. 2023-2024" />
                    </div>
                    <div class="flex flex-col gap-2">
                        <label for="estimated_completion_date" class="text-sm font-medium text-surface-700 dark:text-surface-200">Estimated Completion Date *</label>
                        <Calendar id="estimated_completion_date" v-model="form.estimated_completion_date" dateFormat="yy-mm-dd" showIcon required class="w-full" />
                    </div>
                    <div class="flex flex-col gap-2">
                        <label v-if="isEditing" for="status" class="text-sm font-medium text-surface-700 dark:text-surface-200">Status</label>
                        <Dropdown v-if="isEditing" id="status" v-model="form.status" :options="statuses" placeholder="Select Status" required class="w-full" />
                    </div>
                    <div class="flex flex-col gap-2 md:col-span-2">
                        <label for="notes" class="text-sm font-medium text-surface-700 dark:text-surface-200">Notes</label>
                        <Textarea id="notes" v-model="form.notes" rows="3" />
                    </div>
                </div>

                <div class="flex justify-end gap-3">
                    <Button label="Cancel" severity="secondary" @click="router.back()" />
                    <Button type="submit" :label="isEditing ? 'Update File' : 'Create File'" :loading="loading" />
                </div>
            </form>
        </div>
    </div>
</template>

<script setup>
import { ref, onMounted, computed } from 'vue';
import { useRouter, useRoute } from 'vue-router';
import api from '../../api/axios';
import Button from 'primevue/button';
import InputText from 'primevue/inputtext';
import Textarea from 'primevue/textarea';
import Dropdown from 'primevue/dropdown';
import Calendar from 'primevue/calendar';
import Skeleton from 'primevue/skeleton';

const router = useRouter();
const route = useRoute();
const isEditing = computed(() => !!route.params.id);
const loading = ref(false);
const fetchingData = ref(!!route.params.id);
const clients = ref([]);
const employees = ref([]);
// Add an 'Unassigned' option for the assignee dropdown in edit mode
const assigneeOptions = computed(() => [
    { id: null, name: 'Unassigned' },
    ...employees.value
]);

const serviceTypes = ['Income Tax', 'GST', 'Audit', 'Accounting', 'Consulting'];
const statuses = ['received', 'assigned', 'in-progress', 'ready-to-file', 'filed', 'completed'];

const turnoverOptions = [
    { key: 'AB', label: 'Above 2 Crores' },
    { key: 'AD', label: '1 to 2 Crores' },
    { key: 'NR', label: 'Less than 1 Crore' },
    { key: 'LA', label: 'Less than 50 Lakhs' }
];

const form = ref({
    client_id: null,
    file_type: '',
    assessment_year: '',
    financial_year: '',
    status: 'received',
    assigned_to: null,
    turnover: null,
    estimated_completion_date: null,
    notes: ''
});

const fetchData = async () => {
    try {
        const [clientsRes, employeesRes] = await Promise.all([
            api.get('/clients'),
            api.get('/employees')
        ]);
        clients.value = clientsRes.data;
        employees.value = employeesRes.data;

        if (isEditing.value) {
            const fileRes = await api.get(`/files/${route.params.id}`);
            form.value = fileRes.data;
        }
    } catch (error) {
        console.error('Error fetching data:', error);
    } finally {
        fetchingData.value = false;
    }
};

const formatDateForApi = (date) => {
    if (!date) return null;
    const d = new Date(date);
    const month = '' + (d.getMonth() + 1);
    const day = '' + d.getDate();
    const year = d.getFullYear();
    return [year, month.padStart(2, '0'), day.padStart(2, '0')].join('-');
};

const errorMessages = ref({});

const handleSubmit = async () => {
    loading.value = true;
    errorMessages.value = {};
    try {
        const payload = { ...form.value };
        if (!isEditing.value) {
            payload.status = 'received';
        }
        if (payload.estimated_completion_date) {
            payload.estimated_completion_date = formatDateForApi(payload.estimated_completion_date);
        }
        if (isEditing.value) {
            await api.put(`/files/${route.params.id}`, payload);
        } else {
            await api.post('/files', payload);
        }
        router.push('/files');
    } catch (error) {
        if (error.response && error.response.data && error.response.data.errors) {
            errorMessages.value = Object.fromEntries(
                Object.entries(error.response.data.errors).map(([field, messages]) => [field, messages[0]])
            );
        } else {
            console.error('Error saving file:', error);
        }
    } finally {
        loading.value = false;
    }
};

onMounted(() => {
    fetchData();
});
</script>
