<template>
    <div class="max-w-2xl mx-auto space-y-6">

        <div class="card bg-white dark:bg-surface-800 rounded-xl shadow-sm p-6">
            <form @submit.prevent="handleSubmit" class="space-y-6">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div class="flex flex-col gap-2">
                        <label for="client" class="text-sm font-medium text-surface-700 dark:text-surface-200">Client</label>
                        <Dropdown id="client" v-model="form.client_id" :options="clients" optionLabel="business_name" optionValue="id" placeholder="Select Client" filter required class="w-full" />
                    </div>
                    <div class="flex flex-col gap-2">
                        <label for="invoice_number" class="text-sm font-medium text-surface-700 dark:text-surface-200">Invoice #</label>
                        <InputText id="invoice_number" v-model="form.invoice_number" required />
                    </div>
                    <div class="flex flex-col gap-2">
                        <label for="invoice_date" class="text-sm font-medium text-surface-700 dark:text-surface-200">Invoice Date</label>
                        <InputText id="invoice_date" type="date" v-model="form.invoice_date" required />
                    </div>
                    <div class="flex flex-col gap-2">
                        <label for="due_date" class="text-sm font-medium text-surface-700 dark:text-surface-200">Due Date</label>
                        <InputText id="due_date" type="date" v-model="form.due_date" required />
                    </div>
                    <div class="flex flex-col gap-2">
                        <label for="amount" class="text-sm font-medium text-surface-700 dark:text-surface-200">Total Amount</label>
                        <InputNumber id="amount" v-model="form.total_amount" mode="currency" currency="USD" locale="en-US" required class="w-full" />
                    </div>
                    <div class="flex flex-col gap-2">
                        <label for="tax" class="text-sm font-medium text-surface-700 dark:text-surface-200">Tax Amount</label>
                        <InputNumber id="tax" v-model="form.tax_amount" mode="currency" currency="USD" locale="en-US" class="w-full" />
                    </div>
                    <div class="flex flex-col gap-2 md:col-span-2">
                        <label for="notes" class="text-sm font-medium text-surface-700 dark:text-surface-200">Notes</label>
                        <Textarea id="notes" v-model="form.notes" rows="3" />
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

const form = ref({
    client_id: null,
    invoice_number: `INV-${Date.now()}`,
    invoice_date: new Date().toISOString().split('T')[0],
    due_date: '',
    total_amount: 0,
    tax_amount: 0,
    notes: ''
});

const fetchClients = async () => {
    try {
        const response = await api.get('/clients');
        clients.value = response.data;
    } catch (error) {
        console.error('Error fetching clients:', error);
    }
};

const handleSubmit = async () => {
    loading.value = true;
    try {
        await api.post('/invoices', form.value);
        router.push('/billing');
    } catch (error) {
        console.error('Error creating invoice:', error);
    } finally {
        loading.value = false;
    }
};

onMounted(() => {
    fetchClients();
});
</script>
