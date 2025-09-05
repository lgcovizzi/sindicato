import { createStore } from 'vuex'
import { RootState } from './types'

// Módulos do store
import app from './modules/app'
import auth from './modules/auth'
import user from './modules/user'
import theme from './modules/theme'
import toast from './modules/toast'
import voting from './modules/voting'
import convenio from './modules/convenio'
import news from './modules/news'
import notification from './modules/notification'
import socket from './modules/socket'

const store = createStore<RootState>({
  modules: {
    app,
    auth,
    user,
    theme,
    toast,
    voting,
    convenio,
    news,
    notification,
    socket
  },
  
  strict: process.env.NODE_ENV !== 'production',
  
  plugins: [
    // Plugin para persistir estado no localStorage
    (store) => {
      // Carregar estado inicial do localStorage
      const savedState = localStorage.getItem('vuex-state')
      if (savedState) {
        try {
          const parsedState = JSON.parse(savedState)
          // Apenas restaurar módulos específicos
          if (parsedState.theme) {
            store.commit('theme/RESTORE_STATE', parsedState.theme)
          }
          if (parsedState.user?.preferences) {
            store.commit('user/SET_PREFERENCES', parsedState.user.preferences)
          }
        } catch (error) {
          console.error('Erro ao carregar estado do localStorage:', error)
          localStorage.removeItem('vuex-state')
        }
      }
      
      // Salvar estado no localStorage quando houver mudanças
      store.subscribe((mutation, state) => {
        // Lista de mutations que devem persistir o estado
        const persistMutations = [
          'theme/SET_THEME',
          'theme/SET_DENSITY',
          'theme/SET_SIDEBAR_COLLAPSED',
          'user/SET_PREFERENCES',
          'user/UPDATE_PREFERENCE'
        ]
        
        if (persistMutations.includes(mutation.type)) {
          const stateToSave = {
            theme: state.theme,
            user: {
              preferences: state.user.preferences
            }
          }
          
          try {
            localStorage.setItem('vuex-state', JSON.stringify(stateToSave))
          } catch (error) {
            console.error('Erro ao salvar estado no localStorage:', error)
          }
        }
      })
    },
    
    // Plugin para logging em desenvolvimento
    ...(process.env.NODE_ENV === 'development' ? [
      (store) => {
        store.subscribe((mutation, state) => {
          console.group(`🔄 Mutation: ${mutation.type}`)
          console.log('Payload:', mutation.payload)
          console.log('State after:', state)
          console.groupEnd()
        })
        
        store.subscribeAction((action, state) => {
          console.group(`⚡ Action: ${action.type}`)
          console.log('Payload:', action.payload)
          console.log('State before:', state)
          console.groupEnd()
        })
      }
    ] : [])
  ]
})

// Hot Module Replacement para desenvolvimento
if (module.hot) {
  // Aceitar atualizações dos módulos
  module.hot.accept([
    './modules/app',
    './modules/auth',
    './modules/user',
    './modules/theme',
    './modules/toast',
    './modules/voting',
    './modules/convenio',
    './modules/news',
    './modules/notification',
    './modules/socket'
  ], () => {
    // Recarregar os módulos atualizados
    const newApp = require('./modules/app').default
    const newAuth = require('./modules/auth').default
    const newUser = require('./modules/user').default
    const newTheme = require('./modules/theme').default
    const newToast = require('./modules/toast').default
    const newVoting = require('./modules/voting').default
    const newConvenio = require('./modules/convenio').default
    const newNews = require('./modules/news').default
    const newNotification = require('./modules/notification').default
    const newSocket = require('./modules/socket').default
    
    store.hotUpdate({
      modules: {
        app: newApp,
        auth: newAuth,
        user: newUser,
        theme: newTheme,
        toast: newToast,
        voting: newVoting,
        convenio: newConvenio,
        news: newNews,
        notification: newNotification,
        socket: newSocket
      }
    })
  })
}

export default store