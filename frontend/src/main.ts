import { createApp } from 'vue'
import App from './App.vue'
import router from './router'
import store from './store'
import i18n from './plugins/i18n'
import './plugins/element-plus'
import './plugins/fontawesome'
import './plugins/axios'
import './plugins/socket'
import './registerServiceWorker'
import './styles/main.scss'

// Configuração global da aplicação
const app = createApp(App)

// Plugins e configurações
app.use(store)
app.use(router)
app.use(i18n)

// Configurações globais
app.config.globalProperties.$appName = 'Sistema de Gestão Sindical'
app.config.globalProperties.$version = '1.0.0'

// Error handler global
app.config.errorHandler = (err, instance, info) => {
  console.error('Global error:', err)
  console.error('Component instance:', instance)
  console.error('Error info:', info)
  
  // Enviar erro para serviço de monitoramento em produção
  if (process.env.NODE_ENV === 'production') {
    // TODO: Integrar com serviço de monitoramento (Sentry, etc.)
  }
}

// Performance monitoring
if (process.env.NODE_ENV === 'development') {
  app.config.performance = true
}

// Mount da aplicação
app.mount('#app')

// Hot Module Replacement para desenvolvimento
if (module.hot) {
  module.hot.accept()
}