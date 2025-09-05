import { createRouter, createWebHistory, RouteRecordRaw } from 'vue-router'
import store from '@/store'
import { useAuth } from '@/composables/useAuth'

// Lazy loading de componentes
const Home = () => import('@/views/Home.vue')
const Login = () => import('@/views/auth/Login.vue')
const Register = () => import('@/views/auth/Register.vue')
const ForgotPassword = () => import('@/views/auth/ForgotPassword.vue')
const ResetPassword = () => import('@/views/auth/ResetPassword.vue')
const BiometricSetup = () => import('@/views/auth/BiometricSetup.vue')

// Dashboard
const Dashboard = () => import('@/views/Dashboard.vue')

// Usuário
const Profile = () => import('@/views/user/Profile.vue')
const Preferences = () => import('@/views/user/Preferences.vue')
const Security = () => import('@/views/user/Security.vue')

// Votações
const VotingList = () => import('@/views/voting/VotingList.vue')
const VotingDetail = () => import('@/views/voting/VotingDetail.vue')
const VotingCreate = () => import('@/views/voting/VotingCreate.vue')
const VotingResults = () => import('@/views/voting/VotingResults.vue')

// Convênios
const ConvenioList = () => import('@/views/convenio/ConvenioList.vue')
const ConvenioDetail = () => import('@/views/convenio/ConvenioDetail.vue')
const ConvenioMap = () => import('@/views/convenio/ConvenioMap.vue')

// Notícias
const NewsList = () => import('@/views/news/NewsList.vue')
const NewsDetail = () => import('@/views/news/NewsDetail.vue')
const NewsCreate = () => import('@/views/news/NewsCreate.vue')
const NewsCategories = () => import('@/views/news/NewsCategories.vue')

// Notificações
const NotificationList = () => import('@/views/notification/NotificationList.vue')
const NotificationSettings = () => import('@/views/notification/NotificationSettings.vue')

// Administração
const AdminDashboard = () => import('@/views/admin/AdminDashboard.vue')
const UserManagement = () => import('@/views/admin/UserManagement.vue')
const SystemSettings = () => import('@/views/admin/SystemSettings.vue')
const Analytics = () => import('@/views/admin/Analytics.vue')

// Páginas de erro
const NotFound = () => import('@/views/errors/NotFound.vue')
const Unauthorized = () => import('@/views/errors/Unauthorized.vue')
const ServerError = () => import('@/views/errors/ServerError.vue')

