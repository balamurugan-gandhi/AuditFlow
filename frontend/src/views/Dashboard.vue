<template>
    <div class="dashboard">
        <!-- Header Section -->
        <div class="mb-6">
            <div class="mb-4">
                <h2 class="text-3xl font-bold text-surface-900 dark:text-surface-0 m-0">{{ dashboardTitle }}</h2>
                <p class="text-surface-500 dark:text-surface-400 mt-2">Overview of your audit workflow</p>
            </div>
            
            <!-- Filters -->
            <div class="flex gap-6 flex-wrap">
                <div v-if="authStore.isAdmin" class="flex items-center gap-2">
                    <i class="pi pi-users text-surface-600 dark:text-surface-400"></i>
                    <Dropdown v-model="selectedEmployee" :options="employees" optionLabel="name" optionValue="id" placeholder="Select Employee" class="w-64" showClear @change="fetchDashboardData" />
                </div>
                <div class="flex items-center gap-2">
                    <i class="pi pi-calendar text-surface-600 dark:text-surface-400"></i>
                    <Dropdown v-model="selectedYear" :options="assessmentYears" placeholder="Select Year" class="w-64" @change="fetchDashboardData" />
                </div>
                <div class="flex items-center gap-2">
                    <i class="pi pi-clock text-surface-600 dark:text-surface-400"></i>
                    <Dropdown v-model="selectedTimePeriod" :options="timePeriods" optionLabel="label" optionValue="value" placeholder="Select Period" class="w-64" showClear @change="fetchDashboardData" />
                </div>
            </div>
        </div>

        <!-- Statistics Cards -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
            <!-- Received Files -->
            <div class="dashboard-card card-blue" @click="!loading && navigateToFiles('all')">
                <div class="card-bg-pattern"></div>
                <div class="card-content">
                    <div>
                        <p class="card-label">Received Files</p>
                        <h3 class="card-value" v-if="!loading">{{ stats.received }}</h3>
                        <div v-else class="skeleton-loader" style="width: 80px; height: 40px;"></div>
                        <div class="card-percentage" v-if="!loading">
                            <span class="percentage-badge">{{ getPercentage(stats.received, true) }}%</span>
                            <span class="percentage-text">from {{ stats.total_clients }} clients</span>
                        </div>
                        <div v-else class="skeleton-loader" style="width: 120px; height: 20px; margin-top: 8px;"></div>
                    </div>
                    <div class="card-icon-wrapper">
                        <i class="pi pi-inbox card-icon"></i>
                    </div>
                </div>
            </div>

            <!-- Pending Files -->
            <div class="dashboard-card card-orange" @click="!loading && navigateToFiles('pending')">
                <div class="card-bg-pattern"></div>
                <div class="card-content">
                    <div>
                        <p class="card-label">Pending Files</p>
                        <h3 class="card-value" v-if="!loading">{{ stats.pending }}</h3>
                        <div v-else class="skeleton-loader" style="width: 80px; height: 40px;"></div>
                        <div class="card-percentage" v-if="!loading">
                            <span class="percentage-badge">{{ getPercentage(stats.pending, false) }}%</span>
                            <span class="percentage-text">of total files</span>
                        </div>
                        <div v-else class="skeleton-loader" style="width: 120px; height: 20px; margin-top: 8px;"></div>
                    </div>
                    <div class="card-icon-wrapper">
                        <i class="pi pi-clock card-icon"></i>
                    </div>
                </div>
            </div>

            <!-- In Progress -->
            <div class="dashboard-card card-purple" @click="!loading && navigateToFiles('in-progress')">
                <div class="card-bg-pattern"></div>
                <div class="card-content">
                    <div>
                        <p class="card-label">In Progress</p>
                        <h3 class="card-value" v-if="!loading">{{ stats.in_progress }}</h3>
                        <div v-else class="skeleton-loader" style="width: 80px; height: 40px;"></div>
                        <div class="card-percentage" v-if="!loading">
                            <span class="percentage-badge">{{ getPercentage(stats.in_progress, false) }}%</span>
                            <span class="percentage-text">of total files</span>
                        </div>
                        <div v-else class="skeleton-loader" style="width: 120px; height: 20px; margin-top: 8px;"></div>
                    </div>
                    <div class="card-icon-wrapper">
                        <i class="pi pi-cog card-icon"></i>
                    </div>
                </div>
            </div>

            <!-- Ready to File -->
            <div class="dashboard-card card-cyan" @click="!loading && navigateToFiles('ready-to-file')">
                <div class="card-bg-pattern"></div>
                <div class="card-content">
                    <div>
                        <p class="card-label">Ready to File</p>
                        <h3 class="card-value" v-if="!loading">{{ stats.ready_to_file }}</h3>
                        <div v-else class="skeleton-loader" style="width: 80px; height: 40px;"></div>
                        <div class="card-percentage" v-if="!loading">
                            <span class="percentage-badge">{{ getPercentage(stats.ready_to_file, false) }}%</span>
                            <span class="percentage-text">of total files</span>
                        </div>
                        <div v-else class="skeleton-loader" style="width: 120px; height: 20px; margin-top: 8px;"></div>
                    </div>
                    <div class="card-icon-wrapper">
                        <i class="pi pi-file card-icon"></i>
                    </div>
                </div>
            </div>

            <!-- Completed Files -->
            <div class="dashboard-card card-green" @click="!loading && navigateToFiles('completed')">
                <div class="card-bg-pattern"></div>
                <div class="card-content">
                    <div>
                        <p class="card-label">Completed Files</p>
                        <h3 class="card-value" v-if="!loading">{{ stats.completed }}</h3>
                        <div v-else class="skeleton-loader" style="width: 80px; height: 40px;"></div>
                        <div class="card-percentage" v-if="!loading">
                            <span class="percentage-badge">{{ getPercentage(stats.completed, false) }}%</span>
                            <span class="percentage-text">of total files</span>
                        </div>
                        <div v-else class="skeleton-loader" style="width: 120px; height: 20px; margin-top: 8px;"></div>
                    </div>
                    <div class="card-icon-wrapper">
                        <i class="pi pi-check-circle card-icon"></i>
                    </div>
                </div>
            </div>

            <!-- Payment Received -->
            <div v-if="authStore.isAdmin || authStore.isManager" class="dashboard-card card-teal" @click="!loading && navigateToFiles('payment_received')">
                <div class="card-bg-pattern"></div>
                <div class="card-content">
                    <div>
                        <p class="card-label">Payment Received</p>
                        <h3 class="card-value" v-if="!loading">{{ stats.payment_received }}</h3>
                        <div v-else class="skeleton-loader" style="width: 80px; height: 40px;"></div>
                    </div>
                    <div class="card-icon-wrapper">
                        <i class="pi pi-wallet card-icon"></i>
                    </div>
                </div>
            </div>
        </div>

        <!-- Charts and Activity Section -->
        <div class="dashboard-content">
            <!-- Recent Files -->
            <div class="content-card">
                <div class="card-header">
                    <h2>Recent Files</h2>
                    <Button label="View All" text @click="$router.push('/files')" />
                </div>
                <DataTable :value="recentFiles" :loading="loading">
                    <Column field="file_type" header="File Type"></Column>
                    <Column field="client.business_name" header="Client"></Column>
                    <Column field="assessment_year" header="Assessment Year"></Column>
                    <Column field="status" header="Status">
                        <template #body="slotProps">
                            <Tag :value="slotProps.data.status" :severity="getStatusSeverity(slotProps.data.status)" />
                        </template>
                    </Column>
                </DataTable>
            </div>

            <!-- Pending Invoices -->
            <div class="content-card">
                <div class="card-header">
                    <h2>Pending Invoices</h2>
                    <Button label="View All" text @click="$router.push('/billing')" />
                </div>
                <DataTable :value="pendingInvoices" :loading="loading">
                    <Column field="invoice_number" header="Invoice #"></Column>
                    <Column field="client.business_name" header="Client"></Column>
                    <Column field="total_amount" header="Amount">
                        <template #body="slotProps">
                            ₹{{ formatCurrency(slotProps.data.total_amount) }}
                        </template>
                    </Column>
                    <Column field="due_date" header="Due Date"></Column>
                </DataTable>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, onMounted, computed } from 'vue';
