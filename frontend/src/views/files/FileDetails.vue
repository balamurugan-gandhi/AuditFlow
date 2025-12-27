<template>
    <ConfirmDialog />
    <div class="space-y-6">
        <!-- Skeleton Loading State -->
        <div v-if="loading" class="space-y-6">
            <div class="flex items-center gap-4">
                <Skeleton shape="circle" size="3rem"></Skeleton>
                <div class="flex-1">
                    <Skeleton width="10rem" height="2rem" class="mb-2"></Skeleton>
                    <Skeleton width="15rem" height="1rem"></Skeleton>
                </div>
                <Skeleton width="6rem" height="2rem"></Skeleton>
            </div>
            <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
                <div class="lg:col-span-9 space-y-6">
                    <Skeleton width="100%" height="30rem" borderRadius="12px"></Skeleton>
                </div>
                <div class="lg:col-span-3 space-y-6">
                    <Skeleton width="100%" height="20rem" borderRadius="12px"></Skeleton>
                </div>
            </div>
        </div>

        <!-- Actual Content --> 
        <div v-else-if="file" class="space-y-6 animate-fade-in">
            <!-- Past Due Notification Flag -->
                <div v-if="isPastDue"
                class="flex items-center gap-3 bg-red-50 border border-red-200 rounded-lg px-4 py-2 mb-2"
                >
                    <i class="pi pi-exclamation-triangle text-red-600 text-xl"></i>
                    <span class="text-red-700 font-semibold">
                        This file is <b>past due</b>! Estimated completion was
                        {{ pastDueDays }} day<span v-if="pastDueDays !== 1">s</span> ago.
                    </span>
                </div>
                <div v-else-if="isDueSoon"
                class="flex items-center gap-3 bg-orange-50 border border-orange-200 rounded-lg px-4 py-2 mb-2"
                >
                    <i class="pi pi-clock text-orange-600 text-xl"></i>
                    <span class="text-orange-700 font-semibold">
                        This file is due in
                        <b>{{ dueSoonDays }}</b> day<span v-if="dueSoonDays !== 1">s</span>.
                    </span>
                </div>

            <!-- Header -->
            <div class="flex items-center gap-4">
                <Button icon="pi pi-arrow-left" text rounded @click="router.back()" />
                <div>
                    <h1 class="text-2xl font-bold text-surface-900 dark:text-surface-0 flex items-center gap-3">
                        {{ file.file_type }}
                        <Tag :value="file.status" :severity="getStatusSeverity(file.status)" class="text-sm px-2 py-1" />
                    </h1>
                    <p class="text-surface-500 dark:text-surface-400 mt-1 flex items-center gap-2">
                        <i class="pi pi-building"></i>
                        {{ file.client?.business_name }}
                        <span class="text-surface-300 dark:text-surface-600">|</span>
                        <i class="pi pi-calendar"></i>
                        {{ file.assessment_year }}
                    </p>
                </div>
                <div class="ml-auto flex gap-2">
                    <Button v-if="canEdit" label="Edit" icon="pi pi-pencil" severity="secondary" outlined @click="router.push(`/files/${file.id}/edit`)" />
                </div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
                <!-- Main Content -->
                <div class="lg:col-span-9 space-y-6">
                    <div class="card bg-white dark:bg-surface-800 rounded-xl shadow-sm border border-surface-100 dark:border-surface-700 overflow-hidden">
                        <Tabs value="0">
                            <TabList class="px-4 pt-2">
                                <Tab value="0">Work Logs</Tab>
                                <Tab value="1">Documents</Tab>
                            </TabList>
                            <TabPanels class="p-6">
                                <!-- Work Logs Tab -->
                                <TabPanel value="0">
                                    <div class="flex justify-between items-center mb-6">
                                        <h3 class="text-lg font-semibold m-0">Work History</h3>
                                        <Button label="Log Work" icon="pi pi-plus" size="small" @click="showLogDialog = true" />
                                    </div>
                                    
                                    <!-- Loading Skeletons -->
                                    <div v-if="loading || loadingLogs" class="space-y-4">
                                        <div v-for="i in 3" :key="'skeleton-log-' + i" class="relative pl-6 border-l-2 border-surface-200 dark:border-surface-700 pb-6">
                                            <div class="absolute -left-[9px] top-0 w-4 h-4 rounded-full bg-surface-200 dark:bg-surface-700"></div>
                                            <div class="bg-surface-50 dark:bg-surface-900 p-4 rounded-lg space-y-3">
                                                <div class="flex justify-between items-start">
                                                    <Skeleton width="8rem" height="1rem"></Skeleton>
                                                    <Skeleton width="5rem" height="0.75rem"></Skeleton>
                                                </div>
                                                <Skeleton width="100%" height="0.875rem"></Skeleton>
                                                <Skeleton width="70%" height="0.875rem"></Skeleton>
                                                <Skeleton width="4rem" height="1.5rem" borderRadius="0.375rem"></Skeleton>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Actual Work Logs -->
                                    <div v-else class="space-y-4">
                                        <div v-for="log in workLogs" :key="log.id" class="relative pl-6 border-l-2 border-surface-200 dark:border-surface-700 pb-6 last:pb-0">
                                            <div class="absolute -left-[9px] top-0 w-4 h-4 rounded-full bg-primary-500 border-4 border-white dark:border-surface-800"></div>
                                            <div class="bg-surface-50 dark:bg-surface-900 p-4 rounded-lg">
                                                <div class="flex justify-between items-start mb-2">
                                                    <span class="font-medium text-surface-900 dark:text-surface-0">{{ log.user?.name }}</span>
                                                    <span class="text-xs text-surface-500">{{ formatDate(log.date) }}</span>
                                                </div>
                                                <p class="text-surface-600 dark:text-surface-300 text-sm mb-2">{{ log.description }}</p>
                                                <Tag :value="log.hours_worked + ' hrs'" severity="info" class="text-xs" />
                                            </div>
                                        </div>
                                        <div v-if="workLogs.length === 0" class="text-center py-12">
                                              <div class="flex items-center gap-4">
                                            <div class="w-16 h-16 bg-surface-100 dark:bg-surface-700 rounded-full flex items-center justify-center mx-auto mb-4 text-surface-400">
                                                <i class="pi pi-clock text-2xl"></i>
                                            </div>
                                            <div>
                                            <p class="text-surface-500 font-medium">No work logged yet</p>
                                            <p class="text-surface-400 text-sm mt-1">Start tracking time for this file</p>
                                             </div>
                                            </div>
                                        </div>
                                    </div>
                                </TabPanel>

                                <!-- Documents Tab -->
                                <TabPanel value="1">
                                    <div class="flex justify-between items-center mb-6">
                                        <h3 class="text-lg font-semibold m-0">Documents</h3>
                                        <div>
                                            <Button label="Upload" icon="pi pi-upload" size="small" @click="triggerFileUpload" :loading="uploading" />
                                            <input type="file" ref="fileInput" class="hidden" @change="handleFileUpload" />
                                        </div>
                                    </div>

                                    <!-- Loading Skeletons -->
                                    <div v-if="loading || loadingDocs" class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                        <div v-for="i in 4" :key="'skeleton-doc-' + i" class="bg-surface-50 dark:bg-surface-900 p-4 rounded-xl">
                                            <div class="flex items-start gap-3">
                                                <Skeleton shape="square" size="2.5rem" borderRadius="0.5rem"></Skeleton>
                                                <div class="flex-1 space-y-2">
                                                    <Skeleton width="80%" height="1rem"></Skeleton>
                                                    <Skeleton width="60%" height="0.75rem"></Skeleton>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Actual Documents -->
                                    <div v-else class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                        <div v-for="doc in documents" :key="doc.id" class="group bg-surface-50 dark:bg-surface-900 p-4 rounded-xl border border-transparent hover:border-primary-200 dark:hover:border-primary-800 transition-all">
                                            <div class="flex items-start gap-3">
                                                <div class="w-10 h-10 rounded-lg bg-white dark:bg-surface-800 flex items-center justify-center text-primary-600 shadow-sm">
                                                    <i class="pi pi-file text-xl"></i>
                                                </div>
                                                <div class="flex-1 min-w-0">
                                                    <p class="font-medium text-surface-900 dark:text-surface-0 truncate mb-1">{{ doc.original_name }}</p>
                                                    <p class="text-xs text-surface-500 dark:text-surface-400">{{ formatSize(doc.size) }} • {{ formatDate(doc.created_at) }}</p>
                                                </div>
                                                <div class="flex gap-1 opacity-0 group-hover:opacity-100 transition-opacity">
                                                    <Button icon="pi pi-download" text rounded severity="secondary" size="small" @click="downloadDocument(doc)" :loading="downloadingDocs[doc.id]" />
                                                    <Button icon="pi pi-trash" text rounded severity="danger" size="small" @click="deleteDocument(doc)" :loading="deletingDocs[doc.id]" />
                                                </div>
                                            </div>
                                        </div>
                                        <div v-if="documents.length === 0" class="col-span-full text-center py-12">
                                            <div class="flex flex-col items-center gap-3">
                                                <div class="w-16 h-16 bg-surface-100 dark:bg-surface-700 rounded-full flex items-center justify-center text-surface-400">
                                                    <i class="pi pi-folder-open text-2xl"></i>
                                                </div>

                                                <div>
                                                    <p class="text-surface-500 font-medium">No documents uploaded</p>
                                                    <p class="text-surface-400 text-sm mt-1">Upload relevant files here</p>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </TabPanel>
                            </TabPanels>
                        </Tabs>
                    </div>
                     <!-- Notes Table -->
                    <div class="card bg-white dark:bg-surface-800 p-6 rounded-xl shadow-sm border border-surface-100 dark:border-surface-700 mt-6">
                        <h3 class="text-lg font-semibold mb-6 flex items-center gap-2">
                            <i class="pi pi-align-left text-orange-500"></i>
                            Notes
                        </h3>
                        <DataTable :value="fileNotes" :loading="notesLoading" stripedRows :showGridlines="false" class="p-datatable-sm mb-4">
                            <template #empty>
                                <div class="text-center p-4">
                                    <i class="pi pi-info-circle text-4xl text-surface-400 mb-4"></i>
                                    <h3 class="text-xl font-semibold text-surface-700 dark:text-surface-300 mb-2">No Notes Found</h3>
                                    <p class="text-surface-500 dark:text-surface-400">
                                        No notes have been added for this file yet.
                                    </p>
                                </div>
                            </template>
                            <Column field="reason" header="Reason" style="min-width: 200px"></Column>
                            <Column field="user.name" header="User" style="min-width: 120px"></Column>
                            <Column field="created_at" header="Date" style="min-width: 120px">
                                <template #body="slotProps">
                                    {{ formatDate(slotProps.data.created_at) }}
                            </template>
                            </Column>     
                        </DataTable>
                        <div class="flex justify-end">
                            <Button label="Add Note" icon="pi pi-plus" @click="openAddNoteDialog" />
                        </div>
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
                </div>

                <!-- Sidebar Info -->
                <div class="lg:col-span-3 space-y-6">
                    <div class="card bg-white dark:bg-surface-800 p-6 rounded-xl shadow-sm border border-surface-100 dark:border-surface-700">
                        <h3 class="text-lg font-semibold mb-6 flex items-center gap-2">
                            <i class="pi pi-info-circle text-primary-500"></i>
                            File Details
                        </h3>
                        <div class="space-y-6">
                            <div class="flex items-start gap-3">
                                <div class="w-8 h-8 rounded-full bg-blue-50 dark:bg-blue-900/20 flex items-center justify-center text-blue-600 flex-shrink-0">
                                    <i class="pi pi-user"></i>
                                </div>
                                <div>
                                    <label class="text-xs font-medium text-surface-500 uppercase block mb-1">Assignee</label>
                                    <p class="text-surface-900 dark:text-surface-0 font-medium">{{ file.assignee?.name || 'Unassigned' }}</p>
                                </div>
                            </div>
                            
                            <div class="flex items-start gap-3">
                                <div class="w-8 h-8 rounded-full bg-orange-50 dark:bg-orange-900/20 flex items-center justify-center text-orange-600 flex-shrink-0">
                                    <i class="pi pi-align-left"></i>
                                </div>
                                <div>
                                    <label class="text-xs font-medium text-surface-500 uppercase block mb-1">Admin Notes</label>
                                    <p class="text-surface-900 dark:text-surface-0 text-sm leading-relaxed">{{ file.notes || 'No notes provided' }}</p>
                                </div>
                            </div>

                            <div class="flex items-start gap-3">
                                <div class="w-8 h-8 rounded-full bg-purple-50 dark:bg-purple-900/20 flex items-center justify-center text-purple-600 flex-shrink-0">
                                    <i class="pi pi-calendar"></i>
                                </div>
                                <div>
                                    <label class="text-xs font-medium text-surface-500 uppercase block mb-1">Est. Completion Date</label>
                                    <p class="text-surface-900 dark:text-surface-0 text-sm">{{ file.estimated_completion_date || 'Not set' }}</p>
                                </div>
                            </div>

                            <div class="flex items-start gap-3">
                                <div class="w-8 h-8 rounded-full bg-green-50 dark:bg-green-900/20 flex items-center justify-center text-green-600 flex-shrink-0">
                                    <i class="pi pi-calendar-plus"></i>
                                </div>
                                <div>
                                    <label class="text-xs font-medium text-surface-500 uppercase block mb-1">Created On</label>
                                    <p class="text-surface-900 dark:text-surface-0 text-sm">{{ formatDate(file.created_at) }}</p>
                                </div>
                            </div>

                            <div class="flex items-start gap-3">
                                <div class="w-8 h-8 rounded-full bg-red-50 dark:bg-red-900/20 flex items-center justify-center text-red-600 flex-shrink-0">
                                    <i class="pi pi-chart-bar"></i>
                                </div>
                                <div>
                                    <label class="text-xs font-medium text-surface-500 uppercase block mb-1">Turnover</label>
                                    <p class="text-surface-900 dark:text-surface-0 text-sm">{{ file.turnover || 'Not set' }}</p>
                                </div>
                            </div>
                        </div>
                    </div>                   
                </div>
            </div>
        </div>

        <!-- Log Work Dialog -->
        <Dialog v-model:visible="showLogDialog" modal header="Log Work" :style="{ width: '30rem' }">
            <div class="space-y-4">
                <div class="flex flex-col gap-2">
                    <label for="date" class="text-sm font-medium">Date</label>
                    <InputText id="date" type="date" v-model="logForm.date" class="w-full" />
                </div>
                <div class="flex flex-col gap-2">
                    <label for="hours" class="text-sm font-medium">Hours</label>
                    <InputNumber id="hours" v-model="logForm.hours_worked" :min="0.1" :max="24" :minFractionDigits="1" class="w-full" />
                </div>
                <div class="flex flex-col gap-2">
                    <label for="desc" class="text-sm font-medium">Description</label>
                    <Textarea id="desc" v-model="logForm.description" rows="3" class="w-full" />
                </div>
            </div>
            <template #footer>
                <Button label="Cancel" text severity="secondary" @click="showLogDialog = false" />
                <Button label="Save" @click="saveWorkLog" :loading="savingLog" />
            </template>
        </Dialog>
    </div>
