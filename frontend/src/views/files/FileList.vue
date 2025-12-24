<template>
    <ConfirmDialog />
    <div class="space-y-6">
        <div class="flex flex-col gap-4">
            <!-- Title -->
            <h2 class="text-2xl font-bold text-surface-900 dark:text-surface-0">
                {{ route.query.status === 'unreceived' ? 'Unreceived Files' : 'All Files' }}
            </h2>
            <hr>
            <!-- Controls Row -->
            <div class="grid grid-cols-4 gap-3 items-center">
                
                <!-- Search (≈ 1/4) -->
               <div class="md:col-span-1">
                 <label class="block mb-2 text-sm font-medium text-surface-700 dark:text-surface-300">
                    Search Filter
                </label>
                <IconField iconPosition="left" class="w-full">
                    <InputIcon class="pi pi-search" />
                    <InputText
                        v-model="searchQuery"
                        placeholder="Search..."
                        class="w-full"
                    />
                </IconField>
                </div>

                 <!-- Assessment Year (≈ 1/2) -->
                <div class="md:col-span-1">
                 <label class="block mb-2 text-sm font-medium text-surface-700 dark:text-surface-300">
                    Assessment Year
                </label>
                <Dropdown
                    v-model="selectedAssessmentYear"
                    :options="assessmentYearOptions"
                    optionLabel="label"
                    optionValue="value"
                    placeholder="Assessment Year"
                    class="w-full"
                    @change="onAssessmentYearChange"
                    :clearable="true"
                />
                </div>

                <!-- Button (auto width) -->
                <div class="col-span-2 w-full flex justify-end">
                <Button
                    v-if="isAdminOrManager"
                    label="Add File"
                    icon="pi pi-plus"
                    class="whitespace-nowrap"
                    @click="router.push('/files/create')"
                />
                </div>
            </div>
        </div>


        <div class="card bg-white dark:bg-surface-800 rounded-xl shadow-sm p-4">
            <!-- Files Table -->
            <DataTable v-if="route.query.status !== 'unreceived'" :value="filteredFiles" :loading="loading" paginator :rows="10" tableStyle="min-width: 50rem"
                       stripedRows :showGridlines="false" class="p-datatable-sm">
                <template #empty>
                    <div class="text-center p-8">
                        <i class="pi pi-inbox text-4xl text-surface-400 mb-4"></i>
                        <h3 class="text-xl font-semibold text-surface-700 dark:text-surface-300 mb-2">No Files Found</h3>
                        <p class="text-surface-500 dark:text-surface-400">
                            No files match your current filters.
                        </p>
                    </div>
                </template>
                <Column field="file_type" header="File Type" sortable></Column>
                <Column field="client.business_name" header="Client" sortable></Column>
                <!-- <Column field="assessment_year" header="Assessment Year" sortable></Column> -->
                <Column field="assessment_year" header="Assessment Year" sortable></Column>
                <!-- <Column field="payment_request_date" header="Payment Req. Date" sortable>
                    <template #body="slotProps">
                        {{ slotProps.data.payment_request_date || '-' }}
                    </template>
                </Column> -->
                <Column field="estimated_completion_date" header="Est. Completion Date" sortable>
                    <template #body="slotProps">
                        {{ slotProps.data.estimated_completion_date || '-' }}
                    </template>
                </Column>
                <Column field="turnover" header="Turnover" sortable>
                    <template #body="slotProps">
                        {{ slotProps.data.turnover || '-' }}
                    </template>
                </Column>
                <Column header="Status">
                    <template #body="slotProps">
                        <Tag :value="slotProps.data.status" :severity="getStatusSeverity(slotProps.data.status)" />
                    </template>
                </Column>
                <Column header="Assignee">
                    <template #body="slotProps">
                        <span v-if="slotProps.data.assignee">{{ slotProps.data.assignee.name }}</span>
                        <span v-else class="text-surface-400 italic">Unassigned</span>
                    </template>
                </Column>
                <Column header="Actions">
                    <template #body="slotProps">
                        <DropdownMenu :options="getActionOptions(slotProps.data)" @select="action => handleAction(action, slotProps.data)" />
                    </template>
                </Column>
            </DataTable>

            <!-- Clients Table (for unreceived files) -->
            <DataTable v-else :value="filteredClients" :loading="loading" paginator :rows="10" tableStyle="min-width: 50rem"
                       stripedRows :showGridlines="false" class="p-datatable-sm">
                <template #empty>
                    <div class="text-center p-8">
                        <i class="pi pi-users text-4xl text-surface-400 mb-4"></i>
                        <h3 class="text-xl font-semibold text-surface-700 dark:text-surface-300 mb-2">No Clients Found</h3>
                        <p class="text-surface-500 dark:text-surface-400">
                            All clients have submitted files for this period.
                        </p>
                    </div>
                </template>
                <Column field="business_name" header="Business Name" sortable></Column>
                <Column field="contact_person" header="Contact Person" sortable></Column>
                <Column field="email" header="Email" sortable></Column>
                <Column field="phone" header="Phone" sortable></Column>
                <Column header="Actions">
                    <template #body="slotProps">
                        <div class="flex gap-2">
                            <Button icon="pi pi-eye" text rounded severity="secondary" @click="router.push(`/clients/${slotProps.data.id}`)" />
                            <Button icon="pi pi-file" text rounded severity="info" @click="openNotesModal(slotProps.data)" v-tooltip="'View Notes'" />
                            <Button v-if="isAdminOrManager" icon="pi pi-plus" text rounded severity="success" @click="router.push('/files/create')" v-tooltip="'Add File for this client'" />
                        </div>
                    </template>
                </Column>
            </DataTable>
        </div>

        <!-- Client Notes Modal -->
        <Dialog v-model:visible="notesModalVisible" modal header="Client Notes" :style="{ width: '50rem' }">
            <template #header>
                <div class="flex align-items-center gap-2">
                    <i class="pi pi-file"></i>
                    <span>Notes for {{ selectedClient?.business_name }}</span>
                </div>
            </template>

            <div class="mb-4">
                <Button label="Add Note" icon="pi pi-plus" @click="openAddNoteDialog" />
            </div>

            <DataTable :value="clientNotes" :loading="notesLoading" stripedRows :showGridlines="false" class="p-datatable-sm">
                <template #empty>
                    <div class="text-center p-4">
                        <i class="pi pi-info-circle text-4xl text-surface-400 mb-4"></i>
                        <h3 class="text-xl font-semibold text-surface-700 dark:text-surface-300 mb-2">No Notes Found</h3>
                        <p class="text-surface-500 dark:text-surface-400">
                            No notes have been added for this client yet.
                        </p>
                    </div>
                </template>
                <Column field="reason" header="Reason" style="min-width: 200px"></Column>
                <Column field="user.name" header="User" style="min-width: 120px"></Column>
                <Column field="created_at" header="Last Contacted" style="min-width: 120px">
                    <template #body="slotProps">
                        {{ formatDate(slotProps.data.created_at) }}
                    </template>
                </Column>
            </DataTable>
        </Dialog>

        <!-- Add Note Dialog -->
        <Dialog v-model:visible="addNoteDialogVisible" modal header="Add Note" :style="{ width: '32rem', padding: '2rem' }" class="add-note-dialog">
            <div class="flex flex-col gap-6 py-2 px-2">
                <div>
                    <label for="reason" class="block text-base font-semibold mb-2 text-surface-900 dark:text-surface-100">Reason *</label>
                    <Textarea id="reason" v-model="noteForm.reason" rows="6" class="w-full rounded-lg border border-surface-200 focus:border-primary-500 transition-all" style="min-height: 120px;" />
                </div>
            </div>
            <template #footer>
                <div class="flex justify-end gap-3 pt-2">
                    <Button label="Cancel" icon="pi pi-times" text @click="addNoteDialogVisible = false" class="px-4 py-2 text-base" />
                    <Button label="Save" icon="pi pi-check" @click="saveNote" :loading="notesLoading" class="px-4 py-2 text-base" />
                </div>
            </template>
        </Dialog>
    </div>
