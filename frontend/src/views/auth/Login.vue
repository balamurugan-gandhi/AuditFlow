<template>
    <div class="login-container">
        <div class="login-card">
            <!-- Logo Section -->
            <div class="logo-section">
                <div class="logo-icon">
                    <i class="pi pi-chart-line"></i>
                </div>
                <h1 class="logo-title">AuditFlow</h1>
                <p class="logo-subtitle">Sign in to your account</p>
            </div>

            <!-- Login Form -->
            <form @submit.prevent="handleLogin" class="login-form">
                <div class="form-group">
                    <label for="email">Email Address</label>
                    <InputText 
                        id="email"
                        v-model="email" 
                        type="email" 
                        placeholder="Enter your email address"
                        :class="{ 'p-invalid': errors.email }"
                    />
                    <small v-if="errors.email" class="error-message">{{ errors.email }}</small>
                </div>

                <div class="form-group">
                    <label for="password">Password</label>
                    <Password 
                        id="password"
                        v-model="password" 
                        placeholder="Enter your password"
                        :feedback="false"
                        toggleMask
                        :pt="{
                            input: { class: 'w-full' }
                        }"
                    />
                    <small v-if="errors.password" class="error-message">{{ errors.password }}</small>
                </div>

                <div class="form-actions">
                    <Button 
                        type="submit" 
                        label="Sign In" 
                        :loading="loading"
                        class="sign-in-button"
                    />
                </div>

                <div v-if="errors.general" class="error-alert">
                    <i class="pi pi-exclamation-circle"></i>
                    <span>{{ errors.general }}</span>
                </div>
            </form>

            <!-- Footer -->
            <div class="login-footer">
                <p>© 2024 AuditFlow. All rights reserved.</p>
            </div>
        </div>

        <!-- Background Decoration -->
        <div class="bg-decoration decoration-1"></div>
        <div class="bg-decoration decoration-2"></div>
        <div class="bg-decoration decoration-3"></div>
    </div>
</template>

<script setup>
import { ref } from 'vue';
import { useRouter } from 'vue-router';
import { useAuthStore } from '../../stores/auth';
import InputText from 'primevue/inputtext';
import Password from 'primevue/password';
import Button from 'primevue/button';

const router = useRouter();
const authStore = useAuthStore();

const email = ref('');
const password = ref('');
const loading = ref(false);
const errors = ref({});

const handleLogin = async () => {
    errors.value = {};
    
    // Validation
    if (!email.value) {
        errors.value.email = 'Email is required';
        return;
    }
    if (!password.value) {
        errors.value.password = 'Password is required';
        return;
    }

    loading.value = true;
    try {
        const success = await authStore.login({
            email: email.value,
            password: password.value
        });
        
        if (success) {
            router.push('/');
        } else {
            errors.value.general = authStore.error || 'Invalid credentials. Please try again.';
        }
    } catch (error) {
        errors.value.general = error.response?.data?.message || 'Invalid credentials. Please try again.';
    } finally {
        loading.value = false;
    }
};
</script>

<style scoped>
.login-container {
    min-height: 100vh;
    display: flex;
    align-items: center;
    justify-content: center;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    padding: 2rem;
    position: relative;
    overflow: hidden;
}

.bg-decoration {
    position: absolute;
    border-radius: 50%;
    opacity: 0.1;
    background: white;
}

.decoration-1 {
    width: 300px;
    height: 300px;
    top: -100px;
    left: -100px;
}

.decoration-2 {
    width: 400px;
    height: 400px;
    bottom: -150px;
    right: -150px;
}

.decoration-3 {
    width: 200px;
    height: 200px;
    top: 50%;
    right: 10%;
}

.login-card {
    background: white;
    border-radius: 20px;
    padding: 3rem;
    width: 100%;
    max-width: 440px;
    box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
    position: relative;
    z-index: 1;
}

.logo-section {
    text-align: center;
    margin-bottom: 2.5rem;
}

