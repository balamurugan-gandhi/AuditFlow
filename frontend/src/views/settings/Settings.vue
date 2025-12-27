<template>
    <Toast />
    <div class="max-w-4xl mx-auto space-y-6">
        <!-- Company Information -->
        <div class="card bg-white dark:bg-surface-800 rounded-xl shadow-sm p-6">
            <h2 class="text-xl font-bold text-surface-900 dark:text-surface-0 mb-6">Company Information</h2>
            <form @submit.prevent="saveCompanyInfo" class="space-y-6">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div class="flex flex-col gap-2">
                        <label for="company_name" class="text-sm font-medium text-surface-700 dark:text-surface-200">Company Name</label>
                        <InputText id="company_name" v-model="companyInfo.company_name" />
                    </div>
                    <div class="flex flex-col gap-2">
                        <label for="contact_name" class="text-sm font-medium text-surface-700 dark:text-surface-200">Contact Name</label>
                        <InputText id="contact_name" v-model="companyInfo.contact_name" />
                    </div>
                    <div class="flex flex-col gap-2">
                        <label for="company_email" class="text-sm font-medium text-surface-700 dark:text-surface-200">Email</label>
                        <InputText id="company_email" type="email" v-model="companyInfo.email" />
                    </div>
                    <div class="flex flex-col gap-2">
                        <label for="company_phone" class="text-sm font-medium text-surface-700 dark:text-surface-200">Phone</label>
                        <InputText id="company_phone" v-model="companyInfo.phone" />
                    </div>
                    <div class="flex flex-col gap-2">
                        <label for="company_whatsapp" class="text-sm font-medium text-surface-700 dark:text-surface-200">WhatsApp</label>
                        <InputText id="company_whatsapp" v-model="companyInfo.whatsapp" />
                    </div>
                    <div class="flex flex-col gap-2">
                        <label for="gst_number" class="text-sm font-medium text-surface-700 dark:text-surface-200">GST Number</label>
                        <InputText id="gst_number" v-model="companyInfo.gst_number" />
                    </div>
                    <div class="flex flex-col gap-2">
                        <label for="pan_number" class="text-sm font-medium text-surface-700 dark:text-surface-200">PAN Number</label>
                        <InputText id="pan_number" v-model="companyInfo.pan_number" />
                    </div>
                    <div class="flex flex-col gap-2">
                        <label for="tan_number" class="text-sm font-medium text-surface-700 dark:text-surface-200">TAN Number</label>
                        <InputText id="tan_number" v-model="companyInfo.tan_number" />
                    </div>
                    <div class="flex flex-col gap-2">
                        <label for="license_number" class="text-sm font-medium text-surface-700 dark:text-surface-200">License Number</label>
                        <InputText id="license_number" v-model="companyInfo.license_number" />
                    </div>
                    <div class="flex flex-col gap-2">
                        <label for="company_logo" class="text-sm font-medium text-surface-700 dark:text-surface-200">Logo</label>
                        <div
                            class="relative flex flex-col items-center justify-center border-2 border-dashed border-surface-200 dark:border-surface-700 rounded-lg bg-surface-50 dark:bg-surface-900 py-4 px-4 cursor-pointer transition hover:bg-primary-50"
                            @dragover.prevent
                            @drop.prevent="handleLogoDrop"
                        >
                            <input type="file" id="company_logo" @change="handleLogoUpload" accept="image/*" class="absolute inset-0 opacity-0 cursor-pointer" style="z-index:2;" />
                            <div v-if="logoPreview" class="flex flex-col items-center gap-2">
                                <img :src="logoPreview" alt="Logo preview" class="h-20 object-contain rounded border border-surface-200 dark:border-surface-700 shadow" />
                                <span class="text-xs text-surface-500 dark:text-surface-400">Preview</span>
                            </div>
                            <div v-else class="flex flex-col items-center gap-2 text-surface-400">
                                <i class="pi pi-image text-4xl"></i>
                                <span class="text-xs">Drag & drop or click to upload</span>
                            </div>
                            <Button v-if="logoPreview" icon="pi pi-times" text severity="danger" @click.stop="clearLogo" class="absolute top-2 right-2" v-tooltip="'Remove logo'" />
                        </div>
                        <div v-if="logoFile" class="mt-2 text-xs text-surface-500 dark:text-surface-400">
                            <span>{{ logoFile.name }} ({{ (logoFile.size/1024).toFixed(1) }} KB)</span>
                        </div>
                    </div>
                    <div class="flex flex-col gap-2 md:col-span-2">
                        <label for="company_address" class="text-sm font-medium text-surface-700 dark:text-surface-200">Address</label>
                        <Textarea id="company_address" v-model="companyInfo.address" rows="3" />
                    </div>
                </div>
                <div class="flex justify-end">
                    <Button type="submit" label="Save Company Info" :loading="savingCompany" />
                </div>
            </form>
        </div>


        <!-- Application License -->
        <div class="card bg-white dark:bg-surface-800 rounded-xl shadow-sm p-6 border border-surface-200 dark:border-surface-700">
            <div class="grid grid-cols-[auto_1fr] items-center gap-4 mb-6">
                <div class="w-12 h-12 rounded-xl bg-purple-50 dark:bg-purple-500/10 flex items-center justify-center">
                    <i class="pi pi-key text-purple-500 text-xl"></i>
                </div>
                <div>
                    <h3 class="text-lg font-semibold text-surface-900 dark:text-surface-0 m-0 leading-tight">Application Licensed with Klock Techlogies</h3>
                    <p class="text-surface-500 dark:text-surface-400 text-sm mt-1 mb-0">Manage your AuditFlow application license.</p>
                </div>
            </div>

            <div class="space-y-6">
                <div class="flex flex-col md:flex-row md:items-center justify-between p-4 bg-surface-50 dark:bg-surface-900 rounded-xl border border-surface-100 dark:border-surface-700 gap-4">
                    <div class="flex items-center gap-4">
                        <div :class="['w-3 h-3 rounded-full', licenseStatus.is_valid ? 'bg-green-500' : 'bg-red-500']"></div>
                        <div>
                            <h4 class="font-medium text-surface-900 dark:text-surface-0 m-0 text-base">Status: {{ licenseStatus.status }}</h4>
                            <p v-if="licenseStatus.expires_at" class="text-sm text-surface-500 dark:text-surface-400 mt-1 mb-0">
                                Expires on: {{ new Date(licenseStatus.expires_at).toLocaleDateString() }} 
                                ({{ Math.floor(licenseStatus.remaining_days) }} days remaining)
                            </p>
                            <p v-else class="text-sm text-surface-500 dark:text-surface-400 mt-1 mb-0">
                                {{ licenseStatus.message }}
                            </p>
                        </div>
                    </div>
                </div>

                <div class="flex flex-col gap-2">
                    <label for="update_license" class="text-sm font-medium text-surface-700 dark:text-surface-200">Update License Key</label>
                    <div class="flex flex-col gap-3">
                        <Textarea id="update_license" v-model="newLicenseKey" rows="3" placeholder="Paste your new license key here" />
                        <div class="flex justify-end gap-2">
                            <Button label="Generate Key (Dev)" severity="secondary" @click="generateDevKey" text />
                            <Button label="Update License" :loading="updatingLicense" :disabled="!newLicenseKey" @click="updateLicense" />
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Database Management -->
        <div class="card bg-white dark:bg-surface-800 rounded-xl shadow-sm p-6 border border-surface-200 dark:border-surface-700">
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
</template>

