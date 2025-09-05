import { Module } from 'vuex'
import { ThemeState, RootState } from '../types'

const state: ThemeState = {
  current: 'system',
  density: 'comfortable',
  sidebar_collapsed: false,
  custom_colors: {},
  animations_enabled: true
}

const mutations = {
  SET_THEME(state: ThemeState, theme: 'light' | 'dark' | 'system') {
    state.current = theme
    
    // Aplicar tema no documento
    const root = document.documentElement
    
    if (theme === 'system') {
      // Detectar preferência do sistema
      const prefersDark = window.matchMedia('(prefers-color-scheme: dark)').matches
      root.setAttribute('data-theme', prefersDark ? 'dark' : 'light')
    } else {
      root.setAttribute('data-theme', theme)
    }
    
    // Atualizar meta theme-color
    const metaThemeColor = document.querySelector('meta[name="theme-color"]')
    if (metaThemeColor) {
      const isDark = theme === 'dark' || (theme === 'system' && window.matchMedia('(prefers-color-scheme: dark)').matches)
      metaThemeColor.setAttribute('content', isDark ? '#1a1a1a' : '#ffffff')
    }
  },
  
  SET_DENSITY(state: ThemeState, density: 'comfortable' | 'compact' | 'spacious') {
    state.density = density
    
    // Aplicar densidade no documento
    const root = document.documentElement
    root.setAttribute('data-density', density)
  },
  
  SET_SIDEBAR_COLLAPSED(state: ThemeState, collapsed: boolean) {
    state.sidebar_collapsed = collapsed
    
    // Aplicar classe no body
    const body = document.body
    if (collapsed) {
      body.classList.add('sidebar-collapsed')
    } else {
      body.classList.remove('sidebar-collapsed')
    }
  },
  
  SET_CUSTOM_COLOR(state: ThemeState, { property, value }: { property: string; value: string }) {
    state.custom_colors[property] = value
    
    // Aplicar cor customizada
    const root = document.documentElement
    root.style.setProperty(`--${property}`, value)
  },
  
  SET_CUSTOM_COLORS(state: ThemeState, colors: Record<string, string>) {
    state.custom_colors = { ...colors }
    
    // Aplicar todas as cores customizadas
    const root = document.documentElement
    Object.entries(colors).forEach(([property, value]) => {
      root.style.setProperty(`--${property}`, value)
    })
  },
  
  RESET_CUSTOM_COLORS(state: ThemeState) {
    const root = document.documentElement
    
    // Remover cores customizadas
    Object.keys(state.custom_colors).forEach(property => {
      root.style.removeProperty(`--${property}`)
    })
    
    state.custom_colors = {}
  },
  
  SET_ANIMATIONS_ENABLED(state: ThemeState, enabled: boolean) {
    state.animations_enabled = enabled
    
    // Aplicar classe no body
    const body = document.body
    if (enabled) {
      body.classList.remove('no-animations')
    } else {
      body.classList.add('no-animations')
    }
  },
  
  RESTORE_STATE(state: ThemeState, savedState: Partial<ThemeState>) {
    Object.assign(state, savedState)
  }
}

