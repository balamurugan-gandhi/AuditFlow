<template>
  <div class="notification-view p-4">
    <h2 class="text-xl font-bold mb-4">File Due Notifications</h2>
    <div v-if="files.length === 0" class="text-gray-500">No files to show.</div>
    <ul>
      <li v-for="file in files" :key="file.id" :class="getClass(file)">
        <span>{{ file.name }}</span>
        <span v-if="file.daysLeft < 0" class="ml-2">(Past Due)</span>
        <span v-else-if="file.daysLeft <= 5" class="ml-2">({{ file.daysLeft }} days left)</span>
      </li>
    </ul>
  </div>
</template>

<script>
export default {
  name: 'NotificationView',
  data() {
    return {
      files: [],
      loading: true,
      error: null,
    };
  },
    mounted() {
      this.fetchNotifications();
    },
    methods: {
      async fetchNotifications() {
        this.loading = true;
        try {
          const response = await fetch('/api/file-due-notifications');
          if (!response.ok) throw new Error('Failed to fetch notifications');
          const data = await response.json();
          this.files = [
            ...data.past_due,
            ...data.less_than_5_days
          ];
        } catch (err) {
          this.error = err.message;
        } finally {
          this.loading = false;
        }
      },
  methods: {
    getClass(file) {
      if (file.daysLeft < 0) {
        return 'text-red-600 font-semibold';
      } else if (file.daysLeft <= 5) {
        return 'text-orange-500 font-semibold';
      }
      return 'text-gray-700';
    },
  },
  // No need for computed filteredFiles, as API already filters
};
</script>

<style scoped>
.notification-view {
  max-width: 500px;
  margin: auto;
}
</style>
