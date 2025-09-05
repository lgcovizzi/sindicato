import { Module } from 'vuex'
import { ToastState, ToastMessage, RootState } from '../types'

const state: ToastState = {
  messages: [],
  position: 'top-right',
  max_messages: 5,
  default_duration: 5000
}

const mutations = {
  ADD_MESSAGE(state: ToastState, message: ToastMessage) {
    // Verificar se já existe uma mensagem com o mesmo ID
    const existingIndex = state.messages.findIndex(m => m.id === message.id)
    if (existingIndex !== -1) {
      // Atualizar mensagem existente
      state.messages.splice(existingIndex, 1, message)
    } else {
      // Adicionar nova mensagem
      state.messages.push(message)
      
      // Limitar número máximo de mensagens
      if (state.messages.length > state.max_messages) {
        state.messages.shift()
      }
    }
  },
  
  REMOVE_MESSAGE(state: ToastState, messageId: string) {
    const index = state.messages.findIndex(m => m.id === messageId)
    if (index !== -1) {
      state.messages.splice(index, 1)
    }
  },
  
  CLEAR_MESSAGES(state: ToastState) {
    state.messages = []
  },
  
  CLEAR_MESSAGES_BY_TYPE(state: ToastState, type: ToastMessage['type']) {
    state.messages = state.messages.filter(m => m.type !== type)
  },
  
  SET_POSITION(state: ToastState, position: ToastState['position']) {
    state.position = position
  },
  
  SET_MAX_MESSAGES(state: ToastState, max: number) {
    state.max_messages = max
    
    // Remover mensagens excedentes
    if (state.messages.length > max) {
      state.messages = state.messages.slice(-max)
    }
  },
  
  SET_DEFAULT_DURATION(state: ToastState, duration: number) {
    state.default_duration = duration
  }
}