const actions = {
  async setTheme({ commit, dispatch }: any, theme: 'light' | 'dark' | 'system') {
    commit('SET_THEME', theme)
    
    // Salvar preferência no backend se usuário estiver autenticado
    if (this.getters['auth/isAuthenticated']) {
      try {
        await dispatch('user/updatePreference', {
          key: 'theme',
          value: theme
        }, { root: true })
      } catch (error) {
        console.error('Erro ao salvar preferência de tema:', error)
      }
    }
  },
  
  async setDensity({ commit, dispatch }: any, density: 'comfortable' | 'compact' | 'spacious') {
    commit('SET_DENSITY', density)
    
    // Salvar preferência no backend se usuário estiver autenticado
    if (this.getters['auth/isAuthenticated']) {
      try {
        await dispatch('user/updatePreference', {
          key: 'density',
          value: density
        }, { root: true })
      } catch (error) {
        console.error('Erro ao salvar preferência de densidade:', error)
      }
    }
  },
  
  async toggleSidebar({ commit, state, dispatch }: any) {
    const collapsed = !state.sidebar_collapsed
    commit('SET_SIDEBAR_COLLAPSED', collapsed)
    
    // Salvar preferência no backend se usuário estiver autenticado
    if (this.getters['auth/isAuthenticated']) {
      try {
        await dispatch('user/updatePreference', {
          key: 'sidebar_collapsed',
          value: collapsed
        }, { root: true })
      } catch (error) {
        console.error('Erro ao salvar preferência de sidebar:', error)
      }
    }
  },
  
  setSidebarCollapsed({ commit }: any, collapsed: boolean) {
    commit('SET_SIDEBAR_COLLAPSED', collapsed)
  },
  
  setCustomColor({ commit }: any, { property, value }: { property: string; value: string }) {
    commit('SET_CUSTOM_COLOR', { property, value })
  },
  
  setCustomColors({ commit }: any, colors: Record<string, string>) {
    commit('SET_CUSTOM_COLORS', colors)
  },
  
  resetCustomColors({ commit }: any) {
    commit('RESET_CUSTOM_COLORS')
  },
  
  setAnimationsEnabled({ commit }: any, enabled: boolean) {
    commit('SET_ANIMATIONS_ENABLED', enabled)
  },
  
  initializeTheme({ commit, state }: any) {
    // Aplicar tema inicial
    commit('SET_THEME', state.current)
    commit('SET_DENSITY', state.density)
    commit('SET_SIDEBAR_COLLAPSED', state.sidebar_collapsed)
    commit('SET_ANIMATIONS_ENABLED', state.animations_enabled)
    
    // Aplicar cores customizadas
    if (Object.keys(state.custom_colors).length > 0) {
      commit('SET_CUSTOM_COLORS', state.custom_colors)
    }
    
    // Listener para mudanças na preferência do sistema
    const mediaQuery = window.matchMedia('(prefers-color-scheme: dark)')
    mediaQuery.addEventListener('change', (e) => {
      if (state.current === 'system') {
        commit('SET_THEME', 'system')
      }
    })
  },
  
  applyUserPreferences({ commit }: any, preferences: any) {
    if (preferences.theme) {
      commit('SET_THEME', preferences.theme)
    }
    if (preferences.density) {
      commit('SET_DENSITY', preferences.density)
    }
    if (typeof preferences.sidebar_collapsed === 'boolean') {
      commit('SET_SIDEBAR_COLLAPSED', preferences.sidebar_collapsed)
    }
  }
}

const getters = {
  currentTheme: (state: ThemeState) => {
    if (state.current === 'system') {
      return window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light'
    }
    return state.current
  },
  
  isDarkMode: (state: ThemeState, getters: any) => {
    return getters.currentTheme === 'dark'
  },
  
  isLightMode: (state: ThemeState, getters: any) => {
    return getters.currentTheme === 'light'
  },
  
  isSystemTheme: (state: ThemeState) => {
    return state.current === 'system'
  },
  
  density: (state: ThemeState) => state.density,
  
  isCompactDensity: (state: ThemeState) => state.density === 'compact',
  
  isComfortableDensity: (state: ThemeState) => state.density === 'comfortable',
  
  isSpaciousDensity: (state: ThemeState) => state.density === 'spacious',
  
  isSidebarCollapsed: (state: ThemeState) => state.sidebar_collapsed,
  
  customColors: (state: ThemeState) => state.custom_colors,
  
  hasCustomColors: (state: ThemeState) => Object.keys(state.custom_colors).length > 0,
  
  animationsEnabled: (state: ThemeState) => state.animations_enabled,
  
  themeConfig: (state: ThemeState, getters: any) => ({
    theme: getters.currentTheme,
    density: state.density,
    sidebarCollapsed: state.sidebar_collapsed,
    customColors: state.custom_colors,
    animationsEnabled: state.animations_enabled
  })
}

const theme: Module<ThemeState, RootState> = {
  namespaced: true,
  state,
  mutations,
  actions,
  getters
}

export default theme