.logo-icon {
    width: 70px;
    height: 70px;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    border-radius: 16px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    color: white;
    font-size: 2rem;
    margin-bottom: 1rem;
    box-shadow: 0 8px 16px rgba(102, 126, 234, 0.3);
}

.logo-title {
    font-size: 2rem;
    font-weight: 700;
    color: #1e293b;
    margin: 0 0 0.5rem 0;
}

.logo-subtitle {
    color: #64748b;
    font-size: 1rem;
    margin: 0;
}

.login-form {
    margin-bottom: 1.5rem;
}

.form-group {
    margin-bottom: 1.5rem;
}

.form-group label {
    display: block;
    font-size: 0.875rem;
    font-weight: 600;
    color: #334155;
    margin-bottom: 0.5rem;
}

.form-group :deep(.p-inputtext),
.form-group :deep(.p-password) {
    width: 100%;
}

.form-group :deep(.p-password input) {
    width: 100%;
    padding-right: 3rem;
}

.form-group :deep(.p-inputtext) {
    padding: 0.875rem 1rem;
    border: 2px solid #e2e8f0;
    border-radius: 10px;
    font-size: 0.9375rem;
    transition: all 0.2s;
}

.form-group :deep(.p-inputtext::placeholder) {
    color: #94a3b8 !important;
    opacity: 1 !important;
}

.form-group :deep(.p-inputtext::-webkit-input-placeholder) {
    color: #94a3b8 !important;
    opacity: 1 !important;
}

.form-group :deep(.p-inputtext::-moz-placeholder) {
    color: #94a3b8 !important;
    opacity: 1 !important;
}

.form-group :deep(.p-inputtext:focus) {
    border-color: #667eea;
    box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
}

.form-group :deep(.p-password-input) {
    padding: 0.875rem 2.75rem 0.875rem 1rem !important;
    border: 2px solid #e2e8f0 !important;
    border-radius: 10px !important;
    font-size: 0.9375rem !important;
}

.form-group :deep(.p-password-input::placeholder) {
    color: #94a3b8 !important;
    opacity: 1 !important;
}

.form-group :deep(.p-password-input::-webkit-input-placeholder) {
    color: #94a3b8 !important;
    opacity: 1 !important;
}

.form-group :deep(.p-password-input::-moz-placeholder) {
    color: #94a3b8 !important;
    opacity: 1 !important;
}

.form-group :deep(.p-password-input:focus) {
    border-color: #667eea !important;
    box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1) !important;
}

.form-group :deep(.p-password .p-icon) {
    position: absolute !important;
    right: 1rem !important;
    top: 50% !important;
    transform: translateY(-50%) !important;
    color: #64748b !important;
    cursor: pointer !important;
    z-index: 10 !important;
}

.error-message {
    color: #dc2626;
    font-size: 0.8125rem;
    margin-top: 0.25rem;
    display: block;
}

.form-actions {
    margin-top: 2rem;
}

.sign-in-button {
    width: 100%;
    padding: 0.875rem;
    font-size: 1rem;
    font-weight: 600;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    border: none;
    border-radius: 10px;
    box-shadow: 0 4px 12px rgba(102, 126, 234, 0.4);
    transition: all 0.2s;
}

.sign-in-button:hover {
    transform: translateY(-2px);
    box-shadow: 0 6px 16px rgba(102, 126, 234, 0.5);
}

.sign-in-button:active {
    transform: translateY(0);
}

.error-alert {
    margin-top: 1rem;
    padding: 0.875rem 1rem;
    background: #fee2e2;
    border: 1px solid #fecaca;
    border-radius: 10px;
    color: #dc2626;
    font-size: 0.875rem;
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.error-alert i {
    font-size: 1.125rem;
}

.login-footer {
    text-align: center;
    padding-top: 1.5rem;
    border-top: 1px solid #e2e8f0;
}

.login-footer p {
    color: #94a3b8;
    font-size: 0.8125rem;
    margin: 0;
}

@media (max-width: 640px) {
    .login-card {
        padding: 2rem 1.5rem;
    }

    .logo-title {
        font-size: 1.75rem;
    }
}
</style>