</template>

<script setup>
import { ref, onMounted, computed } from 'vue';
import { useRouter, useRoute } from 'vue-router';
import { useAuthStore } from '../../stores/auth';
import api from '../../api/axios';
import Button from 'primevue/button';
import DataTable from 'primevue/datatable';
import Column from 'primevue/column';
import Tag from 'primevue/tag';
import InputText from 'primevue/inputtext';
import IconField from 'primevue/iconfield';
import InputIcon from 'primevue/inputicon';
import Dialog from 'primevue/dialog';
import Textarea from 'primevue/textarea';
import { useConfirm } from 'primevue/useconfirm';
import ConfirmDialog from 'primevue/confirmdialog';
import DropdownMenu from '../../components/DropdownMenu.vue';

import Dropdown from 'primevue/dropdown';


const confirm = useConfirm();

const router = useRouter();
const route = useRoute();
const authStore = useAuthStore();
const files = ref([]);
const clients = ref([]);
const loading = ref(false);
const searchQuery = ref('');

/* -------------------- assessment year -------------------- */
const selectedAssessmentYear = ref(null)
const allAssessmentYears = ref([])   // 🔑 IMPORTANT

const assessmentYearOptions = computed(() =>
  allAssessmentYears.value.map(y => ({ label: y, value: y }))
)

