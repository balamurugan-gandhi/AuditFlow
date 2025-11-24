<template>
    <div class="space-y-6">
        <div class="card bg-white dark:bg-surface-800 rounded-xl shadow-sm p-4">
            <DataTable :value="invoices" :loading="loading" paginator :rows="10" tableStyle="min-width: 50rem"
                       stripedRows :showGridlines="false" class="p-datatable-sm">
                <template #header>
                    <div class="flex justify-between items-center">
                        <h3 class="text-lg font-semibold text-surface-900 dark:text-surface-0 m-0">All Invoices</h3>
                        <Button label="Create Invoice" icon="pi pi-plus" @click="router.push('/billing/create')" />
                    </div>
                </template>
                <Column field="invoice_number" header="Invoice #" sortable></Column>
                <Column field="client.business_name" header="Client" sortable></Column>
                <Column field="invoice_date" header="Date" sortable></Column>
                <Column field="total_amount" header="Amount" sortable>
                    <template #body="slotProps">
                        {{ formatCurrency(slotProps.data.total_amount) }}
                    </template>
                </Column>
                <Column header="Status">
                    <template #body="slotProps">
                        <Tag :value="slotProps.data.status" :severity="getStatusSeverity(slotProps.data.status)" />
                    </template>
                </Column>
                <Column header="Actions">
                    <template #body="slotProps">
                        <div class="flex gap-2">
                            <Button icon="pi pi-dollar" text rounded severity="success" 
                                v-if="slotProps.data.status !== 'paid'"
                                @click="openPaymentDialog(slotProps.data)" 
                                v-tooltip="'Record Payment'" />
                            <Button icon="pi pi-eye" text rounded severity="secondary" @click="viewInvoice(slotProps.data)" />
                        </div>
                    </template>
                </Column>
            </DataTable>
        </div>

        <!-- Payment Dialog -->
        <Dialog v-model:visible="showPaymentDialog" modal header="Record Payment" :style="{ width: '30rem' }">
            <div class="space-y-4" v-if="selectedInvoice">
                <div class="p-3 bg-surface-50 dark:bg-surface-700 rounded-md mb-4">
                    <p class="text-sm text-surface-600 dark:text-surface-300">Invoice #{{ selectedInvoice.invoice_number }}</p>
                    <p class="text-xl font-bold text-surface-900 dark:text-surface-0">{{ formatCurrency(selectedInvoice.total_amount) }}</p>
                </div>

                <div class="flex flex-col gap-2">
                    <label for="amount" class="text-sm font-medium">Amount</label>
                    <InputNumber id="amount" v-model="paymentForm.amount" mode="currency" currency="USD" locale="en-US" class="w-full" />
                </div>
                <div class="flex flex-col gap-2">
                    <label for="date" class="text-sm font-medium">Date</label>
                    <InputText id="date" type="date" v-model="paymentForm.payment_date" class="w-full" />
                </div>
                <div class="flex flex-col gap-2">
                    <label for="method" class="text-sm font-medium">Method</label>
                    <Dropdown id="method" v-model="paymentForm.payment_method" :options="paymentMethods" placeholder="Select Method" class="w-full" />
                </div>
            </div>
            <template #footer>
                <Button label="Cancel" text severity="secondary" @click="showPaymentDialog = false" />
                <Button label="Record Payment" @click="submitPayment" :loading="submittingPayment" />
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
import Tag from 'primevue/tag';
import Dialog from 'primevue/dialog';
import InputNumber from 'primevue/inputnumber';
import InputText from 'primevue/inputtext';
import Dropdown from 'primevue/dropdown';
import Tooltip from 'primevue/tooltip';

const vTooltip = Tooltip;
const router = useRouter();
const invoices = ref([]);
const loading = ref(true);

// Payment State
const showPaymentDialog = ref(false);
const submittingPayment = ref(false);
const selectedInvoice = ref(null);
const paymentMethods = ['Bank Transfer', 'Cash', 'Cheque', 'UPI'];
const paymentForm = ref({
    amount: 0,
    payment_date: new Date().toISOString().split('T')[0],
    payment_method: ''
});

const fetchInvoices = async () => {
    try {
        const response = await api.get('/invoices');
        invoices.value = response.data;
    } catch (error) {
        console.error('Error fetching invoices:', error);
    } finally {
        loading.value = false;
    }
};

const openPaymentDialog = (invoice) => {
    selectedInvoice.value = invoice;
    paymentForm.value.amount = invoice.total_amount; // Default to full amount
    showPaymentDialog.value = true;
};

const submitPayment = async () => {
    submittingPayment.value = true;
    try {
        await api.post(`/invoices/${selectedInvoice.value.id}/payments`, {
            amount: paymentForm.value.amount,
            payment_date: paymentForm.value.payment_date,
            payment_method: paymentForm.value.payment_method.toLowerCase().replace(' ', '_')
        });
        showPaymentDialog.value = false;
        await fetchInvoices(); // Refresh list
    } catch (error) {
        console.error('Error recording payment:', error);
    } finally {
        submittingPayment.value = false;
    }
};

const viewInvoice = (invoice) => {
    // Implement view details if needed, for now just log
    console.log('View invoice', invoice);
};

const getStatusSeverity = (status) => {
    switch (status) {
        case 'paid': return 'success';
        case 'partial': return 'warning';
        case 'overdue': return 'danger';
        default: return 'secondary';
    }
};

const formatCurrency = (value) => {
    return new Intl.NumberFormat('en-US', { style: 'currency', currency: 'USD' }).format(value);
};

onMounted(() => {
    fetchInvoices();
});
</script>
