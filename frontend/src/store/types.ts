// Tipos base
export interface User {
  id: number
  name: string
  email: string
  cpf: string
  phone?: string
  avatar?: string
  role: 'user' | 'admin' | 'editor'
  status: 'active' | 'inactive' | 'suspended'
  email_verified_at?: string
  biometric_enabled: boolean
  two_factor_enabled: boolean
  last_login_at?: string
  created_at: string
  updated_at: string
}

export interface UserPreferences {
  theme: 'light' | 'dark' | 'system'
  density: 'comfortable' | 'compact' | 'spacious'
  language: 'pt-BR' | 'en-US' | 'es-ES'
  timezone: string
  notifications: {
    email: boolean
    push: boolean
    sms: boolean
    voting: boolean
    news: boolean
    convenios: boolean
    system: boolean
  }
  sidebar_collapsed: boolean
  auto_save: boolean
  sound_enabled: boolean
}

export interface ToastMessage {
  id: string
  type: 'success' | 'error' | 'warning' | 'info'
  title?: string
  message: string
  duration?: number
  persistent?: boolean
  actions?: Array<{
    label: string
    action: () => void
    style?: 'primary' | 'secondary'
  }>
  created_at: number
}

export interface Voting {
  id: number
  title: string
  description: string
  type: 'simple' | 'multiple' | 'ranked'
  status: 'draft' | 'active' | 'paused' | 'ended' | 'cancelled'
  start_date: string
  end_date: string
  quorum_required: number
  quorum_reached: boolean
  total_votes: number
  total_eligible: number
  options: VotingOption[]
  results?: VotingResults
  can_vote: boolean
  user_voted: boolean
  created_by: User
  created_at: string
  updated_at: string
}

export interface VotingOption {
  id: number
  voting_id: number
  title: string
  description?: string
  order: number
  votes_count: number
  percentage: number
}

export interface VotingResults {
  total_votes: number
  quorum_percentage: number
  winner?: VotingOption
  options: VotingOption[]
  participation_rate: number
}

export interface Convenio {
  id: number
  name: string
  description: string
  category: string
  type: 'discount' | 'service' | 'product'
  discount_percentage?: number
  discount_value?: number
  address: string
  city: string
  state: string
  zip_code: string
  latitude?: number
  longitude?: number
  phone?: string
  email?: string
  website?: string
  whatsapp?: string
  instagram?: string
  facebook?: string
  opening_hours: string
  qr_code: string
  logo?: string
  images: string[]
  rating: number
  reviews_count: number
  views_count: number
  uses_count: number
  is_featured: boolean
  is_active: boolean
  expires_at?: string
  created_at: string
  updated_at: string
}

export interface News {
  id: number
  title: string
  slug: string
  excerpt: string
  content: string
  featured_image?: string
  gallery: string[]
  category: NewsCategory
  tags: string[]
  status: 'draft' | 'published' | 'archived'
  priority: 'low' | 'normal' | 'high' | 'urgent'
  is_featured: boolean
  is_breaking: boolean
  is_pinned: boolean
  views_count: number
  likes_count: number
  shares_count: number
  comments_count: number
  reading_time: number
  published_at?: string
  expires_at?: string
  author: User
  created_at: string
  updated_at: string
}

export interface NewsCategory {
  id: number
  name: string
  slug: string
  description?: string
  color: string
  icon?: string
  parent_id?: number
  children?: NewsCategory[]
  news_count: number
  is_active: boolean
  created_at: string
  updated_at: string
}

export interface Notification {
  id: string
  type: 'system' | 'voting' | 'news' | 'convenio' | 'user'
  title: string
  message: string
  data?: Record<string, any>
  read_at?: string
  action_url?: string
  action_text?: string
  priority: 'low' | 'normal' | 'high' | 'urgent'
  channels: ('database' | 'mail' | 'push' | 'sms')[]
  created_at: string
}

export interface SocketConnection {
  connected: boolean
  connecting: boolean
  error?: string
  last_connected_at?: string
  reconnect_attempts: number
}

// Estados dos módulos
export interface AppState {
  loading: boolean
  online: boolean
  mobile: boolean
  sidebar_open: boolean
  version: string
  environment: 'development' | 'staging' | 'production'
  maintenance_mode: boolean
  features: Record<string, boolean>
}