const actions = {
  show({ commit, state }: any, payload: Partial<ToastMessage> & { message: string }) {
    const message: ToastMessage = {
      id: payload.id || `toast-${Date.now()}-${Math.random().toString(36).substr(2, 9)}`,
      type: payload.type || 'info',
      title: payload.title,
      message: payload.message,
      duration: payload.duration ?? state.default_duration,
      persistent: payload.persistent || false,
      actions: payload.actions || [],
      created_at: Date.now()
    }
    
    commit('ADD_MESSAGE', message)
    
    // Auto-remover mensagem se não for persistente
    if (!message.persistent && message.duration > 0) {
      setTimeout(() => {
        commit('REMOVE_MESSAGE', message.id)
      }, message.duration)
    }
    
    return message.id
  },
  
  success({ dispatch }: any, payload: string | Partial<ToastMessage>) {
    const message = typeof payload === 'string' 
      ? { message: payload, type: 'success' as const }
      : { ...payload, type: 'success' as const }
    
    return dispatch('show', message)
  },
  
  error({ dispatch }: any, payload: string | Partial<ToastMessage>) {
    const message = typeof payload === 'string' 
      ? { message: payload, type: 'error' as const, duration: 8000 }
      : { ...payload, type: 'error' as const, duration: payload.duration || 8000 }
    
    return dispatch('show', message)
  },
  
  warning({ dispatch }: any, payload: string | Partial<ToastMessage>) {
    const message = typeof payload === 'string' 
      ? { message: payload, type: 'warning' as const }
      : { ...payload, type: 'warning' as const }
    
    return dispatch('show', message)
  },
  
  info({ dispatch }: any, payload: string | Partial<ToastMessage>) {
    const message = typeof payload === 'string' 
      ? { message: payload, type: 'info' as const }
      : { ...payload, type: 'info' as const }
    
    return dispatch('show', message)
  },
  
  hide({ commit }: any, messageId: string) {
    commit('REMOVE_MESSAGE', messageId)
  },
  
  clear({ commit }: any, type?: ToastMessage['type']) {
    if (type) {
      commit('CLEAR_MESSAGES_BY_TYPE', type)
    } else {
      commit('CLEAR_MESSAGES')
    }
  },
  
  setPosition({ commit }: any, position: ToastState['position']) {
    commit('SET_POSITION', position)
  },
  
  setMaxMessages({ commit }: any, max: number) {
    commit('SET_MAX_MESSAGES', max)
  },
  
  setDefaultDuration({ commit }: any, duration: number) {
    commit('SET_DEFAULT_DURATION', duration)
  },
  
  // Métodos de conveniência para casos específicos
  showLoading({ dispatch }: any, message = 'Carregando...') {
    return dispatch('show', {
      id: 'loading',
      type: 'info',
      message,
      persistent: true,
      duration: 0
    })
  },
  
  hideLoading({ commit }: any) {
    commit('REMOVE_MESSAGE', 'loading')
  },
  
  showSaved({ dispatch }: any, message = 'Salvo com sucesso!') {
    return dispatch('success', {
      message,
      duration: 3000
    })
  },
  
  showDeleted({ dispatch }: any, message = 'Excluído com sucesso!') {
    return dispatch('success', {
      message,
      duration: 3000
    })
  },
  
  showNetworkError({ dispatch }: any, message = 'Erro de conexão. Verifique sua internet.') {
    return dispatch('error', {
      message,
      persistent: true,
      actions: [
        {
          label: 'Tentar novamente',
          action: () => window.location.reload(),
          style: 'primary'
        }
      ]
    })
  },
  
  showValidationErrors({ dispatch }: any, errors: Record<string, string[]>) {
    const messages = Object.entries(errors)
      .map(([field, fieldErrors]) => `${field}: ${fieldErrors.join(', ')}`)
      .join('\n')
    
    return dispatch('error', {
      title: 'Erro de validação',
      message: messages,
      duration: 10000
    })
  },
  
  showUnauthorized({ dispatch }: any, message = 'Você não tem permissão para esta ação.') {
    return dispatch('error', {
      message,
      duration: 6000
    })
  },
  
  showMaintenanceMode({ dispatch }: any, message = 'Sistema em manutenção. Tente novamente em alguns minutos.') {
    return dispatch('warning', {
      message,
      persistent: true
    })
  },
  
  showUpdateAvailable({ dispatch }: any) {
    return dispatch('info', {
      id: 'update-available',
      title: 'Atualização disponível',
      message: 'Uma nova versão está disponível.',
      persistent: true,
      actions: [
        {
          label: 'Atualizar',
          action: () => window.location.reload(),
          style: 'primary'
        },
        {
          label: 'Depois',
          action: () => dispatch('hide', 'update-available'),
          style: 'secondary'
        }
      ]
    })
  }
}

const getters = {
  messages: (state: ToastState) => state.messages,
  
  messagesByType: (state: ToastState) => (type: ToastMessage['type']) => {
    return state.messages.filter(m => m.type === type)
  },
  
  hasMessages: (state: ToastState) => state.messages.length > 0,
  
  hasMessagesByType: (state: ToastState) => (type: ToastMessage['type']) => {
    return state.messages.some(m => m.type === type)
  },
  
  messageCount: (state: ToastState) => state.messages.length,
  
  messageCountByType: (state: ToastState) => (type: ToastMessage['type']) => {
    return state.messages.filter(m => m.type === type).length
  },
  
  position: (state: ToastState) => state.position,
  
  maxMessages: (state: ToastState) => state.max_messages,
  
  defaultDuration: (state: ToastState) => state.default_duration,
  
  isLoading: (state: ToastState) => {
    return state.messages.some(m => m.id === 'loading')
  },
  
  latestMessage: (state: ToastState) => {
    return state.messages.length > 0 ? state.messages[state.messages.length - 1] : null
  },
  
  oldestMessage: (state: ToastState) => {
    return state.messages.length > 0 ? state.messages[0] : null
  }
}

const toast: Module<ToastState, RootState> = {
  namespaced: true,
  state,
  mutations,
  actions,
  getters
}

export default toast