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
                    
                    <!-- Client Assignment -->
                    <div class="flex flex-col gap-2" v-if="form.role === 'employee'">
                        <label for="clients" class="text-sm font-medium text-surface-700 dark:text-surface-200">Assign Clients</label>
                        <MultiSelect id="clients" v-model="form.clients" :options="clients" optionLabel="business_name" optionValue="id" placeholder="Select Clients" filter display="chip" class="w-full" />
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
import MultiSelect from 'primevue/multiselect';
import Skeleton from 'primevue/skeleton';

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

const form = ref({
    name: '',
    email: '',
    password: '',
    role: 'employee',
    clients: []
});

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
            clients: response.data.clients?.map(c => c.id) || []
        };
    } catch (error) {
        console.error('Error fetching employee:', error);
        router.push('/employees');
    } finally {
        fetchingData.value = false;
    }
};

const handleSubmit = async () => {
    loading.value = true;
    try {
        if (isEditing.value) {
            await api.put(`/employees/${route.params.id}`, {
                name: form.value.name,
                email: form.value.email,
                role: form.value.role,
                clients: form.value.clients
            });
        } else {
            await api.post('/employees', form.value);
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
