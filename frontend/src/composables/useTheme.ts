import { computed, watch, onMounted, onUnmounted } from 'vue'
import { useStore } from 'vuex'

export function useTheme() {
  const store = useStore()
  
  // Computed properties
  const currentTheme = computed(() => store.getters['theme/currentTheme'])
  const isDarkMode = computed(() => store.getters['theme/isDarkMode'])
  const isLightMode = computed(() => store.getters['theme/isLightMode'])
  const isSystemTheme = computed(() => store.getters['theme/isSystemTheme'])
  const density = computed(() => store.getters['theme/density'])
  const isCompactDensity = computed(() => store.getters['theme/isCompactDensity'])
  const isComfortableDensity = computed(() => store.getters['theme/isComfortableDensity'])
  const isSpaciousDensity = computed(() => store.getters['theme/isSpaciousDensity'])
  const isSidebarCollapsed = computed(() => store.getters['theme/isSidebarCollapsed'])
  const customColors = computed(() => store.getters['theme/customColors'])
  const hasCustomColors = computed(() => store.getters['theme/hasCustomColors'])
  const animationsEnabled = computed(() => store.getters['theme/animationsEnabled'])
  const themeConfig = computed(() => store.getters['theme/themeConfig'])
  
  // Classe CSS para o tema atual
  const themeClass = computed(() => {
    const classes = [`theme-${currentTheme.value}`]
    
    if (density.value) {
      classes.push(`density-${density.value}`)
    }
    
    if (isSidebarCollapsed.value) {
      classes.push('sidebar-collapsed')
    }
    
    if (!animationsEnabled.value) {
      classes.push('no-animations')
    }
    
    return classes.join(' ')
  })
  
  // CSS Variables para o tema atual
  const themeVariables = computed(() => {
    const variables: Record<string, string> = {}
    
    // Variáveis base do tema
    if (isDarkMode.value) {
      variables['--bg-color'] = '#1a1a1a'
      variables['--bg-color-secondary'] = '#2d2d2d'
      variables['--text-color'] = '#ffffff'
      variables['--text-color-secondary'] = '#b3b3b3'
      variables['--border-color'] = '#404040'
      variables['--shadow-color'] = 'rgba(0, 0, 0, 0.5)'
    } else {
      variables['--bg-color'] = '#ffffff'
      variables['--bg-color-secondary'] = '#f8f9fa'
      variables['--text-color'] = '#212529'
      variables['--text-color-secondary'] = '#6c757d'
      variables['--border-color'] = '#dee2e6'
      variables['--shadow-color'] = 'rgba(0, 0, 0, 0.1)'
    }
    
    // Variáveis de densidade
    switch (density.value) {
      case 'compact':
        variables['--spacing-unit'] = '4px'
        variables['--font-size-base'] = '14px'
        variables['--line-height-base'] = '1.4'
        variables['--border-radius'] = '4px'
        break
      case 'spacious':
        variables['--spacing-unit'] = '12px'
        variables['--font-size-base'] = '16px'
        variables['--line-height-base'] = '1.8'
        variables['--border-radius'] = '8px'
        break
      default: // comfortable
        variables['--spacing-unit'] = '8px'
        variables['--font-size-base'] = '15px'
        variables['--line-height-base'] = '1.6'
        variables['--border-radius'] = '6px'
    }
    
    // Cores customizadas
    Object.entries(customColors.value).forEach(([property, value]) => {
      variables[`--${property}`] = value
    })
    
    return variables
  })
  
  // Actions
  const setTheme = (theme: 'light' | 'dark' | 'system') => {
    return store.dispatch('theme/setTheme', theme)
  }
  
  const toggleTheme = () => {
    const newTheme = isDarkMode.value ? 'light' : 'dark'
    return setTheme(newTheme)
  }
  
  const setDensity = (density: 'comfortable' | 'compact' | 'spacious') => {
    return store.dispatch('theme/setDensity', density)
  }
  
  const toggleSidebar = () => {
    return store.dispatch('theme/toggleSidebar')
  }
  
  const setSidebarCollapsed = (collapsed: boolean) => {
    return store.dispatch('theme/setSidebarCollapsed', collapsed)
  }
  
  const setCustomColor = (property: string, value: string) => {
    return store.dispatch('theme/setCustomColor', { property, value })
  }
  
  const setCustomColors = (colors: Record<string, string>) => {
    return store.dispatch('theme/setCustomColors', colors)
  }
  
  const resetCustomColors = () => {
    return store.dispatch('theme/resetCustomColors')
  }
  
  const setAnimationsEnabled = (enabled: boolean) => {
    return store.dispatch('theme/setAnimationsEnabled', enabled)
  }
  
  const toggleAnimations = () => {
    return setAnimationsEnabled(!animationsEnabled.value)
  }
  
  // Aplicar variáveis CSS no documento
  const applyThemeVariables = () => {
    const root = document.documentElement
    Object.entries(themeVariables.value).forEach(([property, value]) => {
      root.style.setProperty(property, value)
    })
  }
  
  // Detectar preferência do sistema
  const detectSystemTheme = () => {
    return window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light'
  }
  
  // Listener para mudanças na preferência do sistema
  let mediaQueryListener: ((e: MediaQueryListEvent) => void) | null = null
  
  const setupSystemThemeListener = () => {
    const mediaQuery = window.matchMedia('(prefers-color-scheme: dark)')
    
    mediaQueryListener = (e: MediaQueryListEvent) => {
      if (isSystemTheme.value) {
        // Reaplica o tema system para atualizar as variáveis
        store.commit('theme/SET_THEME', 'system')
      }
    }
    
    mediaQuery.addEventListener('change', mediaQueryListener)
  }
  
  const removeSystemThemeListener = () => {
    if (mediaQueryListener) {
      const mediaQuery = window.matchMedia('(prefers-color-scheme: dark)')
      mediaQuery.removeEventListener('change', mediaQueryListener)
      mediaQueryListener = null
    }
  }
  
  // Watchers
  watch(themeVariables, applyThemeVariables, { immediate: true })
  
  // Lifecycle
  onMounted(() => {
    setupSystemThemeListener()
    applyThemeVariables()
  })
  
  onUnmounted(() => {
    removeSystemThemeListener()
  })
  
  // Utilitários
  const getContrastColor = (backgroundColor: string) => {
    // Converter hex para RGB
    const hex = backgroundColor.replace('#', '')
    const r = parseInt(hex.substr(0, 2), 16)
    const g = parseInt(hex.substr(2, 2), 16)
    const b = parseInt(hex.substr(4, 2), 16)
    
    // Calcular luminância
    const luminance = (0.299 * r + 0.587 * g + 0.114 * b) / 255
    
    // Retornar cor de contraste
    return luminance > 0.5 ? '#000000' : '#ffffff'
  }
  
  const hexToRgb = (hex: string) => {
    const result = /^#?([a-f\d]{2})([a-f\d]{2})([a-f\d]{2})$/i.exec(hex)
    return result ? {
      r: parseInt(result[1], 16),
      g: parseInt(result[2], 16),
      b: parseInt(result[3], 16)
    } : null
  }
  
  const rgbToHex = (r: number, g: number, b: number) => {
    return '#' + [r, g, b].map(x => {
      const hex = x.toString(16)
      return hex.length === 1 ? '0' + hex : hex
    }).join('')
  }
  
  const lightenColor = (color: string, amount: number) => {
    const rgb = hexToRgb(color)
    if (!rgb) return color
    
    const { r, g, b } = rgb
    const newR = Math.min(255, Math.floor(r + (255 - r) * amount))
    const newG = Math.min(255, Math.floor(g + (255 - g) * amount))
    const newB = Math.min(255, Math.floor(b + (255 - b) * amount))
    
    return rgbToHex(newR, newG, newB)
  }
  
  const darkenColor = (color: string, amount: number) => {
    const rgb = hexToRgb(color)
    if (!rgb) return color
    
    const { r, g, b } = rgb
    const newR = Math.max(0, Math.floor(r * (1 - amount)))
    const newG = Math.max(0, Math.floor(g * (1 - amount)))
    const newB = Math.max(0, Math.floor(b * (1 - amount)))
    
    return rgbToHex(newR, newG, newB)
  }
  
  return {
    // State
    currentTheme,
    isDarkMode,
    isLightMode,
    isSystemTheme,
    density,
    isCompactDensity,
    isComfortableDensity,
    isSpaciousDensity,
    isSidebarCollapsed,
    customColors,
    hasCustomColors,
    animationsEnabled,
    themeConfig,
    themeClass,
    themeVariables,
    
    // Actions
    setTheme,
    toggleTheme,
    setDensity,
    toggleSidebar,
    setSidebarCollapsed,
    setCustomColor,
    setCustomColors,
    resetCustomColors,
    setAnimationsEnabled,
    toggleAnimations,
    
    // Utilities
    detectSystemTheme,
    getContrastColor,
    hexToRgb,
    rgbToHex,
    lightenColor,
    darkenColor,
    applyThemeVariables
  }
}