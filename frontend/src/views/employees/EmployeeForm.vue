<template>
    <div class="max-w-2xl mx-auto space-y-6">

        <div class="card bg-white dark:bg-surface-800 rounded-xl shadow-sm p-6">
            <!-- Loading Skeleton -->
            <div v-if="fetchingData" class="space-y-6">
                <div class="grid grid-cols-1 gap-6">
                    <div v-for="i in 5" :key="i" class="flex flex-col gap-2">
                        <Skeleton width="6rem" height="1rem"></Skeleton>
                        <Skeleton width="100%" height="2.5rem"></Skeleton>
                    </div>
                </div>
            </div>

            <!-- Actual Form -->
            <form v-else @submit.prevent="handleSubmit" class="space-y-6">
                <div class="grid grid-cols-1 gap-6">
                    <div class="flex flex-col gap-2">
                        <label for="name" class="text-sm font-medium text-surface-700 dark:text-surface-200">Name</label>
                        <InputText id="name" v-model="form.name" required />
                    </div>
                    <div class="flex flex-col gap-2">
                        <label for="email" class="text-sm font-medium text-surface-700 dark:text-surface-200">Email</label>
                        <InputText id="email" v-model="form.email" type="email" required />
                    </div>
                    <div class="flex flex-col gap-2" v-if="!isEditing">
                        <label for="password" class="text-sm font-medium text-surface-700 dark:text-surface-200">Password</label>
                        <Password id="password" v-model="form.password" :feedback="false" toggleMask required />
                    </div>
                    <div class="flex flex-col gap-2">
                        <label for="role" class="text-sm font-medium text-surface-700 dark:text-surface-200">Role</label>
                        <Dropdown id="role" v-model="form.role" :options="roles" optionLabel="name" optionValue="name" placeholder="Select a Role" class="w-full" />
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div class="flex flex-col gap-2">
                            <label for="phone" class="text-sm font-medium text-surface-700 dark:text-surface-200">Phone</label>
                            <InputText id="phone" v-model="form.phone" />
                        </div>
                        <div class="flex flex-col gap-2">
                            <label for="whatsapp_number" class="text-sm font-medium text-surface-700 dark:text-surface-200">WhatsApp Number</label>
                            <InputText id="whatsapp_number" v-model="form.whatsapp_number" />
                        </div>
                        <div class="flex flex-col gap-2">
                            <label for="dob" class="text-sm font-medium text-surface-700 dark:text-surface-200">Date of Birth</label>
                            <Calendar id="dob" v-model="form.dob" dateFormat="yy-mm-dd" showIcon />
                        </div>
                        <div class="flex flex-col gap-2">
                            <label for="gender" class="text-sm font-medium text-surface-700 dark:text-surface-200">Gender</label>
                            <Dropdown id="gender" v-model="form.gender" :options="['Male', 'Female', 'Other']" placeholder="Select Gender" class="w-full" />
                        </div>
                        <div class="flex flex-col gap-2">
                            <label for="doj" class="text-sm font-medium text-surface-700 dark:text-surface-200">Date of Joining</label>
                            <Calendar id="doj" v-model="form.doj" dateFormat="yy-mm-dd" showIcon />
                        </div>
                    </div>

                    <div class="flex flex-col gap-2">
                        <label for="address" class="text-sm font-medium text-surface-700 dark:text-surface-200">Address</label>
                        <Textarea id="address" v-model="form.address" rows="3" autoResize />
                    </div>
                    
                    <!-- Client Assignment (Disabled) -->
                    <div class="flex flex-col gap-4" v-if="false && form.role === 'employee'">
                        <div class="flex flex-col gap-2">
                            <label class="text-sm font-medium text-surface-700 dark:text-surface-200">Assign Clients</label>
                            <Dropdown 
                                v-model="selectedClientToAdd" 
                                :options="availableClients" 
                                optionLabel="business_name" 
                                optionValue="id" 
                                placeholder="Select Client to Assign" 
                                class="w-full"
                                filter
                                @change="addClient"
                            />
                        </div>

                        <!-- Assigned Clients Table -->
                        <div v-if="form.clients.length > 0" class="border border-surface-200 dark:border-surface-700 rounded-lg overflow-hidden">
                            <table class="w-full text-sm text-left">
                                <thead class="bg-surface-50 dark:bg-surface-900 text-surface-500 dark:text-surface-400">
                                    <tr>
                                        <th class="p-3 font-medium">Business Name</th>
                                        <th class="p-3 font-medium text-right">Actions</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-surface-200 dark:divide-surface-700">
                                    <tr v-for="client in assignedClientObjects" :key="client.id" class="bg-white dark:bg-surface-800">
                                        <td class="p-3 text-surface-900 dark:text-surface-0">{{ client.business_name }}</td>
                                        <td class="p-3 text-right">
                                            <Button icon="pi pi-trash" text rounded severity="danger" size="small" @click="removeClient(client.id)" />
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                        <div v-else class="text-sm text-surface-500 dark:text-surface-400 italic px-1">
                            No clients assigned yet.
                        </div>
                    </div>
                </div>

                <div class="flex justify-end gap-3">
                    <Button label="Cancel" severity="secondary" @click="router.back()" />
                    <Button type="submit" :label="isEditing ? 'Update Employee' : 'Create Employee'" :loading="loading" />
                </div>
            </form>
        </div>
    </div>
