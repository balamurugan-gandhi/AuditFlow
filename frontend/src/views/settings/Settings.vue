<template>
    <div class="space-y-6">
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <!-- Database Management Card -->
            <div class="card bg-white dark:bg-surface-800 rounded-xl shadow-sm p-6">
                <div class="grid grid-cols-[auto_1fr] items-center gap-4 mb-6">
                    <div class="w-12 h-12 rounded-xl bg-blue-50 dark:bg-blue-500/10 flex items-center justify-center">
                        <i class="pi pi-database text-blue-500 text-xl"></i>
                    </div>
                    <div>
                        <h3 class="text-lg font-semibold text-surface-900 dark:text-surface-0 m-0 leading-tight">Database Management</h3>
                        <p class="text-surface-500 dark:text-surface-400 text-sm mt-1 mb-0">Manage your application data backups.</p>
                    </div>
                </div>

                <div class="space-y-4">
                    <div class="flex items-center justify-between p-4 bg-surface-50 dark:bg-surface-900 rounded-xl border border-surface-100 dark:border-surface-700 gap-4">
                        <div>
                            <h4 class="font-medium text-surface-900 dark:text-surface-0 m-0 text-base">Backup Database</h4>
                            <p class="text-sm text-surface-500 dark:text-surface-400 mt-1 mb-0">Download a full SQL dump</p>
                        </div>
                        <Button label="Download" icon="pi pi-download" :loading="downloading" @click="downloadBackup" severity="secondary" outlined />
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref } from 'vue';
import api from '../../api/axios';
import Button from 'primevue/button';

const downloading = ref(false);

const downloadBackup = async () => {
    downloading.value = true;
    try {
        const response = await api.get('/backup/download', {
            responseType: 'blob'
        });

        // Create a link to download the file
        const url = window.URL.createObjectURL(new Blob([response.data]));
        const link = document.createElement('a');
        link.href = url;
        
        // Extract filename from header if available, or generate one
        const contentDisposition = response.headers['content-disposition'];
        let filename = 'backup.sql';
        if (contentDisposition) {
            const filenameMatch = contentDisposition.match(/filename="?([^"]+)"?/);
            if (filenameMatch.length === 2)
                filename = filenameMatch[1];
        } else {
             filename = `backup-${new Date().toISOString().slice(0, 19).replace(/:/g, '-')}.sql`;
        }

        link.setAttribute('download', filename);
        document.body.appendChild(link);
        link.click();
        document.body.removeChild(link);
        window.URL.revokeObjectURL(url);
    } catch (error) {
        console.error('Backup download failed:', error);
        alert('Failed to download backup. Please try again.');
    } finally {
        downloading.value = false;
    }
};
</script>