</template>

<script setup>
import { ref, onMounted, computed } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import api from '../../api/axios';
import Button from 'primevue/button';
import Tag from 'primevue/tag';
import Tabs from 'primevue/tabs';
import TabList from 'primevue/tablist';
import Tab from 'primevue/tab';
import TabPanels from 'primevue/tabpanels';
import TabPanel from 'primevue/tabpanel';
import Dialog from 'primevue/dialog';
import InputText from 'primevue/inputtext';
import InputNumber from 'primevue/inputnumber';
import Textarea from 'primevue/textarea';
import Skeleton from 'primevue/skeleton';
import DataTable from 'primevue/datatable';
import Column from 'primevue/column';
import { useConfirm } from 'primevue/useconfirm';
import ConfirmDialog from 'primevue/confirmdialog';

import { useAuthStore } from '../../stores/auth';

const route = useRoute();
const router = useRouter();
const authStore = useAuthStore();
const fileId = route.params.id;

const canEdit = computed(() => {
    return authStore.user?.roles?.some(role => ['admin', 'manager'].includes(role.name));
});

const file = ref(null);
const workLogs = ref([]);
const documents = ref([]);
const loading = ref(true);
const loadingLogs = ref(false);
const loadingDocs = ref(false);
const uploading = ref(false);
const downloadingDocs = ref({});
const deletingDocs = ref({});

