<template>
  <div id="app" :class="themeClass">
    <!-- Loading Global -->
    <LoadingOverlay v-if="isLoading" />
    
    <!-- Toast Container -->
    <ToastContainer />
    
    <!-- Layout Principal -->
    <div class="app-layout">
      <!-- Header -->
      <AppHeader v-if="showHeader" />
      
      <!-- Sidebar -->
      <AppSidebar v-if="showSidebar" />
      
      <!-- Main Content -->
      <main class="main-content" :class="mainContentClass">
        <router-view v-slot="{ Component, route }">
          <transition :name="transitionName" mode="out-in">
            <component :is="Component" :key="route.path" />
          </transition>
        </router-view>
      </main>
      
      <!-- Footer -->
      <AppFooter v-if="showFooter" />
    </div>
    
    <!-- PWA Update Available -->
    <PwaUpdatePrompt />
  </div>
</template>

<script setup lang="ts">
import { computed, onMounted, watch } from 'vue'
import { useStore } from 'vuex'
import { useRoute } from 'vue-router'
import { useTheme } from '@/composables/useTheme'
import { useAuth } from '@/composables/useAuth'
import { useSocket } from '@/composables/useSocket'

// Components
import AppHeader from '@/components/layout/AppHeader.vue'
import AppSidebar from '@/components/layout/AppSidebar.vue'
import AppFooter from '@/components/layout/AppFooter.vue'
import LoadingOverlay from '@/components/common/LoadingOverlay.vue'
import ToastContainer from '@/components/common/ToastContainer.vue'
import PwaUpdatePrompt from '@/components/common/PwaUpdatePrompt.vue'

// Composables
const store = useStore()
const route = useRoute()
const { themeClass } = useTheme()
const { initializeAuth } = useAuth()
const { initializeSocket } = useSocket()

// Computed
const isLoading = computed(() => store.getters['app/isLoading'])
const showHeader = computed(() => !route.meta?.hideHeader)
const showSidebar = computed(() => !route.meta?.hideSidebar && store.getters['auth/isAuthenticated'])
const showFooter = computed(() => !route.meta?.hideFooter)

const mainContentClass = computed(() => ({
  'with-sidebar': showSidebar.value,
  'without-sidebar': !showSidebar.value,
  'full-height': route.meta?.fullHeight
}))

const transitionName = computed(() => {
  if (route.meta?.transition) {
    return route.meta.transition
  }
  return 'fade'
})

// Lifecycle
onMounted(async () => {
  // Inicializar autenticação
  await initializeAuth()
  
  // Inicializar WebSocket se autenticado
  if (store.getters['auth/isAuthenticated']) {
    initializeSocket()
  }
  
  // Carregar preferências do usuário
  if (store.getters['auth/user']) {
    await store.dispatch('user/loadPreferences')
  }
})

// Watchers
watch(
  () => store.getters['auth/isAuthenticated'],
  (isAuthenticated) => {
    if (isAuthenticated) {
      initializeSocket()
    }
  }
)
</script>

<style lang="scss">
@import '@/styles/variables';
@import '@/styles/mixins';

#app {
  font-family: $font-family-base;
  -webkit-font-smoothing: antialiased;
  -moz-osx-font-smoothing: grayscale;
  min-height: 100vh;
  background-color: var(--bg-color);
  color: var(--text-color);
  transition: background-color 0.3s ease, color 0.3s ease;
}

.app-layout {
  display: flex;
  flex-direction: column;
  min-height: 100vh;
  
  @include media-breakpoint-up(md) {
    flex-direction: row;
    flex-wrap: wrap;
  }
}

.main-content {
  flex: 1;
  padding: $spacer-3;
  transition: margin-left 0.3s ease;
  
  &.with-sidebar {
    @include media-breakpoint-up(md) {
      margin-left: $sidebar-width;
    }
  }
  
  &.without-sidebar {
    margin-left: 0;
  }
  
  &.full-height {
    padding: 0;
    height: 100vh;
  }
  
  @include media-breakpoint-down(md) {
    margin-left: 0;
    padding: $spacer-2;
  }
}

// Transições
.fade-enter-active,
.fade-leave-active {
  transition: opacity 0.3s ease;
}

.fade-enter-from,
.fade-leave-to {
  opacity: 0;
}

.slide-left-enter-active,
.slide-left-leave-active {
  transition: transform 0.3s ease;
}

.slide-left-enter-from {
  transform: translateX(100%);
}

.slide-left-leave-to {
  transform: translateX(-100%);
}

.slide-right-enter-active,
.slide-right-leave-active {
  transition: transform 0.3s ease;
}

.slide-right-enter-from {
  transform: translateX(-100%);
}

.slide-right-leave-to {
  transform: translateX(100%);
}

.scale-enter-active,
.scale-leave-active {
  transition: transform 0.3s ease;
}

.scale-enter-from,
.scale-leave-to {
  transform: scale(0.9);
}
</style>