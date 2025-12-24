<template>
    <aside
    :class="[
        'h-full transition-all duration-300 bg-slate-900 text-white',
        collapsed ? 'w-20' : 'w-64'
    ]"
    >
        <!-- Logo Section -->
        <div class="logo-section">
            <div class="logo-icon">
                <i class="pi pi-chart-line"></i>
            </div>
            <span class="logo-text" v-show="!collapsed">AuditFlow</span>
        </div>

        <!-- Navigation Menu -->
        <nav class="nav-menu">
            <ul>
                <li v-for="item in menuItems" :key="item.label">
                    <router-link :to="item.to" class="nav-item" :class="{ 'active': isRouteActive(item.to) }" :title="collapsed ? item.label : ''">
                        <i :class="item.icon"></i>
                        <span v-show="!collapsed">{{ item.label }}</span>
                    </router-link>
                </li>
            </ul>
        </nav>

        <!-- User Profile Section -->
        <div class="user-profile">
            <div class="user-avatar">
                {{ userInitials }}
            </div>
            <div class="user-info" v-show="!collapsed">
                <p class="user-name">{{ authStore.user?.name }}</p>
                <p class="user-email">{{ authStore.user?.email }}</p>
            </div>
        </div>
    </aside>
</template>

<script setup>
import { computed } from 'vue';
import { useRoute } from 'vue-router';
import { useAuthStore } from '../../stores/auth';

const props = defineProps({
    collapsed: {
        type: Boolean,
        default: false
    }
});

const authStore = useAuthStore();
const route = useRoute();

const userInitials = computed(() => {
    const name = authStore.user?.name || '';
    return name.split(' ').map(n => n[0]).join('').toUpperCase().substring(0, 2);
});

const isRouteActive = (path) => {
    if (path === '/') {
        return route.path === '/';
    }
    return route.path.startsWith(path);
};

const menuItems = computed(() => {
    const items = [
        { label: 'Dashboard', icon: 'pi pi-home', to: '/' },
    ];

    const isAdminOrManager = authStore.user?.roles?.some(role => ['admin', 'manager'].includes(role.name));
    const isAdmin = authStore.user?.roles?.some(role => role.name === 'admin');

    if (isAdminOrManager) {
        items.push({ label: 'Clients', icon: 'pi pi-users', to: '/clients' });
        items.push({ label: 'Employees', icon: 'pi pi-id-card', to: '/employees' });
        items.push({ label: 'Billing', icon: 'pi pi-wallet', to: '/billing' });
    }

    items.push(
        { label: 'Files', icon: 'pi pi-folder', to: '/files' },
        { label: 'Work Logs', icon: 'pi pi-clock', to: '/work-logs' },
    );

    if (isAdminOrManager) {
        items.push({ label: 'Settings', icon: 'pi pi-cog', to: '/settings' });
    }

    return items;
});
</script>

<style scoped>
/* .sidebar {
    width: 260px;
    background: linear-gradient(180deg, #1e293b 0%, #0f172a 100%);
    display: flex;
    flex-direction: column;
    height: 100vh;
    position: fixed;
    left: 0;
    top: 0;
    box-shadow: 2px 0 10px rgba(0, 0, 0, 0.1);
    transition: width 0.3s ease;
    z-index: 100;
}

.sidebar.collapsed {
    width: 80px;
} */

.logo-section {
    padding: 1.5rem;
    display: flex;
    align-items: center;
    gap: 0.75rem;
    border-bottom: 1px solid rgba(255, 255, 255, 0.1);
    height: 80px;
    overflow: hidden;
}

.sidebar.collapsed .logo-section {
    padding: 1.5rem 0;
    justify-content: center;
}

.logo-icon {
    width: 40px;
    height: 40px;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    border-radius: 10px;
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    font-size: 1.25rem;
    flex-shrink: 0;
}

.logo-text {
    font-size: 1.25rem;
    font-weight: 700;
    color: white;
    letter-spacing: -0.5px;
    white-space: nowrap;
}

.nav-menu {
    flex: 1;
    overflow-y: auto;
    overflow-x: hidden;
    padding: 1rem 0.75rem;
}

.nav-menu ul {
    list-style: none;
    padding: 0;
    margin: 0;
}

.nav-menu li {
    margin-bottom: 0.25rem;
}

.nav-item {
    display: flex;
    align-items: center;
    gap: 0.75rem;
    padding: 0.875rem 1rem;
    border-radius: 10px;
    color: #94a3b8;
    text-decoration: none;
    transition: all 0.2s;
    font-size: 0.9375rem;
    font-weight: 500;
    white-space: nowrap;
}

.sidebar.collapsed .nav-item {
    justify-content: center;
    padding: 0.875rem 0;
}

.nav-item:hover {
    background: rgba(255, 255, 255, 0.05);
    color: white;
}

.nav-item.active {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    box-shadow: 0 4px 12px rgba(102, 126, 234, 0.3);
}

.nav-item i {
    font-size: 1.125rem;
    width: 20px;
    text-align: center;
}

.user-profile {
    padding: 1rem;
    border-top: 1px solid rgba(255, 255, 255, 0.1);
    display: flex;
    align-items: center;
    gap: 0.75rem;
    overflow: hidden;
}

.sidebar.collapsed .user-profile {
    justify-content: center;
    padding: 1rem 0;
}

.user-avatar {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    font-weight: 700;
    font-size: 0.875rem;
    flex-shrink: 0;
}

.user-info {
    flex: 1;
    min-width: 0;
}

.user-name {
    color: white;
    font-size: 0.875rem;
    font-weight: 600;
    margin: 0;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}

.user-email {
    color: #64748b;
    font-size: 0.75rem;
    margin: 0;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}
</style>