// Work Log State
const showLogDialog = ref(false);
const savingLog = ref(false);
const logForm = ref({
    date: new Date().toISOString().split('T')[0],
    hours_worked: 1,
    description: ''
});

// No external date library needed
// ...existing code...
const daysDiff = computed(() => {
    if (!file.value?.estimated_completion_date) return null;

    const dueDate = new Date(file.value.estimated_completion_date);
    if (isNaN(dueDate.getTime())) return null;

    const today = new Date();
    today.setHours(0, 0, 0, 0);
    dueDate.setHours(0, 0, 0, 0);

    return Math.floor((dueDate - today) / (1000 * 60 * 60 * 24));
});
const isPastDue = computed(() => {
    if (!file.value) return false;
    if (file.value.status === 'completed' || file.value.payment_id) return false;
    return daysDiff.value !== null && daysDiff.value < 0;
});

const pastDueDays = computed(() => {
    return isPastDue.value ? Math.abs(daysDiff.value) : 0;
});
const isDueSoon = computed(() => {
    if (!file.value) return false;
    if (file.value.status === 'completed' || file.value.payment_id) return false;
    return daysDiff.value !== null && daysDiff.value >= 0 && daysDiff.value <= 5;
});

const dueSoonDays = computed(() => {
    return isDueSoon.value ? daysDiff.value : 0;
});

