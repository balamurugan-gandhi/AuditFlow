<template>
    <div class="max-w-4xl mx-auto space-y-6">
        <!-- Loading Skeleton -->
        <div v-if="loading" class="space-y-6">
            <Skeleton width="100%" height="200px"></Skeleton>
            <Skeleton width="100%" height="300px"></Skeleton>
        </div>

        <!-- Invoice Details -->
        <div v-else-if="invoice" class="space-y-6">
            <!-- Header -->
            <div class="card bg-white dark:bg-surface-800 rounded-xl shadow-sm p-6">
                <div class="flex justify-between items-start mb-6">
                    <div>
                        <h2 class="text-2xl font-bold text-surface-900 dark:text-surface-0">Invoice {{ invoice.invoice_number }}</h2>
                        <p class="text-surface-600 dark:text-surface-400 mt-1">{{ invoice.client?.business_name }}</p>
                    </div>
                    <Tag :value="invoice.status" :severity="getStatusSeverity(invoice.status)" class="text-lg px-4 py-2" />
                </div>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <div>
                        <p class="text-sm text-surface-600 dark:text-surface-400">Invoice Date</p>
                        <p class="text-lg font-semibold text-surface-900 dark:text-surface-0">{{ invoice.invoice_date }}</p>
                    </div>
                    <div>
                        <p class="text-sm text-surface-600 dark:text-surface-400">Due Date</p>
                        <p class="text-lg font-semibold text-surface-900 dark:text-surface-0">{{ invoice.due_date }}</p>
                    </div>
                    <div>
                        <p class="text-sm text-surface-600 dark:text-surface-400">Total Amount</p>
                        <p class="text-2xl font-bold text-surface-900 dark:text-surface-0">{{ formatCurrency(invoice.total_amount) }}</p>
                    </div>
                </div>
            </div>

            <!-- Client Information -->
            <div class="card bg-white dark:bg-surface-800 rounded-xl shadow-sm p-6">
                <h3 class="text-lg font-semibold text-surface-900 dark:text-surface-0 mb-4">Client Information</h3>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <p class="text-sm text-surface-600 dark:text-surface-400">Business Name</p>
                        <p class="text-surface-900 dark:text-surface-0">{{ invoice.client?.business_name }}</p>
                    </div>
                    <div>
                        <p class="text-sm text-surface-600 dark:text-surface-400">Contact Person</p>
                        <p class="text-surface-900 dark:text-surface-0">{{ invoice.client?.contact_person || 'N/A' }}</p>
                    </div>
                    <div>
                        <p class="text-sm text-surface-600 dark:text-surface-400">Email</p>
                        <p class="text-surface-900 dark:text-surface-0">{{ invoice.client?.email || 'N/A' }}</p>
                    </div>
                    <div>
                        <p class="text-sm text-surface-600 dark:text-surface-400">Phone</p>
                        <p class="text-surface-900 dark:text-surface-0">{{ invoice.client?.phone || 'N/A' }}</p>
                    </div>
                </div>
            </div>

            <!-- Amount Breakdown -->
            <div class="card bg-white dark:bg-surface-800 rounded-xl shadow-sm p-6">
                <h3 class="text-lg font-semibold text-surface-900 dark:text-surface-0 mb-4">Amount Details</h3>
                <div class="space-y-3">
                    <div class="flex justify-between">
                        <span class="text-surface-600 dark:text-surface-400">Subtotal</span>
                        <span class="text-surface-900 dark:text-surface-0 font-medium">{{ formatCurrency(invoice.total_amount - (invoice.tax_amount || 0)) }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-surface-600 dark:text-surface-400">Tax</span>
                        <span class="text-surface-900 dark:text-surface-0 font-medium">{{ formatCurrency(invoice.tax_amount || 0) }}</span>
                    </div>
                    <div class="border-t border-surface-200 dark:border-surface-700 pt-3 flex justify-between">
                        <span class="text-lg font-semibold text-surface-900 dark:text-surface-0">Total</span>
                        <span class="text-lg font-bold text-surface-900 dark:text-surface-0">{{ formatCurrency(invoice.total_amount) }}</span>
                    </div>
                </div>
            </div>

            <!-- Notes -->
            <div v-if="invoice.notes" class="card bg-white dark:bg-surface-800 rounded-xl shadow-sm p-6">
                <h3 class="text-lg font-semibold text-surface-900 dark:text-surface-0 mb-4">Notes</h3>
                <p class="text-surface-700 dark:text-surface-300">{{ invoice.notes }}</p>
            </div>

            <!-- Payment History -->
            <div v-if="invoice.payments && invoice.payments.length > 0" class="card bg-white dark:bg-surface-800 rounded-xl shadow-sm p-6">
                <h3 class="text-lg font-semibold text-surface-900 dark:text-surface-0 mb-4">Payment History</h3>
                <DataTable :value="invoice.payments" class="p-datatable-sm">
                    <Column field="payment_date" header="Date"></Column>
                    <Column field="payment_method" header="Method">
                        <template #body="slotProps">
                            {{ formatPaymentMethod(slotProps.data.payment_method) }}
                        </template>
                    </Column>
                    <Column field="amount" header="Amount">
                        <template #body="slotProps">
                            {{ formatCurrency(slotProps.data.amount) }}
                        </template>
                    </Column>
                </DataTable>
            </div>

            <!-- Actions -->
            <div class="flex justify-end gap-3">
                <Button label="Back" severity="secondary" @click="router.back()" />
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import { useRouter, useRoute } from 'vue-router';
import api from '../../api/axios';
import Button from 'primevue/button';
import Tag from 'primevue/tag';
import Skeleton from 'primevue/skeleton';
import DataTable from 'primevue/datatable';
import Column from 'primevue/column';

const router = useRouter();
const route = useRoute();
const invoice = ref(null);
const loading = ref(true);

const fetchInvoice = async () => {
    try {
        const response = await api.get(`/invoices/${route.params.id}`);
        invoice.value = response.data;
    } catch (error) {
        console.error('Error fetching invoice:', error);
        router.push('/billing');
    } finally {
        loading.value = false;
    }
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

const formatPaymentMethod = (method) => {
    return method.replace('_', ' ').replace(/\b\w/g, l => l.toUpperCase());
};

onMounted(() => {
    fetchInvoice();
});
</script>
