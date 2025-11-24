<template>
    <div class="space-y-6">
        <div class="flex justify-end items-center">
            <Button label="Back" icon="pi pi-arrow-left" text @click="router.back()" />
        </div>

        <div v-if="loading" class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- Basic Information Skeleton -->
            <div class="lg:col-span-2 space-y-6">
                <div class="card bg-white dark:bg-surface-800 rounded-xl shadow-sm p-6 border border-surface-200 dark:border-surface-700">
                    <div class="flex justify-between items-start mb-6">
                        <div>
                            <Skeleton width="16rem" height="2rem" class="mb-2"></Skeleton>
                            <Skeleton width="8rem" height="1rem"></Skeleton>
                        </div>
                        <Skeleton width="6rem" height="2rem" borderRadius="16px"></Skeleton>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div class="info-item" v-for="i in 2" :key="i">
                            <Skeleton width="6rem" height="1rem" class="mb-2"></Skeleton>
                            <Skeleton width="100%" height="1.5rem"></Skeleton>
                        </div>
                    </div>
                </div>

                <!-- Assigned Clients Skeleton -->
                <div class="card bg-white dark:bg-surface-800 rounded-xl shadow-sm p-6 border border-surface-200 dark:border-surface-700">
                    <Skeleton width="10rem" height="1.5rem" class="mb-4"></Skeleton>
                    <div class="space-y-4">
                        <Skeleton width="100%" height="3rem" v-for="i in 3" :key="i"></Skeleton>
                    </div>
                </div>
            </div>

            <!-- Stats Skeleton -->
            <div class="space-y-6">
                <div class="card bg-white dark:bg-surface-800 rounded-xl shadow-sm p-6 border border-surface-200 dark:border-surface-700">
                    <Skeleton width="10rem" height="1.5rem" class="mb-4"></Skeleton>
                    <div class="grid grid-cols-2 gap-4">
                        <Skeleton width="100%" height="5rem" v-for="i in 4" :key="i"></Skeleton>
                    </div>
                </div>
            </div>
        </div>

        <div v-else-if="employee" class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- Basic Information -->
            <div class="lg:col-span-2 space-y-6">
                <div class="card bg-white dark:bg-surface-800 rounded-xl shadow-sm p-6 border border-surface-200 dark:border-surface-700">
                    <div class="flex justify-between items-start mb-6">
                        <div>
                            <h3 class="text-xl font-bold text-surface-900 dark:text-surface-0 m-0">{{ employee.name }}</h3>
                            <p class="text-surface-500 dark:text-surface-400 mt-1">{{ employee.email }}</p>
                        </div>
                        <div class="flex gap-2">
                            <Tag v-for="role in employee.roles" :key="role.id" :value="role.name" severity="info" />
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div class="info-item">
                            <label class="block text-sm font-medium text-surface-500 dark:text-surface-400 mb-1">Phone</label>
                            <div class="text-surface-900 dark:text-surface-0 font-medium">{{ employee.phone || 'N/A' }}</div>
                        </div>
                        <div class="info-item">
                            <label class="block text-sm font-medium text-surface-500 dark:text-surface-400 mb-1">WhatsApp</label>
                            <div class="text-surface-900 dark:text-surface-0 font-medium">{{ employee.whatsapp_number || 'N/A' }}</div>
                        </div>
                        <div class="info-item">
                            <label class="block text-sm font-medium text-surface-500 dark:text-surface-400 mb-1">Date of Birth</label>
                            <div class="text-surface-900 dark:text-surface-0 font-medium">{{ formatDate(employee.dob) }}</div>
                        </div>
                        <div class="info-item">
                            <label class="block text-sm font-medium text-surface-500 dark:text-surface-400 mb-1">Gender</label>
                            <div class="text-surface-900 dark:text-surface-0 font-medium">{{ employee.gender || 'N/A' }}</div>
                        </div>
                        <div class="info-item">
                            <label class="block text-sm font-medium text-surface-500 dark:text-surface-400 mb-1">Joined Date</label>
                            <div class="text-surface-900 dark:text-surface-0 font-medium">{{ formatDate(employee.doj) }}</div>
                        </div>
                        <div class="info-item">
                            <label class="block text-sm font-medium text-surface-500 dark:text-surface-400 mb-1">Status</label>
                            <Tag value="Active" severity="success" />
                        </div>
                        <div class="info-item md:col-span-2">
                            <label class="block text-sm font-medium text-surface-500 dark:text-surface-400 mb-1">Address</label>
                            <div class="text-surface-900 dark:text-surface-0 font-medium">{{ employee.address || 'N/A' }}</div>
                        </div>
                    </div>
                </div>

                <!-- Assigned Files History -->
                <div class="card bg-white dark:bg-surface-800 rounded-xl shadow-sm p-6 border border-surface-200 dark:border-surface-700">
                    <div class="flex justify-between items-center mb-4">
                        <h3 class="text-lg font-bold text-surface-900 dark:text-surface-0 m-0">Assigned Files History</h3>
                        <Dropdown v-model="selectedYear" :options="assessmentYears" placeholder="Select Year" class="w-48" />
                    </div>
                    <DataTable :value="filteredFiles" stripedRows class="p-datatable-sm" paginator :rows="5" :rowsPerPageOptions="[5, 10, 20]">
                        <template #empty>
                            <div class="text-center p-4">No files assigned to this employee.</div>
                        </template>
                        <Column field="client.business_name" header="Client"></Column>
                        <Column field="file_type" header="File Type"></Column>
                        <Column field="assessment_year" header="Year"></Column>
                        <Column field="status" header="Status">
                            <template #body="slotProps">
                                <Tag :value="slotProps.data.status" :severity="getStatusSeverity(slotProps.data.status)" />
                            </template>
                        </Column>
                        <Column header="Actions" style="width: 4rem">
                            <template #body="slotProps">
                                <Button icon="pi pi-eye" text rounded severity="secondary" @click="router.push(`/files/${slotProps.data.id}`)" />
                            </template>
                        </Column>
                    </DataTable>
                </div>
            </div>

            <!-- Stats Section -->
            <div class="space-y-6">
                <div class="card bg-white dark:bg-surface-800 rounded-xl shadow-sm p-6 border border-surface-200 dark:border-surface-700">
                    <h3 class="text-lg font-bold text-surface-900 dark:text-surface-0 mb-4">Performance Stats</h3>
                    
                    <div class="grid grid-cols-2 gap-4">
                        <div class="p-4 bg-blue-50 dark:bg-blue-900/20 rounded-xl text-center">
                            <div class="text-2xl font-bold text-blue-600 dark:text-blue-400">{{ stats.total_files || 0 }}</div>
                            <div class="text-xs text-surface-500 dark:text-surface-400 mt-1">Assigned Files</div>
                        </div>
                        <div class="p-4 bg-orange-50 dark:bg-orange-900/20 rounded-xl text-center">
                            <div class="text-2xl font-bold text-orange-600 dark:text-orange-400">{{ stats.pending || 0 }}</div>
                            <div class="text-xs text-surface-500 dark:text-surface-400 mt-1">Pending</div>
                        </div>
                        <div class="p-4 bg-purple-50 dark:bg-purple-900/20 rounded-xl text-center">
                            <div class="text-2xl font-bold text-purple-600 dark:text-purple-400">{{ stats.in_progress || 0 }}</div>
                            <div class="text-xs text-surface-500 dark:text-surface-400 mt-1">In Progress</div>
                        </div>
                        <div class="p-4 bg-green-50 dark:bg-green-900/20 rounded-xl text-center">
                            <div class="text-2xl font-bold text-green-600 dark:text-green-400">{{ stats.completed || 0 }}</div>
                            <div class="text-xs text-surface-500 dark:text-surface-400 mt-1">Completed</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, onMounted, computed } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import api from '../../api/axios';