import { useRouter } from 'vue-router';
import { useAuthStore } from '../stores/auth';
import api from '../api/axios';
import Dropdown from 'primevue/dropdown';
import DataTable from 'primevue/datatable';
import Column from 'primevue/column';
import Tag from 'primevue/tag';
import Button from 'primevue/button';

const authStore = useAuthStore();
const router = useRouter();
const loading = ref(false);

const dashboardTitle = computed(() => {
    if (authStore.isAdmin) {
        if (selectedEmployee.value) {
            const employee = employees.value.find(e => e.id === selectedEmployee.value);
            if (employee) {
                const firstName = employee.name.split(' ')[0];
                return `${firstName} Dashboard`;
            }
        }
        return 'Admin Dashboard';
    }
    const firstName = authStore.user?.name?.split(' ')[0] || 'User';
    return `${firstName} Dashboard`;
});

const currentYear = new Date().getFullYear();
const assessmentYears = ref([
    `${currentYear}-${currentYear + 1}`,
    `${currentYear - 1}-${currentYear}`,
    `${currentYear - 2}-${currentYear - 1}`
]);
const timePeriods = ref([
    { label: 'Last 24 Hours', value: '24h' },
    { label: 'Last 7 Days', value: '7d' },
    { label: 'Last 30 Days', value: '30d' }
]);
const selectedYear = ref(assessmentYears.value[0]);
const selectedTimePeriod = ref(null);
const employees = ref([]);
const selectedEmployee = ref(null);

