<template>
    <div class="max-w-4xl mx-auto py-8">
        <h2 class="text-2xl font-bold mb-6 flex items-center gap-2">
            <i class="pi pi-plug text-primary-500 text-2xl"></i>
            Plugins
        </h2>
        <div class="card bg-white dark:bg-surface-800 rounded-xl shadow-sm p-6">
            <p class="mb-4 text-surface-700 dark:text-surface-300">Manage and configure plugins for your application here.</p>
            <DataTable :value="plugins" class="p-datatable-sm" :showGridlines="false">
                <Column field="icon" header="" style="width: 60px">
                    <template #body="slotProps">
                        <i :class="slotProps.data.icon + ' text-2xl'" :style="slotProps.data.iconColor ? 'color:' + slotProps.data.iconColor : ''"></i>
                    </template>
                </Column>
                <Column field="name" header="Plugin"></Column>
                <Column field="enabled" header="Enabled" style="width: 120px">
                    <template #body="slotProps">
                        <InputSwitch v-model="slotProps.data.enabled" @change="togglePlugin(slotProps.data)" />
                    </template>
                </Column>
                <Column header="Settings" style="width: 120px">
                    <template #body="slotProps">
                        <Button v-if="slotProps.data.settingsRoute" icon="pi pi-cog" label="Settings" size="small" @click="goToPluginSettings(slotProps.data.settingsRoute)" />
                    </template>
                </Column>
            </DataTable>
        </div>
    </div>
</template>

<script setup>
import { useRouter } from 'vue-router';
import { ref, onMounted } from 'vue';
import DataTable from 'primevue/datatable';
import Column from 'primevue/column';
import InputSwitch from 'primevue/inputswitch';
import Button from 'primevue/button';
import api from '../api/axios';

const router = useRouter();

const plugins = ref([]);

onMounted(fetchPlugins);

async function fetchPlugins() {
    try {
        const res = await api.get('/plugins');
        plugins.value = res.data.map(p => ({
            ...p,
            enabled: !!p.enabled, // Normalize to boolean
            icon: p.icon || 'pi pi-cog',
            iconColor: p.icon_color || '',
            settingsRoute: p.name === 'whatsapp-meta' ? '/plugins/whatsapp-meta' : '',
        }));
    } catch (e) {
        // eslint-disable-next-line no-console
        console.error('Failed to load plugins', e);
    }
}

function goToPluginSettings(route) {
    router.push(route);
}

async function togglePlugin(plugin) {
    try {
        const res = await api.put(`/plugins/${plugin.id}`, { enabled: plugin.enabled });
        // Update local plugin state only
        const updated = res.data;
        const idx = plugins.value.findIndex(p => p.id === updated.id);
        if (idx !== -1) {
            plugins.value[idx].enabled = updated.enabled;
        }
    } catch (e) {
        // eslint-disable-next-line no-console
        console.error('Failed to update plugin', e);
    }
}
</script>
