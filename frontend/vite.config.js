import { defineConfig } from 'vite'
import vue from '@vitejs/plugin-vue'

export default defineConfig({
  plugins: [vue()],

  server: {
    host: true,
    port: 5173,

    proxy: {
      '/api': {
        target: 'http://nginx:80',
        changeOrigin: true,
      },
    },

    watch: {
      usePolling: true,
    },
  },

  test: {
    globals: true,
    environment: 'jsdom',
  },
})
