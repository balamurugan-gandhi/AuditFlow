import { defineConfig } from 'vite'
import vue from '@vitejs/plugin-vue'

// https://vitejs.dev/config/
export default defineConfig({
    plugins: [vue()],
    server: {
        host: true,
        port: 5173,
    },
    test: {
        globals: true,
        environment: 'jsdom',
    }
})