const stats = ref({
    received: 0,
    total_clients: 0,
    total_files: 0,
    pending: 0,
    in_progress: 0,
    ready_to_file: 0,
    completed: 0,
    payment_received: 0,
    unassigned: 0
});

const getPercentage = (count, isReceivedCard = false) => {
    if (isReceivedCard) {
        // For received card, calculate percentage of total clients
        if (stats.value.total_clients === 0) return 0;
        return Math.round((count / stats.value.total_clients) * 100);
    } else {
        // For other cards, calculate percentage of total files
        if (stats.value.total_files === 0) return 0;
        return Math.round((count / stats.value.total_files) * 100);
    }
};

const dashboardCards = computed(() => [
    { 
        title: 'Received Files', 
        count: stats.value.received, 
        status: 'all', 
        icon: 'pi-inbox', 
        gradient: 'from-blue-500 to-blue-600',
        showPercentage: true,
        percentageText: 'of total clients',
        isReceivedCard: true
    },
    { 
        title: 'Pending Files', 
        count: stats.value.pending, 
        status: 'pending-info', 
        icon: 'pi-clock', 
        gradient: 'from-orange-500 to-red-500',
        showPercentage: true,
        percentageText: 'of total files',
        isReceivedCard: false
    },
    { 
        title: 'In Progress', 
        count: stats.value.in_progress, 
        status: 'in-progress', 
        icon: 'pi-cog', 
        gradient: 'from-indigo-500 to-purple-500',
        showPercentage: true,
        percentageText: 'of total files',
        isReceivedCard: false
    },
    { 
        title: 'Ready to File', 
        count: stats.value.ready_to_file, 
        status: 'ready-to-file', 
        icon: 'pi-file', 
        gradient: 'from-cyan-500 to-blue-500',
        showPercentage: true,
        percentageText: 'of total files',
        isReceivedCard: false
    },
    { 
        title: 'Completed Files', 
        count: stats.value.completed, 
        status: 'completed', 
        icon: 'pi-check-circle', 
        gradient: 'from-emerald-500 to-green-500',
        showPercentage: true,
        percentageText: 'of total files',
        isReceivedCard: false
    },
    { 
        title: 'Payment Received', 
        count: stats.value.payment_received, 
        status: 'payment_received', 
        icon: 'pi-wallet', 
        gradient: 'from-teal-500 to-emerald-500',
        showPercentage: false,
        isReceivedCard: false
    }
]);