// File Upload State
const fileInput = ref(null);

const fetchData = async () => {
    try {
        const [fileRes, logsRes, docsRes] = await Promise.all([
            api.get(`/files/${fileId}`),
            api.get(`/files/${fileId}/logs`),
            api.get(`/files/${fileId}/documents`)
        ]);
        file.value = fileRes.data;
        workLogs.value = logsRes.data || [];
        documents.value = docsRes.data || [];
    } catch (error) {
        console.error('Error fetching data:', error);
    } finally {
        loading.value = false;
    }
};

const saveWorkLog = async () => {
    savingLog.value = true;
    try {
        await api.post(`/files/${fileId}/worklogs`, logForm.value);
        showLogDialog.value = false;
        logForm.value = { date: new Date().toISOString().split('T')[0], hours_worked: 1, description: '' };
        loadingLogs.value = true;
        // Refresh logs
        const res = await api.get(`/files/${fileId}/logs`);
        workLogs.value = res.data || [];
        loadingLogs.value = false;
    } catch (error) {
        console.error('Error saving log:', error);
    } finally {
        savingLog.value = false;
    }
};

const triggerFileUpload = () => {
    fileInput.value.click();
};

const handleFileUpload = async (event) => {
    const file = event.target.files[0];
    if (!file) return;

    const formData = new FormData();
    formData.append('file', file);
    formData.append('type', file.name.split('.').pop() || 'unknown');

    uploading.value = true;
    try {
        await api.post(`/files/${fileId}/documents`, formData, {
            headers: { 'Content-Type': 'multipart/form-data' }
        });
        loadingDocs.value = true;
        // Refresh docs
        const res = await api.get(`/files/${fileId}/documents`);
        documents.value = res.data;
        loadingDocs.value = false;
    } catch (error) {
        console.error('Error uploading file:', error);
    } finally {
        uploading.value = false;
        event.target.value = ''; // Reset input
    }
};

