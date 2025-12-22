import { createRouter, createWebHistory } from 'vue-router';
import Login from '../views/auth/Login.vue';
import MainLayout from '../components/layout/MainLayout.vue';

const routes = [
    {
        path: '/login',
        name: 'Login',
        component: Login,
        meta: { guest: true }
    },
    {
        path: '/',
        component: MainLayout,
        meta: { requiresAuth: true },
        children: [
            {
                path: '',
                name: 'Dashboard',
                component: () => import('../views/Dashboard.vue'),
                meta: { title: 'Dashboard' }
            },
            {
                path: 'clients',
                name: 'ClientList',
                component: () => import('../views/clients/ClientList.vue'),
                meta: { title: 'Clients', requiresAdminOrManager: true }
            },
            {
                path: 'clients/create',
                name: 'ClientCreate',
                component: () => import('../views/clients/ClientForm.vue'),
                meta: { title: 'Add Client', requiresAdminOrManager: true }
            },
            {
                path: 'clients/:id/edit',
                name: 'ClientEdit',
                component: () => import('../views/clients/ClientForm.vue'),
                meta: { title: 'Edit Client', requiresAdminOrManager: true }
            },
            {
                path: 'clients/:id',
                name: 'ClientDetails',
                component: () => import('../views/clients/ClientDetails.vue'),
                meta: { title: 'Client Details', requiresAdminOrManager: true }
            },
            {
                path: 'employees',
                name: 'EmployeeList',
                component: () => import('../views/employees/EmployeeList.vue'),
                meta: { title: 'Employees', requiresAdminOrManager: true }
            },
            {
                path: 'employees/create',
                name: 'EmployeeCreate',
                component: () => import('../views/employees/EmployeeForm.vue'),
                meta: { title: 'Add Employee', requiresAdminOrManager: true }
            },
            {
                path: 'employees/:id/edit',
                name: 'EmployeeEdit',
                component: () => import('../views/employees/EmployeeForm.vue'),
                meta: { title: 'Edit Employee', requiresAdminOrManager: true }
            },
            {
                path: 'employees/:id',
                name: 'EmployeeDetails',
                component: () => import('../views/employees/EmployeeDetails.vue'),
                meta: { title: 'Employee Details', requiresAdminOrManager: true }
            },
            {
                path: 'files',
                name: 'FileList',
                component: () => import('../views/files/FileList.vue'),
                meta: { title: 'Files' }
            },
            {
                path: 'files/create',
                name: 'FileCreate',
                component: () => import('../views/files/FileForm.vue'),
                meta: { title: 'Add File' }
            },
            {
                path: 'files/:id/edit',
                name: 'FileEdit',
                component: () => import('../views/files/FileForm.vue'),
                meta: { title: 'Edit File', requiresAdminOrManager: true }
            },
            {
                path: 'files/:id',
                name: 'FileDetails',
                component: () => import('../views/files/FileDetails.vue'),
                meta: { title: 'File Details' }
            },
            {
                path: 'billing',
                name: 'BillingList',
                component: () => import('../views/billing/BillingList.vue'),
                meta: { title: 'Billing' }
            },
            {
                path: 'billing/create',
                name: 'InvoiceCreate',
                component: () => import('../views/billing/InvoiceForm.vue'),
                meta: { title: 'Create Invoice' }
            },
            {
                path: 'billing/:id',
                name: 'InvoiceDetails',
                component: () => import('../views/billing/InvoiceDetails.vue'),
                meta: { title: 'Invoice Details' }
            },
            {
                path: 'work-logs',
                name: 'WorkLogList',
                component: () => import('../views/worklogs/WorkLogList.vue'),
                meta: { title: 'Work Logs' }
            },
            {
                path: 'settings',
                name: 'Settings',
                component: () => import('../views/settings/Settings.vue'),
                meta: { title: 'Settings', requiresAdminOrManager: true }
            },
        ]
    }
];

const router = createRouter({
    history: createWebHistory(),
    routes
});

router.beforeEach(async (to, from, next) => {
    const token = localStorage.getItem('token');

    if (to.meta.requiresAuth && !token) {
        next('/login');
    } else if (to.meta.guest && token) {
        next('/');
    } else if (to.meta.requiresAdminOrManager) {
        // Check if user has admin or manager role
        const userStr = localStorage.getItem('user');
        if (!userStr) {
            next('/login');
            return;
        }

        try {
            const user = JSON.parse(userStr);
            const isAdminOrManager = user.roles?.some(role => ['admin', 'manager'].includes(role.name));
            if (!isAdminOrManager) {
                next('/');
                return;
            }
            next();
        } catch (e) {
            next('/login');
        }
    } else if (to.meta.requiresAdmin) {
        // Check if user has admin role
        const userStr = localStorage.getItem('user');
        if (!userStr) {
            next('/login');
            return;
        }

        try {
            const user = JSON.parse(userStr);
            // Check if user has admin role (roles is an array of objects with 'name' property)
            const isAdmin = user.roles?.some(role => role.name === 'admin');
            if (!isAdmin) {
                // Redirect non-admins to dashboard
                next('/');
                return;
            }
            next();
        } catch (e) {
            next('/login');
        }
    } else {
        next();
    }
});

export default router;
