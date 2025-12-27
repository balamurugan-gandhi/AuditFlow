<template>
  <div class="relative inline-block" ref="root">
    <Button icon="pi pi-ellipsis-v" text rounded @click="toggleMenu" />

    <Teleport to="body">
      <div
        v-if="visible"
        ref="menuRef"
        class="dropdown-menu-popup"
        :class="menuDirection"
        :style="menuStyle"
        @mousedown.stop
      >
        <ul>
          <template v-for="option in options" :key="option.label">
            <li
              v-if="option.action !== 'delete'"
              class="dropdown-menu-item"
              @click="select(option)"
            >
              <i :class="option.icon + ' dropdown-menu-icon'" />
              <span>{{ option.label }}</span>
            </li>

            <li v-else>
              <div class="dropdown-menu-divider"></div>
              <div
                class="dropdown-menu-item dropdown-menu-delete"
                @click="select(option)"
              >
                <i :class="option.icon + ' dropdown-menu-icon'" />
                <span>{{ option.label }}</span>
              </div>
            </li>
          </template>
        </ul>
      </div>
    </Teleport>
  </div>
</template>


<script setup>
import { ref, onMounted, onBeforeUnmount, nextTick } from 'vue';
import Button from 'primevue/button';
const menuStyle = ref({});
const menuRef = ref(null);

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

function handleScroll() {
  visible.value = false;
}

function openDropdown() {
  window.dispatchEvent(new CustomEvent('dropdownmenu:open', { detail: id }));

  visible.value = true;

  nextTick(() => {
    const rootRect = root.value.getBoundingClientRect();
    const menuEl = menuRef.value;

    if (!menuEl) return;

    const menuHeight = menuEl.offsetHeight;
    const spaceBelow = window.innerHeight - rootRect.bottom;
    const spaceAbove = rootRect.top;

    const openUpwards = spaceBelow < menuHeight && spaceAbove > spaceBelow;

    menuDirection.value = openUpwards ? 'upwards' : '';

    menuStyle.value = {
      position: 'absolute',
      top: openUpwards
        ? `${rootRect.top + window.scrollY - menuHeight - 8}px`
        : `${rootRect.bottom + window.scrollY + 8}px`,
      left: `${rootRect.right + window.scrollX - menuEl.offsetWidth}px`,
      zIndex: 9999
    };
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
  window.addEventListener('scroll', handleScroll, true);
});
onBeforeUnmount(() => {
  document.removeEventListener('mousedown', closeAllDropdowns);
  window.removeEventListener('dropdownmenu:open', handleGlobalOpen);
  window.removeEventListener('scroll', handleScroll, true);
});
</script>

<style scoped>
.dropdown-menu-popup {
  width: 12rem;
  background: #fff;
  border: 1px solid #e5e7eb;
  border-radius: 0.75rem;
  box-shadow: 0 4px 24px rgba(0, 0, 0, 0.1);
  padding: 0.5rem 0;
  max-height: 200px;
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