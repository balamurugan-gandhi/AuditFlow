<template>
    <div class="main-layout flex h-screen overflow-hidden">
        <AppSidebar :collapsed="isSidebarCollapsed" class="shrink-0" />
        
        <div class="content-wrapper">
            <!-- Impersonation Banner -->
            <div v-if="authStore.isImpersonating" class="bg-orange-500 text-white px-4 py-2 flex justify-between items-center">
                <span class="font-medium">Viewing as: {{ authStore.user?.name }}</span>
                <Button label="Switch back to Admin" size="small" severity="secondary" @click="stopImpersonating" />
            </div>

            <AppTopbar />
            
            <main class="main-content">
                <router-view></router-view>
            </main>
        </div>
    </div>
</template>

<script setup>
import { ref, onMounted, onUnmounted } from 'vue';
import { useRouter } from 'vue-router';
import { useAuthStore } from '../../stores/auth';
import AppSidebar from './AppSidebar.vue';
import AppTopbar from './AppTopbar.vue';
import Button from 'primevue/button';

const router = useRouter();
const authStore = useAuthStore();
const isSidebarCollapsed = ref(false);

const checkScreenSize = () => {
    isSidebarCollapsed.value = window.innerWidth < 1024;
};

const stopImpersonating = () => {
    if (authStore.stopImpersonating()) {
        router.push('/employees');
    }
};

onMounted(() => {
    checkScreenSize();
    window.addEventListener('resize', checkScreenSize);
});

onUnmounted(() => {
    window.removeEventListener('resize', checkScreenSize);
});
</script>

<style scoped>
.main-layout {
    display: flex;
    min-height: 100vh;
    overflow: hidden;
}

.content-wrapper {
    flex: 1;
    display: flex;
    flex-direction: column;
    overflow: hidden;
}

.main-content {
    flex: 1;
    overflow-y: auto;
    background: #f8f9fa;
    padding: 2rem;
}
</style>