<script setup>
import { ref, onMounted } from 'vue';
import api from '../../api/axios';
import Button from 'primevue/button';
import InputText from 'primevue/inputtext';
import Password from 'primevue/password';
import Toast from 'primevue/toast';
import Textarea from 'primevue/textarea';
import { useToast } from 'primevue/usetoast';

const downloading = ref(false);
const saving = ref(false);
const savingCompany = ref(false);
const updatingLicense = ref(false);
const toast = useToast();

const licenseStatus = ref({
    status: 'loading',
    is_valid: false,
    message: '',
    expires_at: null,
    remaining_days: 0
});
const newLicenseKey = ref('');

const settings = ref({
    whatsapp_access_token: '',
    whatsapp_phone_number_id: '',
    whatsapp_business_account_id: ''
});

const companyInfo = ref({
    company_name: '',
    contact_name: '',
    email: '',
    phone: '',
    whatsapp: '',
    address: '',
    logo: '',
    gst_number: '',
    pan_number: '',
    tan_number: '',
    license_number: ''
});

const logoPreview = ref('');
const logoFile = ref(null);

function clearLogo() {
    logoPreview.value = '';
    logoFile.value = null;
}

function handleLogoDrop(e) {
    const file = e.dataTransfer.files[0];
    if (file) {
        handleLogoUpload({ target: { files: [file] } });
    }
}

const fetchSettings = async () => {
    try {
        const response = await api.get('/settings');
        const data = response.data;
        
        // WhatsApp settings
        settings.value = {
            whatsapp_access_token: data.whatsapp_access_token || '',
            whatsapp_phone_number_id: data.whatsapp_phone_number_id || '',
            whatsapp_business_account_id: data.whatsapp_business_account_id || ''
        };
        
        // Company info
        companyInfo.value = {
            company_name: data.company_name || '',
            contact_name: data.company_contact_name || '',
            email: data.company_email || '',
            phone: data.company_phone || '',
            whatsapp: data.company_whatsapp || '',
            address: data.company_address || '',
            logo: data.company_logo || '',
            gst_number: data.gst_number || '',
            pan_number: data.pan_number || '',
            tan_number: data.tan_number || '',
            license_number: data.license_number || ''
        };
        
        if (companyInfo.value.logo) {
            // Use the backend URL to access the logo
            logoPreview.value = `http://localhost:8080/storage/${companyInfo.value.logo}`;
        }
    } catch (error) {
        console.error('Error fetching settings:', error);
        toast.add({ severity: 'error', summary: 'Error', detail: 'Failed to load settings', life: 3000 });
    }
};

