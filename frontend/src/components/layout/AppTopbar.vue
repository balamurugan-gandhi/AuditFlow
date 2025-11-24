<script setup>
import { computed, ref, onMounted } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import { useAuthStore } from '../../stores/auth';
import api from '../../api/axios';
import Button from 'primevue/button';
import BadgeDirective from 'primevue/badgedirective';
import OverlayPanel from 'primevue/overlaypanel';
import Avatar from 'primevue/avatar';

const vBadge = BadgeDirective;

const route = useRoute();
const router = useRouter();
const authStore = useAuthStore();

const notificationCount = ref(0);
const notifications = ref([]);
const op = ref();

const pageTitle = computed(() => {
    return route.meta.title || 'Dashboard';
});

const fetchNotifications = async () => {
    try {
        const [countRes, listRes] = await Promise.all([
            api.get('/notifications/unread-count'),
            api.get('/notifications')
        ]);
        notificationCount.value = countRes.data.count;
        notifications.value = listRes.data;
    } catch (error) {
        console.error('Error fetching notifications:', error);
    }
};

const toggleNotifications = (event) => {
    op.value.toggle(event);
};

const handleLogout = async () => {
    await authStore.logout();
    router.push('/login');
};

onMounted(() => {
    fetchNotifications();
    // Poll for notifications every 60s
    setInterval(fetchNotifications, 60000);
});
</script>

<template>
    <header class="topbar">
        <div class="topbar-left">
            <h2 class="page-title">{{ pageTitle }}</h2>
        </div>

        <div class="topbar-right">
            <!-- Notifications -->
            <button class="icon-button" @click="toggleNotifications" v-badge.danger="notificationCount > 0 ? notificationCount : null">
                <i class="pi pi-bell"></i>
            </button>

            <!-- User Menu -->
            <div class="user-menu">
                <Avatar :label="authStore.user?.name?.charAt(0)" shape="circle" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white;" />
            </div>

            <!-- Logout -->
            <button class="icon-button logout" @click="handleLogout" title="Logout">
                <i class="pi pi-power-off"></i>
            </button>
        </div>

        <OverlayPanel ref="op">
            <div class="notifications-panel">
                <div class="notifications-header">
                    <h3>Notifications</h3>
                    <span class="badge">{{ notificationCount }}</span>
                </div>
                <div v-if="notifications.length === 0" class="no-notifications">
                    <i class="pi pi-inbox"></i>
                    <p>No notifications</p>
                </div>
                <div v-else class="notifications-list">
                    <div v-for="notif in notifications" :key="notif.id" class="notification-item">
                        <div class="notification-icon">
                            <i class="pi pi-info-circle"></i>
                        </div>
                        <div class="notification-content">
                            <p class="notification-message">{{ notif.data.message }}</p>
                            <p class="notification-time">{{ new Date(notif.created_at).toLocaleString() }}</p>
                        </div>
                    </div>
                </div>
            </div>
        </OverlayPanel>
    </header>
</template>

<style scoped>
.topbar {
    height: 70px;
    background: white;
    border-bottom: 1px solid #e2e8f0;
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 0 2rem;
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05);
    position: sticky;
    top: 0;
    z-index: 100;
}

.topbar-left {
    display: flex;
    align-items: center;
    gap: 1rem;
}

.page-title {
    font-size: 1.5rem;
    font-weight: 700;
    color: #1e293b;
    margin: 0;
}

.topbar-right {
    display: flex;
    align-items: center;
    gap: 1rem;
}

.icon-button {
    width: 40px;
    height: 40px;
    border-radius: 10px;
    border: none;
    background: #f1f5f9;
    color: #64748b;
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    transition: all 0.2s;
    font-size: 1.125rem;
    position: relative;
}

.icon-button:hover {
    background: #e2e8f0;
    color: #334155;
}

.icon-button.logout:hover {
    background: #fee2e2;
    color: #dc2626;
}

.user-menu {
    cursor: pointer;
}

.notifications-panel {
    width: 360px;
    max-height: 480px;
    overflow: hidden;
    display: flex;
    flex-direction: column;
    background: white;
    border-radius: 12px;
    box-shadow: 0 4px 20px rgba(0, 0, 0, 0.15);
}

.notifications-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 1rem;
    border-bottom: 1px solid #e2e8f0;
}

.notifications-header h3 {
    font-size: 1rem;
    font-weight: 600;
    color: #1e293b;
    margin: 0;
}

.badge {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    padding: 0.25rem 0.5rem;
    border-radius: 12px;
    font-size: 0.75rem;
    font-weight: 600;
}

.no-notifications {
    padding: 3rem 1rem;
    text-align: center;
    color: #94a3b8;
}

.no-notifications i {
    font-size: 3rem;
    margin-bottom: 0.5rem;
    opacity: 0.5;
}

.no-notifications p {
    margin: 0;
    font-size: 0.875rem;
}

.notifications-list {
    overflow-y: auto;
    max-height: 400px;
}

.notification-item {
    display: flex;
    gap: 0.75rem;
    padding: 1rem;
    border-bottom: 1px solid #f1f5f9;
    transition: background 0.2s;
}

.notification-item:hover {
    background: #f8fafc;
}

.notification-icon {
    width: 36px;
    height: 36px;
    border-radius: 8px;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    flex-shrink: 0;
}

.notification-content {
    flex: 1;
    min-width: 0;
}

.notification-message {
    font-size: 0.875rem;
    color: #334155;
    margin: 0 0 0.25rem 0;
    font-weight: 500;
}

.notification-time {
    font-size: 0.75rem;
    color: #94a3b8;
    margin: 0;
}
</style>