import Button from 'primevue/button';
import Tag from 'primevue/tag';
import DataTable from 'primevue/datatable';
import Column from 'primevue/column';
import Skeleton from 'primevue/skeleton';
import Dropdown from 'primevue/dropdown';

const route = useRoute();
const router = useRouter();
const employee = ref(null);
const loading = ref(true);
const stats = ref({});
const currentYear = new Date().getFullYear();
const assessmentYears = ref([
    'All Years',
    `${currentYear}-${currentYear + 1}`,
    `${currentYear - 1}-${currentYear}`,
    `${currentYear - 2}-${currentYear - 1}`
]);
const selectedYear = ref('All Years');

const filteredFiles = computed(() => {
    if (!employee.value || !employee.value.files) return [];
    if (selectedYear.value === 'All Years') return employee.value.files;
    return employee.value.files.filter(file => file.assessment_year === selectedYear.value);
});

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

const fetchEmployee = async () => {
    try {
        const response = await api.get(`/employees/${route.params.id}`);
        employee.value = response.data;
        
        // Fetch stats for this employee for the current assessment year
        // Default to current year range, e.g., "2025-2026"
        const currentAY = `${currentYear}-${currentYear + 1}`;
        
        const statsRes = await api.get('/dashboard/stats', {
            params: { 
                employee_id: route.params.id,
                assessment_year: currentAY
            }
        });
        stats.value = statsRes.data;
    } catch (error) {
        console.error('Error fetching employee:', error);
    } finally {
        loading.value = false;
    }
};

const formatDate = (dateString) => {
    if (!dateString) return 'N/A';
    return new Date(dateString).toLocaleDateString('en-IN', {
        year: 'numeric',
        month: 'long',
        day: 'numeric'
    });
};

onMounted(() => {
    fetchEmployee();
});
</script>