/* -------------------- helpers -------------------- */
function getCurrentAssessmentYear() {
  const today = new Date()
  const year = today.getFullYear()
  return today.getMonth() >= 3
    ? `${year}-${year + 1}`
    : `${year - 1}-${year}`
}

// const assessmentYearOptions = computed(() => {
//     // Get unique assessment years from files
//     const years = Array.from(new Set(files.value.map(f => f.assessment_year).filter(Boolean)));
//     return years.sort().reverse().map(y => ({ label: y, value: y }));
// });

// Utility to get current assessment year string (e.g. 2025-2026 if today is Dec 2025)
// function getCurrentAssessmentYear() {
//     const today = new Date();
//     let year = today.getFullYear();
//     // If month >= April (3), assessment year is current-next, else previous-current
//     if (today.getMonth() >= 3) {
//         return `${year}-${year + 1}`;
//     } else {
//         return `${year - 1}-${year}`;
//     }
// }

function onAssessmentYearChange() {
    // Update route query for year
    const query = { ...route.query };
    if (selectedAssessmentYear.value) {
        query.year = selectedAssessmentYear.value;
    } else {
        delete query.year;
    }
    router.replace({ query });
}

// Notes modal data
const notesModalVisible = ref(false);
const selectedClient = ref(null);
const clientNotes = ref([]);
const notesLoading = ref(false);
const addNoteDialogVisible = ref(false);
const noteForm = ref({
    reason: '',
    assessment_year: ''
});

const isAdminOrManager = computed(() => {
    return authStore.user?.roles?.some(role => role.name === 'admin' || role.name === 'manager');
});

const filteredFiles = computed(() => {
    if (!searchQuery.value) {
        return files.value;
    }
    
    const query = searchQuery.value.toLowerCase();
    return files.value.filter(file => {
        return (
            file.file_type?.toLowerCase().includes(query) ||
            file.client?.business_name?.toLowerCase().includes(query) ||
            file.assessment_year?.toLowerCase().includes(query) ||
            file.financial_year?.toLowerCase().includes(query) ||
            file.status?.toLowerCase().includes(query) 
        );
    });
});

const filteredClients = computed(() => {
    if (!searchQuery.value) {
        return clients.value;
    }
    
    const query = searchQuery.value.toLowerCase();
    return clients.value.filter(client => {
        return (
            client.business_name?.toLowerCase().includes(query) ||
            client.contact_person?.toLowerCase().includes(query) ||
            client.email?.toLowerCase().includes(query) ||
            client.phone?.toLowerCase().includes(query)
        );
    });
});