const recentFiles = ref([]);
const pendingInvoices = ref([]);

const fetchDashboardData = async () => {
    loading.value = true;
    try {
        // Fetch stats
        const statsRes = await api.get('/dashboard/stats', {
            params: { 
                assessment_year: selectedYear.value,
                employee_id: selectedEmployee.value,
                time_period: selectedTimePeriod.value
            }
        });
        stats.value = statsRes.data;

        // Fetch recent files (optional: filter by year if needed, currently just recent)
        const filesRes = await api.get('/files');
        recentFiles.value = filesRes.data.slice(0, 5);

        // Fetch pending invoices
        const invoicesRes = await api.get('/invoices');
        pendingInvoices.value = (invoicesRes.data || []).filter(i => i.status === 'unpaid').slice(0, 5);
    } catch (error) {
        console.error('Error fetching dashboard data:', error);
    } finally {
        loading.value = false;
    }
};

const fetchEmployees = async () => {
    if (!authStore.isAdmin) return;
    try {
        const response = await api.get('/employees', {
            params: { role: 'employee' }
        });
        employees.value = response.data;
    } catch (error) {
        console.error('Error fetching employees:', error);
    }
};

const navigateToFiles = (status) => {
    const query = { 
        status: status, 
        year: selectedYear.value 
    };
    
    if (selectedTimePeriod.value) {
        query.time_period = selectedTimePeriod.value;
    }
    
    if (status === 'payment_received') {
        // Handle payment received filter specifically if needed, or just go to files
        query.payment_status = 'received';
    }
    
    router.push({ path: '/files', query });
};

const formatCurrency = (value) => {
    return new Intl.NumberFormat('en-IN').format(value);
};

const getStatusSeverity = (status) => {
    const severityMap = {
        'received': 'secondary',
        'assigned': 'info',
        'in-progress': 'info',
        'pending-info': 'warning',
        'ready-to-file': 'info',
        'filed': 'success',
        'completed': 'success'
    };
    return severityMap[status] || 'secondary';
};

onMounted(() => {
    fetchEmployees();
    fetchDashboardData();
});
</script>

<style scoped>
.dashboard {
    padding: 2rem;
    background: #f8f9fa;
    min-height: 100vh;
}

.stats-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: 1.5rem;
    margin-bottom: 2rem;
}

.stat-card {
    background: white;
    border-radius: 12px;
    padding: 1.5rem;
    display: flex;
    align-items: center;
    gap: 1rem;
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
    transition: transform 0.2s, box-shadow 0.2s;
}

.stat-card:hover {
    transform: translateY(-4px);
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
}

.stat-icon {
    width: 60px;
    height: 60px;
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.5rem;
}

.stat-card.clients .stat-icon {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
}

.stat-card.invoices .stat-icon {
    background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
    color: white;
}

.stat-card.files .stat-icon {
    background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);
    color: white;
}

.stat-card.revenue .stat-icon {
    background: linear-gradient(135deg, #43e97b 0%, #38f9d7 100%);
    color: white;
}

.stat-content h3 {
    font-size: 2rem;
    font-weight: 700;
    color: #1e293b;
    margin: 0;
}

.stat-content p {
    color: #64748b;
    margin: 0;
    font-size: 0.875rem;
}

.dashboard-content {
    display: grid;
    gap: 1.5rem;
}

.content-card {
    background: white;
    border-radius: 12px;
    padding: 1.5rem;
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
}

.card-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 1rem;
}

.card-header h2 {
    font-size: 1.25rem;
    font-weight: 600;
    color: #1e293b;
    margin: 0;
}

@media (max-width: 768px) {
    .dashboard {
        padding: 1rem;
    }

    .stats-grid {
        grid-template-columns: 1fr;
    }
}
</style>