const saveSettings = async () => {
    saving.value = true;
    try {
        await api.post('/settings', { settings: settings.value });
        toast.add({ severity: 'success', summary: 'Success', detail: 'Settings saved successfully', life: 3000 });
    } catch (error) {
        console.error('Error saving settings:', error);
        toast.add({ severity: 'error', summary: 'Error', detail: 'Failed to save settings', life: 3000 });
    } finally {
        saving.value = false;
    }
};

const handleLogoUpload = (event) => {
    const file = event.target.files[0];
    if (file) {
        // Check file size (10MB = 10 * 1024 * 1024 bytes)
        const maxSize = 10 * 1024 * 1024;
        if (file.size > maxSize) {
            toast.add({ 
                severity: 'error', 
                summary: 'File Too Large', 
                detail: 'The logo file is too large. Please upload an image smaller than 10MB.', 
                life: 5000 
            });
            // Clear the file input
            event.target.value = '';
            return;
        }
        
        logoFile.value = file;
        const reader = new FileReader();
        reader.onload = (e) => {
            logoPreview.value = e.target.result;
        };
        reader.readAsDataURL(file);
    }
};

const saveCompanyInfo = async () => {
    savingCompany.value = true;
    try {
        const formData = new FormData();
        formData.append('company_name', companyInfo.value.company_name);
        formData.append('company_contact_name', companyInfo.value.contact_name);
        formData.append('company_email', companyInfo.value.email);
        formData.append('company_phone', companyInfo.value.phone);
        formData.append('company_whatsapp', companyInfo.value.whatsapp);
        formData.append('company_address', companyInfo.value.address);
        formData.append('gst_number', companyInfo.value.gst_number);
        formData.append('pan_number', companyInfo.value.pan_number);
        formData.append('tan_number', companyInfo.value.tan_number);
        formData.append('license_number', companyInfo.value.license_number);
        
        if (logoFile.value) {
            formData.append('company_logo', logoFile.value);
        }
        
        await api.post('/settings', formData, {
            headers: {
                'Content-Type': 'multipart/form-data'
            }
        });
        
        toast.add({ severity: 'success', summary: 'Success', detail: 'Company information saved successfully', life: 3000 });
    } catch (error) {
        console.error('Error saving company info:', error);
        
        // Handle specific error cases
        if (error.response?.status === 413) {
            toast.add({ 
                severity: 'error', 
                summary: 'File Too Large', 
                detail: 'The logo file is too large. Please upload an image smaller than 10MB.', 
                life: 5000 
            });
        } else if (error.response?.status === 422) {
            toast.add({ 
                severity: 'error', 
                summary: 'Validation Error', 
                detail: 'Please check your input and try again.', 
                life: 3000 
            });
        } else {
            toast.add({ 
                severity: 'error', 
                summary: 'Error', 
                detail: 'Failed to save company information. Please try again.', 
                life: 3000 
            });
        }
    } finally {
        savingCompany.value = false;
    }
};

const downloadBackup = async () => {
    downloading.value = true;
    try {
        const response = await api.get('/backup/download', {
            responseType: 'blob'
        });

        const url = window.URL.createObjectURL(new Blob([response.data]));
        const link = document.createElement('a');
        link.href = url;
        
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

const fetchLicenseStatus = async () => {
    try {
        const response = await api.get('/license/status');
        licenseStatus.value = response.data;
    } catch (error) {
        console.error('Error fetching license status:', error);
    }
};

const updateLicense = async () => {
    updatingLicense.value = true;
    try {
        const response = await api.post('/license/update', { license_key: newLicenseKey.value });
        toast.add({ severity: 'success', summary: 'Success', detail: 'License updated successfully', life: 3000 });
        licenseStatus.value = response.data.license;
        newLicenseKey.value = '';
    } catch (error) {
        console.error('Error updating license:', error);
        toast.add({ severity: 'error', summary: 'Error', detail: error.response?.data?.message || 'Failed to update license', life: 3000 });
    } finally {
        updatingLicense.value = false;
    }
};

const generateDevKey = async () => {
    try {
        const response = await api.post('/license/generate', { years: 2 });
        newLicenseKey.value = response.data.license_key;
        toast.add({ severity: 'info', summary: 'Dev Mode', detail: 'Generated a 2-year test key', life: 3000 });
    } catch (error) {
        console.error('Error generating dev key:', error);
    }
};

onMounted(() => {
    fetchSettings();
    fetchLicenseStatus();
});
</script>
