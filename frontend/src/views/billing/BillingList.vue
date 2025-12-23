<template>
    <div class="space-y-6">
        <div class="flex justify-between items-center gap-4">
            <h2 class="text-2xl font-bold text-surface-900 dark:text-surface-0 m-0">All Invoices</h2>
            <div class="flex gap-3">
                <IconField iconPosition="left">
                    <InputIcon class="pi pi-search" />
                    <InputText v-model="searchQuery" placeholder="Search invoices..." style="width: 300px;" />
                </IconField>
                <Button label="Create Invoice" icon="pi pi-plus" @click="router.push('/billing/create')" />
            </div>
        </div>

        <div class="card bg-white dark:bg-surface-800 rounded-xl shadow-sm p-4">
            <DataTable :value="filteredInvoices" :loading="loading" paginator :rows="10" tableStyle="min-width: 50rem"
                       stripedRows :showGridlines="false" class="p-datatable-sm">
                <Column field="invoice_number" header="Invoice #" sortable></Column>
                <Column field="client.business_name" header="Client" sortable></Column>
                <Column field="file.file_number" header="File ID" sortable></Column>
                <Column field="invoice_date" header="Date" sortable></Column>
                <Column field="auditor_fee" header="Amount" sortable>
                    <template #body="slotProps">
                        {{ formatCurrency(slotProps.data.auditor_fee) }}
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
                    <p class="text-xl font-bold text-surface-900 dark:text-surface-0">{{ formatCurrency(selectedInvoice.auditor_fee) }}</p>
                </div>

                <div class="flex flex-col gap-2">
                    <label for="amount" class="text-sm font-medium">Amount</label>
                    <InputNumber id="amount" v-model="paymentForm.amount" mode="currency" currency="INR" locale="en-IN" class="w-full" />
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
import { ref, onMounted, computed } from 'vue';
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
import IconField from 'primevue/iconfield';
import InputIcon from 'primevue/inputicon';

const vTooltip = Tooltip;
const router = useRouter();
const invoices = ref([]);
const loading = ref(true);
const searchQuery = ref('');

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

const filteredInvoices = computed(() => {
    if (!searchQuery.value) {
        return invoices.value;
    }
    
    const query = searchQuery.value.toLowerCase();
    return invoices.value.filter(invoice => {
        return (
            invoice.invoice_number?.toLowerCase().includes(query) ||
            invoice.client?.business_name?.toLowerCase().includes(query) ||
            invoice.status?.toLowerCase().includes(query)
        );
    });
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
    paymentForm.value.amount = invoice.auditor_fee; // Default to full amount
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
    router.push(`/billing/${invoice.id}`);
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
    return new Intl.NumberFormat('en-IN', { style: 'currency', currency: 'INR' }).format(value);
};

onMounted(() => {
    fetchInvoices();
});
</script>