const fetchFiles = async () => {
    loading.value = true;
    try {
        const status = route.query.status;
        const year = route.query.year;
        // // Set default assessment year if not set in route
        // if (!year) {
        //     // Wait for files to load, then set default if available
        //     // (This will be called again on watch, so safe to set here)
        //     const ay = getCurrentAssessmentYear();
        //     // Only set if the year exists in options after files load
        //     setTimeout(() => {
        //         const found = assessmentYearOptions.value.find(opt => opt.value === ay);
        //         if (found && !selectedAssessmentYear.value) {
        //             selectedAssessmentYear.value = ay;
        //             onAssessmentYearChange();
        //         }
        //     }, 0);
        // } else {
        //     selectedAssessmentYear.value = year;
        // }
        const timePeriod = route.query.time_period;
        const employeeId = route.query.employee_id;
        if (status === 'unreceived') {
            // 1️⃣ Fetch all clients
            const { data: allClients } = await api.get('/clients');

            // 2️⃣ Fetch ALL files once (no mutation)
            const { data: allFilesRaw } = await api.get('/files');

            // 3️⃣ Extract assessment years from ALL files
            allAssessmentYears.value = Array.from(
                new Set(
                    allFilesRaw
                        .map(f => f.assessment_year)
                        .filter(Boolean)
                )
            ).sort().reverse();

            // 4️⃣ Apply year filter (for unreceived logic)
            let filteredFiles = year
                ? allFilesRaw.filter(f => f.assessment_year === year)
                : [...allFilesRaw];

            // 5️⃣ Apply time period filter
            if (timePeriod) {
                const now = new Date();
                let cutoff = null;

                if (timePeriod.endsWith('d')) {
                    cutoff = new Date(now - parseInt(timePeriod) * 24 * 60 * 60 * 1000);
                } else if (timePeriod.endsWith('w')) {
                    cutoff = new Date(now - parseInt(timePeriod) * 7 * 24 * 60 * 60 * 1000);
                } else if (timePeriod.endsWith('h') || timePeriod.endsWith('hr')) {
                    cutoff = new Date(now - parseInt(timePeriod) * 60 * 60 * 1000);
                }

                if (cutoff) {
                    filteredFiles = filteredFiles.filter(
                        file => new Date(file.created_at) >= cutoff
                    );
                }
            }

            // 6️⃣ Find clients WITH files
            const clientsWithFiles = new Set(
                filteredFiles.map(file => file.client_id)
            );

            // 7️⃣ Unreceived = clients WITHOUT files
            clients.value = allClients.filter(
                client => !clientsWithFiles.has(client.id)
            );

            files.value = [];
        } else {
            // Always fetch all files, then filter
            const response = await api.get('/files');
            let fetchedFiles = response.data;

             // 🔑 extract years from ALL files (never filtered)
            allAssessmentYears.value = Array.from(
            new Set(fetchedFiles.map(f => f.assessment_year).filter(Boolean))
            ).sort().reverse();

            let filtered = fetchedFiles;
                        
            if (status && status !== 'all') {
            if (status === 'payment_received') {
                filtered = filtered.filter(
                f => f.status === 'payment_received' && f.payment_id !== null
                );
            } else if (status === 'pending') {
                filtered = filtered.filter(
                f => f.status !== 'completed' && f.status !== 'filed'
                );
            } else {
                filtered = filtered.filter(f => f.status === status);
            }
            }

            // assessment year filter ✅
            if (year) {
            filtered = filtered.filter(f => f.assessment_year === year);
            }

            // employee filter
            if (employeeId && ['all','received','pending','in-progress','completed','ready-to-file'].includes(status)) {
            filtered = filtered.filter(f => f.assigned_to == employeeId);
            }

            // time_period filter
            if (timePeriod) {
            const now = new Date();
            let cutoff = null;

            if (timePeriod.endsWith('d')) {
                cutoff = new Date(now.getTime() - parseInt(timePeriod) * 86400000);
            } else if (timePeriod.endsWith('w')) {
                cutoff = new Date(now.getTime() - parseInt(timePeriod) * 604800000);
            } else if (timePeriod.endsWith('h') || timePeriod.endsWith('hr')) {
                cutoff = new Date(now.getTime() - parseInt(timePeriod) * 3600000);
            }

            if (cutoff) {
                filtered = filtered.filter(f => new Date(f.created_at) >= cutoff);
            }
            }

            // ✅ THIS IS THE ONLY ASSIGNMENT
            files.value = filtered.sort((a, b) => b.id - a.id);
            clients.value = [];
        }
        files.value.sort((a, b) => b.id - a.id);
    } catch (error) {
        console.error('Error fetching files:', error);
    } finally {
        loading.value = false;
    }
};

const openNotesModal = async (client) => {
    selectedClient.value = client;
    notesModalVisible.value = true;
    await fetchClientNotes(client.id);
};