</template>

<script setup>
import { ref, onMounted, computed } from 'vue';
import { useRouter, useRoute } from 'vue-router';
import api from '../../api/axios';
import Button from 'primevue/button';
import InputText from 'primevue/inputtext';
import Password from 'primevue/password';
import Dropdown from 'primevue/dropdown';
import Skeleton from 'primevue/skeleton';
import Calendar from 'primevue/calendar';
import Textarea from 'primevue/textarea';

const router = useRouter();
const route = useRoute();
const isEditing = computed(() => !!route.params.id);
const loading = ref(false);
const fetchingData = ref(!!route.params.id);

const roles = ref([
    { name: 'admin' },
    { name: 'manager' },
    { name: 'employee' }
]);
const clients = ref([]);
const selectedClientToAdd = ref(null);

const form = ref({
    name: '',
    email: '',
    password: '',
    role: 'employee',
    clients: [],
    phone: '',
    whatsapp_number: '',
    address: '',
    doj: null,
    dob: null,
    gender: ''
});

const assignedClientObjects = computed(() => {
    return clients.value.filter(c => form.value.clients.includes(c.id));
});

const availableClients = computed(() => {
    return clients.value.filter(c => !form.value.clients.includes(c.id));
});

const addClient = () => {
    if (selectedClientToAdd.value) {
        form.value.clients.push(selectedClientToAdd.value);
        selectedClientToAdd.value = null;
    }
};

const removeClient = (clientId) => {
    form.value.clients = form.value.clients.filter(id => id !== clientId);
};

const fetchClients = async () => {
    try {
        const response = await api.get('/clients');
        clients.value = response.data;
    } catch (error) {
        console.error('Error fetching clients:', error);
    }
};

const fetchEmployee = async () => {
    if (!isEditing.value) return;
    
    try {
        const response = await api.get(`/employees/${route.params.id}`);
        form.value = {
            name: response.data.name,
            email: response.data.email,
            role: response.data.roles[0]?.name || 'employee',
            clients: response.data.clients?.map(c => c.id) || [],
            phone: response.data.phone,
            whatsapp_number: response.data.whatsapp_number,
            address: response.data.address,
            doj: response.data.doj ? new Date(response.data.doj) : null,
            dob: response.data.dob ? new Date(response.data.dob) : null,
            gender: response.data.gender
        };
    } catch (error) {
        console.error('Error fetching employee:', error);
        router.push('/employees');
    } finally {
        fetchingData.value = false;
    }
};



const formatDateForApi = (date) => {
    if (!date) return null;
    const d = new Date(date);
    const month = '' + (d.getMonth() + 1);
    const day = '' + d.getDate();
    const year = d.getFullYear();

    return [year, month.padStart(2, '0'), day.padStart(2, '0')].join('-');
};

const handleSubmit = async () => {
    loading.value = true;
    try {
        if (isEditing.value) {
            await api.put(`/employees/${route.params.id}`, {
                name: form.value.name,
                email: form.value.email,
                role: form.value.role,
                clients: form.value.clients,
                phone: form.value.phone,
                whatsapp_number: form.value.whatsapp_number,
                address: form.value.address,
                doj: form.value.doj ? formatDateForApi(form.value.doj) : null,
                dob: form.value.dob ? formatDateForApi(form.value.dob) : null,
                gender: form.value.gender
            });
        } else {
            await api.post('/employees', {
                ...form.value,
                doj: form.value.doj ? formatDateForApi(form.value.doj) : null,
                dob: form.value.dob ? formatDateForApi(form.value.dob) : null
            });
        }
        router.push('/employees');
    } catch (error) {
        console.error('Error saving employee:', error);
    } finally {
        loading.value = false;
    }
};

onMounted(async () => {
    await fetchClients();
    await fetchEmployee();
});
</script>