const routes: Array<RouteRecordRaw> = [
  {
    path: '/',
    name: 'Home',
    component: Home,
    meta: {
      title: 'Início',
      requiresAuth: false,
      hideHeader: false,
      hideSidebar: false,
      hideFooter: false
    }
  },
  {
    path: '/auth',
    name: 'Auth',
    redirect: '/auth/login',
    meta: {
      requiresAuth: false,
      hideHeader: true,
      hideSidebar: true,
      hideFooter: true,
      layout: 'auth'
    },
    children: [
      {
        path: 'login',
        name: 'Login',
        component: Login,
        meta: {
          title: 'Entrar',
          transition: 'slide-right'
        }
      },
      {
        path: 'register',
        name: 'Register',
        component: Register,
        meta: {
          title: 'Cadastrar',
          transition: 'slide-left'
        }
      },
      {
        path: 'forgot-password',
        name: 'ForgotPassword',
        component: ForgotPassword,
        meta: {
          title: 'Esqueci a Senha'
        }
      },
      {
        path: 'reset-password',
        name: 'ResetPassword',
        component: ResetPassword,
        meta: {
          title: 'Redefinir Senha'
        }
      },
      {
        path: 'biometric-setup',
        name: 'BiometricSetup',
        component: BiometricSetup,
        meta: {
          title: 'Configurar Biometria',
          requiresAuth: true
        }
      }
    ]
  },
  {
    path: '/dashboard',
    name: 'Dashboard',
    component: Dashboard,
    meta: {
      title: 'Dashboard',
      requiresAuth: true,
      roles: ['user', 'admin']
    }
  },
  {
    path: '/user',
    name: 'User',
    redirect: '/user/profile',
    meta: {
      requiresAuth: true
    },
    children: [
      {
        path: 'profile',
        name: 'Profile',
        component: Profile,
        meta: {
          title: 'Perfil'
        }
      },
      {
        path: 'preferences',
        name: 'Preferences',
        component: Preferences,
        meta: {
          title: 'Preferências'
        }
      },
      {
        path: 'security',
        name: 'Security',
        component: Security,
        meta: {
          title: 'Segurança'
        }
      }
    ]
  },
  {
    path: '/voting',
    name: 'Voting',
    redirect: '/voting/list',
    meta: {
      requiresAuth: true
    },
    children: [
      {
        path: 'list',
        name: 'VotingList',
        component: VotingList,
        meta: {
          title: 'Votações'
        }
      },
      {
        path: 'create',
        name: 'VotingCreate',
        component: VotingCreate,
        meta: {
          title: 'Nova Votação',
          roles: ['admin']
        }
      },
      {
        path: ':id',
        name: 'VotingDetail',
        component: VotingDetail,
        meta: {
          title: 'Detalhes da Votação'
        }
      },
      {
        path: ':id/results',
        name: 'VotingResults',
        component: VotingResults,
        meta: {
          title: 'Resultados da Votação'
        }
      }
    ]
  },
  {
    path: '/convenios',
    name: 'Convenios',
    redirect: '/convenios/list',
    meta: {
      requiresAuth: true
    },
    children: [
      {
        path: 'list',
        name: 'ConvenioList',
        component: ConvenioList,
        meta: {
          title: 'Convênios'
        }
      },
      {
        path: 'map',
        name: 'ConvenioMap',
        component: ConvenioMap,
        meta: {
          title: 'Mapa de Convênios',
          fullHeight: true
        }
      },
      {
        path: ':id',
        name: 'ConvenioDetail',
        component: ConvenioDetail,
        meta: {
          title: 'Detalhes do Convênio'
        }
      }
    ]
  },
  {
    path: '/news',
    name: 'News',
    redirect: '/news/list',
    children: [
      {
        path: 'list',
        name: 'NewsList',
        component: NewsList,
        meta: {
          title: 'Notícias'
        }
      },
      {
        path: 'categories',
        name: 'NewsCategories',
        component: NewsCategories,
        meta: {
          title: 'Categorias'
        }
      },
      {
        path: 'create',
        name: 'NewsCreate',
        component: NewsCreate,
        meta: {
          title: 'Nova Notícia',
          requiresAuth: true,
          roles: ['admin', 'editor']
        }
      },
      {
        path: ':id',
        name: 'NewsDetail',
        component: NewsDetail,
        meta: {
          title: 'Notícia'
        }
      }
    ]
  },
  {
    path: '/notifications',
    name: 'Notifications',
    redirect: '/notifications/list',
    meta: {
      requiresAuth: true
    },
    children: [
      {
        path: 'list',
        name: 'NotificationList',
        component: NotificationList,
        meta: {
          title: 'Notificações'
        }
      },
      {
        path: 'settings',
        name: 'NotificationSettings',
        component: NotificationSettings,
        meta: {
          title: 'Configurações de Notificação'
        }
      }
    ]
  },
  {
    path: '/admin',
    name: 'Admin',
    redirect: '/admin/dashboard',
    meta: {
      requiresAuth: true,
      roles: ['admin']
    },
    children: [
      {
        path: 'dashboard',
        name: 'AdminDashboard',
        component: AdminDashboard,
        meta: {
          title: 'Painel Administrativo'
        }
      },
      {
        path: 'users',
        name: 'UserManagement',
        component: UserManagement,
        meta: {
          title: 'Gerenciar Usuários'
        }
      },
      {
        path: 'settings',
        name: 'SystemSettings',
        component: SystemSettings,
        meta: {
          title: 'Configurações do Sistema'
        }
      },
      {
        path: 'analytics',
        name: 'Analytics',
        component: Analytics,
        meta: {
          title: 'Análises'
        }
      }
    ]
  },
  {
    path: '/401',
    name: 'Unauthorized',
    component: Unauthorized,
    meta: {
      title: 'Não Autorizado',
      hideHeader: true,
      hideSidebar: true
    }
  },
  {
    path: '/500',
    name: 'ServerError',
    component: ServerError,
    meta: {
      title: 'Erro do Servidor',
      hideHeader: true,
      hideSidebar: true
    }
  },
  {
    path: '/:pathMatch(.*)*',
    name: 'NotFound',
    component: NotFound,
    meta: {
      title: 'Página Não Encontrada',
      hideHeader: true,
      hideSidebar: true
    }
  }
]

const router = createRouter({
  history: createWebHistory(process.env.BASE_URL),
  routes,
  scrollBehavior(to, from, savedPosition) {
    if (savedPosition) {
      return savedPosition
    } else if (to.hash) {
      return {
        el: to.hash,
        behavior: 'smooth'
      }
    } else {
      return { top: 0 }
    }
  }
})

// Guards de navegação
router.beforeEach(async (to, from, next) => {
  const { isAuthenticated, hasRole } = useAuth()
  
  // Atualizar título da página
  if (to.meta?.title) {
    document.title = `${to.meta.title} - Sistema de Gestão Sindical`
  }
  
  // Verificar autenticação
  if (to.meta?.requiresAuth && !isAuthenticated.value) {
    next({ name: 'Login', query: { redirect: to.fullPath } })
    return
  }
  
  // Verificar roles
  if (to.meta?.roles && !hasRole(to.meta.roles)) {
    next({ name: 'Unauthorized' })
    return
  }
  
  // Redirecionar usuários autenticados da página de login
  if (to.name === 'Login' && isAuthenticated.value) {
    next({ name: 'Dashboard' })
    return
  }
  
  next()
})

router.afterEach((to, from) => {
  // Analytics de navegação
  if (process.env.NODE_ENV === 'production') {
    // TODO: Integrar com Google Analytics ou similar
  }
  
  // Log de navegação para desenvolvimento
  if (process.env.NODE_ENV === 'development') {
    console.log(`Navegação: ${from.path} → ${to.path}`)
  }
})

export default router