export interface AuthState {
  user: User | null
  token: string | null
  refresh_token: string | null
  authenticated: boolean
  loading: boolean
  biometric_available: boolean
  two_factor_required: boolean
  login_attempts: number
  locked_until?: string
}

export interface UserState {
  preferences: UserPreferences
  profile_completion: number
  activity_log: any[]
  statistics: {
    votes_cast: number
    news_read: number
    convenios_used: number
    login_streak: number
  }
}

export interface ThemeState {
  current: 'light' | 'dark' | 'system'
  density: 'comfortable' | 'compact' | 'spacious'
  sidebar_collapsed: boolean
  custom_colors: Record<string, string>
  animations_enabled: boolean
}

export interface ToastState {
  messages: ToastMessage[]
  position: 'top-right' | 'top-left' | 'bottom-right' | 'bottom-left' | 'top-center' | 'bottom-center'
  max_messages: number
  default_duration: number
}

export interface VotingState {
  list: Voting[]
  current: Voting | null
  loading: boolean
  filters: {
    status: string[]
    type: string[]
    search: string
    date_range: [string, string] | null
  }
  pagination: {
    page: number
    per_page: number
    total: number
    last_page: number
  }
  real_time_updates: boolean
}

export interface ConvenioState {
  list: Convenio[]
  current: Convenio | null
  loading: boolean
  filters: {
    category: string[]
    type: string[]
    city: string[]
    search: string
    location: {
      latitude: number
      longitude: number
      radius: number
    } | null
  }
  pagination: {
    page: number
    per_page: number
    total: number
    last_page: number
  }
  favorites: number[]
  recent_views: number[]
}

export interface NewsState {
  list: News[]
  current: News | null
  categories: NewsCategory[]
  loading: boolean
  filters: {
    category: string[]
    status: string[]
    priority: string[]
    search: string
    date_range: [string, string] | null
  }
  pagination: {
    page: number
    per_page: number
    total: number
    last_page: number
  }
  bookmarks: number[]
  reading_history: number[]
}

export interface NotificationState {
  list: Notification[]
  unread_count: number
  loading: boolean
  settings: {
    email: boolean
    push: boolean
    sms: boolean
    categories: Record<string, boolean>
  }
  filters: {
    type: string[]
    read: boolean | null
    priority: string[]
    date_range: [string, string] | null
  }
  pagination: {
    page: number
    per_page: number
    total: number
    last_page: number
  }
}

export interface SocketState {
  connection: SocketConnection
  rooms: string[]
  events: Record<string, any[]>
  last_ping: string | null
  latency: number
}

// Estado raiz
export interface RootState {
  app: AppState
  auth: AuthState
  user: UserState
  theme: ThemeState
  toast: ToastState
  voting: VotingState
  convenio: ConvenioState
  news: NewsState
  notification: NotificationState
  socket: SocketState
}

// Tipos para mutations
export interface MutationTree<S> {
  [key: string]: (state: S, payload?: any) => void
}

// Tipos para actions
export interface ActionTree<S, R> {
  [key: string]: (context: ActionContext<S, R>, payload?: any) => any
}

// Tipos para getters
export interface GetterTree<S, R> {
  [key: string]: (state: S, getters: any, rootState: R, rootGetters: any) => any
}

// Context para actions
export interface ActionContext<S, R> {
  dispatch: (type: string, payload?: any) => Promise<any>
  commit: (type: string, payload?: any) => void
  state: S
  getters: any
  rootState: R
  rootGetters: any
}

// Tipos para API responses
export interface ApiResponse<T = any> {
  data: T
  message?: string
  status: number
  success: boolean
}

export interface PaginatedResponse<T = any> {
  data: T[]
  current_page: number
  per_page: number
  total: number
  last_page: number
  from: number
  to: number
  links: {
    first: string
    last: string
    prev: string | null
    next: string | null
  }
}

// Tipos para formulários
export interface LoginForm {
  email: string
  password: string
  remember: boolean
  biometric?: boolean
}

export interface RegisterForm {
  name: string
  email: string
  cpf: string
  phone: string
  password: string
  password_confirmation: string
  terms_accepted: boolean
}

export interface VotingForm {
  title: string
  description: string
  type: 'simple' | 'multiple' | 'ranked'
  start_date: string
  end_date: string
  quorum_required: number
  options: Array<{
    title: string
    description?: string
  }>
}

// Tipos para validação
export interface ValidationError {
  field: string
  message: string
}

export interface FormErrors {
  [key: string]: string[]
}