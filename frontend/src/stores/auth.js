import { defineStore } from 'pinia';
import api from '../api/axios';

export const useAuthStore = defineStore('auth', {
    state: () => ({
        user: JSON.parse(localStorage.getItem('user')) || null,
        token: localStorage.getItem('token') || null,
        impersonating: !!localStorage.getItem('admin_token'),
        loading: false,
        error: null
    }),
    getters: {
        isAuthenticated: (state) => !!state.token,
        isAdmin: (state) => state.user?.roles?.some(role => role.name === 'admin'),
        isImpersonating: (state) => state.impersonating,
    },
    actions: {
        async login(credentials) {
            this.loading = true;
            this.error = null;
            try {
                const response = await api.post('/login', credentials);
                this.token = response.data.access_token;
                this.user = response.data.user;

                localStorage.setItem('token', this.token);
                localStorage.setItem('user', JSON.stringify(this.user));

                return true;
            } catch (error) {
                this.error = error.response?.data?.message || 'Login failed';
                return false;
            } finally {
                this.loading = false;
            }
        },
        async logout() {
            try {
                await api.post('/logout');
            } catch (error) {
                console.error('Logout error', error);
            } finally {
                this.token = null;
                this.user = null;
                this.impersonating = false;
                localStorage.removeItem('token');
                localStorage.removeItem('user');
                localStorage.removeItem('admin_token');
                localStorage.removeItem('admin_user');
            }
        },
        async impersonate(userId) {
            try {
                const response = await api.post(`/impersonate/${userId}`);

                // Save admin credentials
                localStorage.setItem('admin_token', this.token);
                localStorage.setItem('admin_user', JSON.stringify(this.user));

                // Set new credentials
                this.token = response.data.access_token;
                this.user = response.data.user;
                this.impersonating = true;
                localStorage.setItem('token', this.token);
                localStorage.setItem('user', JSON.stringify(this.user));

                return true;
            } catch (error) {
                console.error('Impersonation failed', error);
                return false;
            }
        },
        stopImpersonating() {
            const adminToken = localStorage.getItem('admin_token');
            const adminUser = localStorage.getItem('admin_user');

            if (adminToken && adminUser) {
                this.token = adminToken;
                this.user = JSON.parse(adminUser);
                this.impersonating = false;
                localStorage.setItem('token', this.token);
                localStorage.setItem('user', adminUser);

                localStorage.removeItem('admin_token');
                localStorage.removeItem('admin_user');
                return true;
            }
            return false;
        }
    }
});