const fetchClientNotes = async (clientId) => {
    notesLoading.value = true;
    try {
        const response = await api.get(`/clients/${clientId}/notes`, {
            params: {
                assessment_year: selectedAssessmentYear.value
            }
        });
        clientNotes.value = response.data;
    } catch (error) {
        console.error('Error fetching client notes:', error);
    } finally {
        notesLoading.value = false;
    }
};

const openAddNoteDialog = () => {
    if (!selectedAssessmentYear.value) {
        console.warn('No assessment year selected');
        return;
    }

    noteForm.value = {
        reason: '',
        assessment_year: selectedAssessmentYear.value
    };

    addNoteDialogVisible.value = true;
};

const saveNote = async () => {
    if (!selectedAssessmentYear.value) {
        console.warn('Cannot save note without assessment year');
        return;
    }

    try {
        const payload = {
            reason: noteForm.value.reason,
            assessment_year: selectedAssessmentYear.value // 🔑 enforce
        };

        await api.post(
            `/clients/${selectedClient.value.id}/notes`,
            payload
        );

        addNoteDialogVisible.value = false;

        // reload notes for CURRENT year
        await fetchClientNotes(selectedClient.value.id);
    } catch (error) {
        console.error('Error saving note:', error);
    }
};

const formatDate = (dateString) => {
    if (!dateString) return '-';
    const date = new Date(dateString);
    return date.toLocaleString('en-IN', {
        timeZone: 'Asia/Kolkata',
        year: 'numeric',
        month: 'short',
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit'
    });
};

const getStatusSeverity = (status) => {
    switch (status) {
        case 'completed': return 'success';
        case 'filed': return 'success';
        case 'ready-to-file': return 'info';
        case 'in-progress': return 'info';
        case 'assigned': return 'secondary';
        case 'received': return 'secondary';
        default: return 'secondary';
    }
};

// Helper for actions dropdown
function getActionOptions(file) {
    const options = [];
    if (isAdminOrManager.value) {
        options.push({ label: 'Edit', icon: 'pi pi-pencil', action: 'edit' });
    }
    options.push({ label: 'View', icon: 'pi pi-eye', action: 'view' });
    if (file.client) {
        options.push({ label: 'Notes', icon: 'pi pi-file', action: 'notes' });
    }
    if (isAdminOrManager.value) {
        options.push({ label: 'Delete', icon: 'pi pi-trash', action: 'delete', class: 'text-danger' });
    }
    return options;
}

function handleAction(action, file) {
    switch (action.action) {
        case 'edit':
            router.push(`/files/${file.id}/edit`);
            break;
        case 'view':
            router.push(`/files/${file.id}`);
            break;
        case 'notes':
            openNotesModal(file.client);
            break;
        case 'delete':
            confirmDelete(file);
            break;
    }
}

const confirmDelete = (file) => {
    confirm.require({
        message: `Are you sure you want to delete this file (${file.file_type} - ${file.assessment_year})?`,
        header: 'Confirm Delete',
        icon: 'pi pi-exclamation-triangle',
        acceptClass: 'p-button-danger',
        accept: async () => {
            try {
                await api.delete(`/files/${file.id}`);
                await fetchFiles();
            } catch (error) {
                console.error('Error deleting file:', error);
            }
        }
    });
};


// Watch for route changes to reload files when switching tabs
import { watch } from 'vue';
watch(
  () => route.fullPath,
  () => {
    fetchFiles()
    selectedAssessmentYear.value = route.query.year ?? null
  },
  { immediate: true }
);

const defaultInjected = ref(false);

watch(
  allAssessmentYears,
  (years) => {
    if (!years.length) return
    if (defaultInjected.value) return
    if (route.query.year) return

    const defaultYear =
      years.includes(getCurrentAssessmentYear())
        ? getCurrentAssessmentYear()
        : years[0]

    defaultInjected.value = true

    router.replace({
      query: {
        ...route.query,
        year: defaultYear
      }
    })
  },
  { immediate: true }
);

/* -------------------- DROPDOWN → ROUTE -------------------- */
watch(selectedAssessmentYear, (val) => {
  if (!val) return
  if (val === route.query.year) return

  router.replace({
    query: {
      ...route.query,
      year: val
    }
  })
});
/* -------------------- lifecycle -------------------- */
onMounted(fetchFiles);
</script>
