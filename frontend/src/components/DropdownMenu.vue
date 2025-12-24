<template>
  <div class="relative" ref="root">
    <Button icon="pi pi-ellipsis-v" text rounded @click="toggleMenu" />
    <div v-if="visible" class="dropdown-menu-popup" :class="menuDirection">
      <ul>
        <template v-for="(option, idx) in options">
          <li
            v-if="option.action !== 'delete'"
            :key="option.label"
            class="dropdown-menu-item"
            @click="select(option)"
          >
            <i :class="option.icon + ' dropdown-menu-icon'" />
            <span>{{ option.label }}</span>
          </li>
          <li v-else :key="option.label">
            <div class="dropdown-menu-divider"></div>
            <div class="dropdown-menu-item dropdown-menu-delete" @click="select(option)">
              <i :class="option.icon + ' dropdown-menu-icon'" />
              <span>{{ option.label }}</span>
            </div>
          </li>
        </template>
      </ul>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, onBeforeUnmount, nextTick } from 'vue';
import Button from 'primevue/button';

const props = defineProps({
  options: Array,
});
const emit = defineEmits(['select']);
const visible = ref(false);
const root = ref(null);
const id = Math.random().toString(36).substr(2, 9);
const menuDirection = ref('');

function closeAllDropdowns(e) {
  if (!root.value || (e && root.value.contains(e.target))) return;
  visible.value = false;
}

function getMenuDirection() {
  // Try to open upwards if not enough space below
  const rootEl = root.value;
  if (!rootEl) return '';
  const rect = rootEl.getBoundingClientRect();
  const spaceBelow = window.innerHeight - rect.bottom;
  const spaceAbove = rect.top;
  // If less than 250px below and more above, open upwards
  return (spaceBelow < 250 && spaceAbove > spaceBelow) ? 'upwards' : '';
}

function openDropdown() {
  window.dispatchEvent(new CustomEvent('dropdownmenu:open', { detail: id }));
  nextTick(() => {
    menuDirection.value = getMenuDirection();
    visible.value = true;
  });
}

function toggleMenu() {
  if (!visible.value) {
    openDropdown();
  } else {
    visible.value = false;
  }
}

function select(option) {
  visible.value = false;
  emit('select', option);
}

function handleGlobalOpen(e) {
  if (e.detail !== id) {
    visible.value = false;
  }
}

onMounted(() => {
  document.addEventListener('mousedown', closeAllDropdowns);
  window.addEventListener('dropdownmenu:open', handleGlobalOpen);
});
onBeforeUnmount(() => {
  document.removeEventListener('mousedown', closeAllDropdowns);
  window.removeEventListener('dropdownmenu:open', handleGlobalOpen);
});
</script>

<style scoped>
.dropdown-menu-popup {
  position: absolute;
  z-index: 10;
  right: 0;
  margin-top: 0.5rem;
  width: 12rem;
  background: var(--surface-0, #fff);
  border: 1px solid var(--surface-200, #e5e7eb);
  border-radius: 0.75rem;
  box-shadow: 0 4px 24px 0 rgba(0,0,0,0.10);
  padding: 0.5rem 0;
  animation: fadeIn 0.15s;
  /* Add max-height and scroll for overflow */
  max-height: 300px;
  overflow-y: auto;
}

/* Add a modifier for opening upwards */
.dropdown-menu-popup.upwards {
  bottom: 2.5rem;
  top: auto;
  margin-top: 0;
  margin-bottom: 0.5rem;
  animation: fadeInUp 0.15s;
}

.dropdown-menu-item {
  display: flex;
  align-items: center;
  gap: 0.75rem;
  padding: 0.6rem 1.25rem;
  font-size: 1rem;
  color: var(--surface-900, #222);
  cursor: pointer;
  border-radius: 0.5rem;
  transition: background 0.12s, color 0.12s;
}
.dropdown-menu-item:hover {
  background: var(--surface-100, #f3f4f6);
  color: var(--primary-600, #2563eb);
}
.dropdown-menu-icon {
  font-size: 1.1rem;
  min-width: 1.3em;
  text-align: center;
}
.dropdown-menu-divider {
  height: 1px;
  background: var(--surface-200, #e5e7eb);
  margin: 0.25rem 0 0.25rem 0;
}
.dropdown-menu-delete {
  color: #e53935;
  font-weight: 500;
}
.dropdown-menu-delete:hover {
  background: #ffeaea;
  color: #b71c1c;
}
@keyframes fadeIn {
  from { opacity: 0; transform: translateY(8px); }
  to { opacity: 1; transform: translateY(0); }
}
@keyframes fadeInUp {
  from { opacity: 0; transform: translateY(-8px); }
  to { opacity: 1; transform: translateY(0); }
}
</style>