<template>
    <div class="max-w-2xl mx-auto space-y-6">

        <div class="card bg-white dark:bg-surface-800 rounded-xl shadow-sm p-6">
            <form @submit.prevent="handleSubmit" class="space-y-6">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div class="flex flex-col gap-2">
                        <label for="client" class="text-sm font-medium text-surface-700 dark:text-surface-200">Client</label>
                        <Dropdown id="client" v-model="form.client_id" :options="clients" optionLabel="business_name" optionValue="id" placeholder="Select Client" filter required class="w-full" :invalid="!!errors.client_id" @change="onClientChange" />
                        <small class="text-red-500" v-if="errors.client_id">{{ errors.client_id[0] }}</small>
                    </div>
                    <div class="flex flex-col gap-2">
                        <label for="file" class="text-sm font-medium text-surface-700 dark:text-surface-200">File</label>
                        <Dropdown id="file" v-model="form.file_id" :options="clientFiles" optionLabel="file_display" optionValue="id" placeholder="Select File" filter class="w-full" :invalid="!!errors.file_id" :disabled="!form.client_id" />
                        <small class="text-red-500" v-if="errors.file_id">{{ errors.file_id[0] }}</small>
                    </div>
                    <div class="flex flex-col gap-2">
                        <label for="invoice_number" class="text-sm font-medium text-surface-700 dark:text-surface-200">Invoice #</label>
                        <InputText id="invoice_number" v-model="form.invoice_number" required :invalid="!!errors.invoice_number" />
                        <small class="text-red-500" v-if="errors.invoice_number">{{ errors.invoice_number[0] }}</small>
                    </div>
                    <div class="flex flex-col gap-2">
                        <label for="invoice_date" class="text-sm font-medium text-surface-700 dark:text-surface-200">Invoice Date</label>
                        <InputText id="invoice_date" type="date" v-model="form.invoice_date" required :invalid="!!errors.invoice_date" />
                        <small class="text-red-500" v-if="errors.invoice_date">{{ errors.invoice_date[0] }}</small>
                    </div>
                    <div class="flex flex-col gap-2">
                        <label for="due_date" class="text-sm font-medium text-surface-700 dark:text-surface-200">Due Date</label>
                        <InputText id="due_date" type="date" v-model="form.due_date" required :invalid="!!errors.due_date" />
                        <small class="text-red-500" v-if="errors.due_date">{{ errors.due_date[0] }}</small>
                    </div>
                    <div class="flex flex-col gap-2">
                        <label for="total_tax_amount" class="text-sm font-medium text-surface-700 dark:text-surface-200">Total Tax Amount</label>
                        <InputNumber id="total_tax_amount" v-model="form.total_tax_amount" mode="currency" currency="INR" locale="en-IN" class="w-full" :invalid="!!errors.total_tax_amount" />
                        <small class="text-red-500" v-if="errors.total_tax_amount">{{ errors.total_tax_amount[0] }}</small>
                    </div>
                    <div class="flex flex-col gap-2">
                        <label for="auditor_fee" class="text-sm font-medium text-surface-700 dark:text-surface-200">Auditor Fee</label>
                        <InputNumber id="auditor_fee" v-model="form.auditor_fee" mode="currency" currency="INR" locale="en-IN" required class="w-full" :invalid="!!errors.auditor_fee" />
                        <small class="text-red-500" v-if="errors.auditor_fee">{{ errors.auditor_fee[0] }}</small>
                    </div>
                    <div class="flex flex-col gap-2 md:col-span-2">
                        <label for="notes" class="text-sm font-medium text-surface-700 dark:text-surface-200">Notes</label>
                        <Textarea id="notes" v-model="form.notes" rows="3" :invalid="!!errors.notes" />
                        <small class="text-red-500" v-if="errors.notes">{{ errors.notes[0] }}</small>
                    </div>
                </div>

                <div class="flex justify-end gap-3">
                    <Button label="Cancel" severity="secondary" @click="router.back()" />
                    <Button type="submit" label="Create Invoice" :loading="loading" />
                </div>
            </form>
        </div>
    </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import { useRouter } from 'vue-router';
import api from '../../api/axios';
import Button from 'primevue/button';
import InputText from 'primevue/inputtext';
import InputNumber from 'primevue/inputnumber';
import Textarea from 'primevue/textarea';
import Dropdown from 'primevue/dropdown';

const router = useRouter();
const loading = ref(false);
const clients = ref([]);
const clientFiles = ref([]);

const form = ref({
    client_id: null,
    file_id: null,
    invoice_number: `INV-${Date.now()}`,
    invoice_date: new Date().toISOString().split('T')[0],
    due_date: '',
    total_tax_amount: null,
    auditor_fee: null,
    notes: ''
});

const errors = ref({});

const fetchClients = async () => {
    try {
        const response = await api.get('/clients');
        clients.value = response.data;
    } catch (error) {
        console.error('Error fetching clients:', error);
    }
};

const onClientChange = async () => {
    if (form.value.client_id) {
        try {
            const response = await api.get('/files', {
                params: { client_id: form.value.client_id }
            });
            clientFiles.value = response.data.map(file => ({
                ...file,
                file_display: `${file.file_type} - ${file.assessment_year}`
            }));
        } catch (error) {
            console.error('Error fetching client files:', error);
            clientFiles.value = [];
        }
    } else {
        clientFiles.value = [];
    }
    form.value.file_id = null; // Reset file selection when client changes
};

const handleSubmit = async () => {
    loading.value = true;
    errors.value = {};
    try {
        await api.post('/invoices', form.value);
        router.push('/billing');
    } catch (error) {
        console.error('Error creating invoice:', error);
        if (error.response?.status === 422) {
            errors.value = error.response.data.errors || {};
        }
    } finally {
        loading.value = false;
    }
};

onMounted(() => {
    fetchClients();
});
</script>
