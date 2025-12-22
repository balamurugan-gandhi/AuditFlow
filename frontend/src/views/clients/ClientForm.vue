<template>
    <div class="max-w-4xl mx-auto space-y-6">

        <div class="card bg-white dark:bg-surface-800 rounded-xl shadow-sm p-6 border border-surface-200 dark:border-surface-700">
            <h2 class="text-2xl font-bold mb-6 text-surface-900 dark:text-surface-0">{{ isEditing ? 'Edit Client' : 'Add Client' }}</h2>
            
            <!-- Loading Skeleton -->
            <div v-if="fetchingData" class="space-y-6">
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    <div v-for="i in 12" :key="i" class="flex flex-col gap-2">
                        <Skeleton width="8rem" height="1rem"></Skeleton>
                        <Skeleton width="100%" height="2.5rem"></Skeleton>
                    </div>
                </div>
                <div class="flex flex-col gap-2">
                    <Skeleton width="5rem" height="1rem"></Skeleton>
                    <Skeleton width="100%" height="5rem"></Skeleton>
                </div>
            </div>

            <!-- Actual Form -->
            <form v-else @submit.prevent="handleSubmit" class="space-y-6">
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    <!-- Basic Info -->
                    <div class="flex flex-col gap-2">
                        <label for="business_name" class="text-sm font-medium text-surface-700 dark:text-surface-200">Business Name *</label>
                        <InputText id="business_name" v-model="form.business_name" :invalid="!!errors.business_name" />
                        <small class="text-red-500" v-if="errors.business_name">{{ errors.business_name[0] }}</small>
                    </div>
                    <div class="flex flex-col gap-2">
                        <label for="file_id" class="text-sm font-medium text-surface-700 dark:text-surface-200">File ID</label>
                        <InputText id="file_id" v-model="form.file_id" :invalid="!!errors.file_id" />
                        <small class="text-red-500" v-if="errors.file_id">{{ errors.file_id[0] }}</small>
                    </div>
                    <div class="flex flex-col gap-2">
                        <label for="contact_person" class="text-sm font-medium text-surface-700 dark:text-surface-200">Contact Person</label>
                        <InputText id="contact_person" v-model="form.contact_person" :invalid="!!errors.contact_person" />
                        <small class="text-red-500" v-if="errors.contact_person">{{ errors.contact_person[0] }}</small>
                    </div>
                    <div class="flex flex-col gap-2">
                        <label for="business_type" class="text-sm font-medium text-surface-700 dark:text-surface-200">Business Type</label>
                        <Dropdown id="business_type" v-model="form.business_type" :options="businessTypes" placeholder="Select Type" :invalid="!!errors.business_type" />
                        <small class="text-red-500" v-if="errors.business_type">{{ errors.business_type[0] }}</small>
                    </div>                    
                    <!-- Contact Info -->
                    <div class="flex flex-col gap-2">
                        <label for="email" class="text-sm font-medium text-surface-700 dark:text-surface-200">Email</label>
                        <InputText id="email" v-model="form.email" type="email" :invalid="!!errors.email" />
                        <small class="text-red-500" v-if="errors.email">{{ errors.email[0] }}</small>
                    </div>
                    <div class="flex flex-col gap-2">
                        <label for="phone" class="text-sm font-medium text-surface-700 dark:text-surface-200">Phone</label>
                        <InputText id="phone" v-model="form.phone" :invalid="!!errors.phone" />
                        <small class="text-red-500" v-if="errors.phone">{{ errors.phone[0] }}</small>
                    </div>
                    <div class="flex flex-col gap-2">
                        <label for="alternate_phone" class="text-sm font-medium text-surface-700 dark:text-surface-200">Alternate Phone</label>
                        <InputText id="alternate_phone" v-model="form.alternate_phone" :invalid="!!errors.alternate_phone" />
                        <small class="text-red-500" v-if="errors.alternate_phone">{{ errors.alternate_phone[0] }}</small>
                    </div>
                    <div class="flex flex-col gap-2">
                        <label for="whatsapp_number" class="text-sm font-medium text-surface-700 dark:text-surface-200">WhatsApp Number</label>
                        <InputText id="whatsapp_number" v-model="form.whatsapp_number" :invalid="!!errors.whatsapp_number" />
                        <small class="text-red-500" v-if="errors.whatsapp_number">{{ errors.whatsapp_number[0] }}</small>
                    </div>

                    <!-- Tax & Filing Info -->
                    <div class="flex flex-col gap-2">
                        <label for="pan_number" class="text-sm font-medium text-surface-700 dark:text-surface-200">PAN Number</label>
                        <InputText id="pan_number" v-model="form.pan_number" class="uppercase" :invalid="!!errors.pan_number" />
                        <small class="text-red-500" v-if="errors.pan_number">{{ errors.pan_number[0] }}</small>
                    </div>
                    <div class="flex flex-col gap-2">
                        <label for="gst_number" class="text-sm font-medium text-surface-700 dark:text-surface-200">GST Number</label>
                        <InputText id="gst_number" v-model="form.gst_number" class="uppercase" :invalid="!!errors.gst_number" />
                        <small class="text-red-500" v-if="errors.gst_number">{{ errors.gst_number[0] }}</small>
                    </div>
                    <div class="flex flex-col gap-2">
                        <label for="tin_number" class="text-sm font-medium text-surface-700 dark:text-surface-200">TIN Number</label>
                        <InputText id="tin_number" v-model="form.tin_number" :invalid="!!errors.tin_number" />
                        <small class="text-red-500" v-if="errors.tin_number">{{ errors.tin_number[0] }}</small>
                    </div>
                    <div class="flex flex-col gap-2">
                        <label for="filing_cycle" class="text-sm font-medium text-surface-700 dark:text-surface-200">Filing Cycle</label>
                        <Dropdown id="filing_cycle" v-model="form.filing_cycle" :options="filingCycles" placeholder="Select Cycle" :invalid="!!errors.filing_cycle" />
                        <small class="text-red-500" v-if="errors.filing_cycle">{{ errors.filing_cycle[0] }}</small>
                    </div>

                    <!-- Address -->
                    <div class="flex flex-col gap-2 md:col-span-2 lg:col-span-3">
                        <label for="address" class="text-sm font-medium text-surface-700 dark:text-surface-200">Address</label>
                        <Textarea id="address" v-model="form.address" rows="3" :invalid="!!errors.address" />
                        <small class="text-red-500" v-if="errors.address">{{ errors.address[0] }}</small>
                    </div>
                </div>

                <div class="flex justify-end gap-3 pt-4 border-t border-surface-200 dark:border-surface-700">
                    <Button label="Cancel" severity="secondary" @click="router.back()" />
                    <Button type="submit" :label="isEditing ? 'Update Client' : 'Create Client'" :loading="loading" />
                </div>
            </form>
        </div>
        <div v-if="serverError" class="p-4 bg-red-50 border border-red-200 rounded-lg text-red-700 text-sm"
        >   {{ serverError }}
        </div>
        <div v-if="Object.keys(errors).length"
            class="p-4 bg-red-50 text-red-700 rounded-lg border border-red-200">
            Please fix the highlighted errors above.
        </div>
    </div>