const deleteDocument = async (doc) => {
    if (!confirm('Delete this document?')) return;
    deletingDocs.value[doc.id] = true;
    try {
        await api.delete(`/documents/${doc.id}`);
        documents.value = documents.value.filter(d => d.id !== doc.id);
    } catch (error) {
        console.error('Error deleting document:', error);
    } finally {
        delete deletingDocs.value[doc.id];
    }
};

const downloadDocument = async (doc) => {
    downloadingDocs.value[doc.id] = true;
    try {
        const response = await api.get(`/documents/${doc.id}/download`, {
            responseType: 'blob'
        });
        
        // Create a blob URL and trigger download
        const url = window.URL.createObjectURL(new Blob([response.data]));
        const link = document.createElement('a');
        link.href = url;
        link.setAttribute('download', doc.original_name);
        document.body.appendChild(link);
        link.click();
        link.remove();
        window.URL.revokeObjectURL(url);
    } catch (error) {
        console.error('Error downloading document:', error);
        alert('Failed to download document');
    } finally {
        delete downloadingDocs.value[doc.id];
    }
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

const formatDate = (dateString) => {
    if (!dateString) return 'N/A';
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

const formatSize = (bytes) => {
    if (bytes === 0) return '0 B';
    const k = 1024;
    const sizes = ['B', 'KB', 'MB', 'GB'];
    const i = Math.floor(Math.log(bytes) / Math.log(k));
    return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
};

// --- Notes Section ---
const fileNotes = ref([]);
const notesLoading = ref(false);
const addNoteDialogVisible = ref(false);
const noteForm = ref({ reason: '' });

const fetchNotes = async () => {
    if (!file.value?.client_id || !file.value?.assessment_year) return;
    notesLoading.value = true;
    try {
        const res = await api.get(`/clients/${file.value.client_id}/notes`, {
            params: { assessment_year: file.value.assessment_year }
        });
        fileNotes.value = res.data || [];
    } catch (error) {
        fileNotes.value = [];
    } finally {
        notesLoading.value = false;
    }
};

const openAddNoteDialog = () => {
    noteForm.value = { reason: '' };
    addNoteDialogVisible.value = true;
};

const saveNote = async () => {
    if (!file.value?.client_id || !file.value?.assessment_year) return;
    notesLoading.value = true;
    try {
        await api.post(`/clients/${file.value.client_id}/notes`, {
            ...noteForm.value,
            assessment_year: file.value.assessment_year
        });
        addNoteDialogVisible.value = false;
        await fetchNotes();
    } catch (error) {
        // handle error
    } finally {
        notesLoading.value = false;
    }
};

const confirm = useConfirm();

const deleteNote = (note) => {
    confirm.require({
        message: 'Are you sure you want to delete this note?',
        header: 'Delete Confirmation',
        icon: 'pi pi-exclamation-triangle',
        acceptLabel: 'Yes',
        rejectLabel: 'No',
        accept: async () => {
            try {
                await api.delete(`/clients/${file.value.client_id}/notes/${note.id}`);
                await fetchNotes();
            } catch (error) {
                // handle error
            }
        },
    });
};

onMounted(() => {
    fetchData().then(() => {
        fetchNotes();
    });
});
</script>
