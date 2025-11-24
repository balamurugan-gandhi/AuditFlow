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
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div class="flex flex-col gap-2">
                        <label for="client" class="text-sm font-medium text-surface-700 dark:text-surface-200">Client</label>
                        <Dropdown id="client" v-model="form.client_id" :options="clients" optionLabel="business_name" optionValue="id" placeholder="Select Client" filter required class="w-full" />
                    </div>
                    <div class="flex flex-col gap-2">
                        <label for="file_type" class="text-sm font-medium text-surface-700 dark:text-surface-200">File Type</label>
                        <Dropdown id="file_type" v-model="form.file_type" :options="serviceTypes" placeholder="Select Type" required class="w-full" />
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
                        <label for="status" class="text-sm font-medium text-surface-700 dark:text-surface-200">Status</label>
                        <Dropdown id="status" v-model="form.status" :options="statuses" placeholder="Select Status" required class="w-full" />
                    </div>
                    <div class="flex flex-col gap-2">
                        <label for="assignee" class="text-sm font-medium text-surface-700 dark:text-surface-200">Assignee</label>
                        <Dropdown id="assignee" v-model="form.assigned_to" :options="employees" optionLabel="name" optionValue="id" placeholder="Select Employee" filter class="w-full" />
                    </div>
                    <div class="flex flex-col gap-2">
                        <label for="payment_request_date" class="text-sm font-medium text-surface-700 dark:text-surface-200">Payment Request Date</label>
                        <Calendar id="payment_request_date" v-model="form.payment_request_date" dateFormat="yy-mm-dd" showIcon class="w-full" />
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

const serviceTypes = ['Income Tax', 'GST', 'Audit', 'Accounting', 'Consulting'];
const statuses = ['received', 'assigned', 'in-progress', 'pending-info', 'ready-to-file', 'filed', 'completed'];

const form = ref({
    client_id: null,
    file_type: '',
    assessment_year: '',
    financial_year: '',
    status: 'received',
    assigned_to: null,
    estimated_completion_date: null,
    payment_request_date: null,
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

const handleSubmit = async () => {
    loading.value = true;
    try {
        if (isEditing.value) {
            await api.put(`/files/${route.params.id}`, form.value);
        } else {
            await api.post('/files', form.value);
        }
        router.push('/files');
    } catch (error) {
        console.error('Error saving file:', error);
    } finally {
        loading.value = false;
    }
};

onMounted(() => {
    fetchData();
});
</script>