</template>

<script setup>
import { ref, onMounted, computed } from 'vue';
import { useRouter, useRoute } from 'vue-router';
import api from '../../api/axios';
import Button from 'primevue/button';
import InputText from 'primevue/inputtext';
import Textarea from 'primevue/textarea';
import Dropdown from 'primevue/dropdown';
import Skeleton from 'primevue/skeleton';

const router = useRouter();
const route = useRoute();
const isEditing = computed(() => !!route.params.id);
const loading = ref(false);
const fetchingData = ref(!!route.params.id); // Start as true if editing

const businessTypes = ['Proprietorship', 'Partnership', 'LLP', 'Private Limited', 'Public Limited', 'Trust', 'Society', 'Other'];
const filingCycles = ['Monthly', 'Quarterly', 'Yearly'];
const errors = ref({});
const serverError = ref("");

const form = ref({
    business_name: '',
    contact_person: '',
    file_id:'',
    email: '',
    phone: '',
    alternate_phone: '',
    whatsapp_number: '',
    pan_number: '',
    gst_number: '',
    tin_number: '',
    business_type: '',
    filing_cycle: 'Yearly',
    address: ''
});

const fetchClient = async () => {
    if (!isEditing.value) return;
    
    fetchingData.value = true;
    try {
        const response = await api.get(`/clients/${route.params.id}`);
        form.value = response.data;
    } catch (error) {
        console.error('Error fetching client:', error);
        router.push('/clients');
    } finally {
        fetchingData.value = false;
    }
};

const handleSubmit = async () => {
    loading.value = true;
    errors.value = {};
    serverError.value = "";

    try {
        let response;

        if (isEditing.value) {
            response = await api.put(`/clients/${route.params.id}`, form.value);
        } else {
            response = await api.post('/clients', form.value);
        }

        router.push('/clients');

    } catch (error) {
        console.error("API Error:", error);

        // --- Laravel 422 Validation Errors ---
        if (error.response?.status === 422) {
            errors.value = error.response.data.errors || {};
            return;
        }

        // --- Laravel 500 Server Errors ---
        if (error.response?.status === 500) {
            serverError.value = `Internal server error. Please try again later. ${error.response?.data?.message}`;
            return;
        }

        // --- Unauthorized / Forbidden ---
        if (error.response?.status === 403 || error.response?.status === 401) {
            serverError.value = "You are not authorized to perform this action.";
            return;
        }

        // --- Network Failure ---
        if (!error.response) {
            serverError.value = "Network error — please check your internet connection.";
            return;
        }

        // --- Other unexpected cases ---
        serverError.value = error.response.data.message || "Unexpected error occurred.";
    }

    finally {
        loading.value = false;
    }
};

onMounted(() => {
    fetchClient();
});
</script>
