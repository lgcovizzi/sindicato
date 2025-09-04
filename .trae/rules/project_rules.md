# Sistema de Gestão Sindical

## Visão Geral
Sistema completo de gestão sindical com arquitetura moderna, segura e escalável, desenvolvido para atender às necessidades de sindicatos e suas operações administrativas.
o arquivo de regras não deve ter implementação de códigos. 

## Arquitetura Técnica

### Infraestrutura
- **Containerização**: Projeto dockerizado para facilitar deploy e escalabilidade
- **Banco de Dados**: PostgreSQL como banco principal
- **Cache**: Redis para otimização de performance
- **Ambiente**: Desenvolvimento e produção isolados via Docker

### Backend - Laravel Framework
- **Framework**: Laravel (PHP)
- **API**: RESTful para comunicação com frontend
- **ORM**: Eloquent ORM para mapeamento objeto-relacional
- **WebSocket**: Socket.io integrado para comunicação em tempo real
- **Broadcasting**: Laravel Broadcasting com Redis para eventos em tempo real
- **Cache Layers**:
  - Cache de rotas
  - Cache de views
  - Cache de models
  - Cache de controllers
  - Cache de middleware
  - Cache de configurações
  - Cache de traduções (lang)
  - Cache de recursos públicos

#### Sistema de Tempo Real (WebSocket)
- **Socket.io Server**: Servidor Node.js integrado ao Laravel
- **Laravel Broadcasting**: Sistema de eventos em tempo real
- **Redis Pub/Sub**: Comunicação entre Laravel e Socket.io
- **Autenticação WebSocket**: JWT token validation para conexões
- **Salas de Votação**: Namespaces específicos por votação
- **Event Broadcasting**: Eventos automáticos do Laravel para Socket.io
- **Configuração Técnica**:
  - **Servidor Socket.io**: Porta 6001 (configurável)
  - **Redis Channel**: `laravel_database_voting_channel`
  - **Middleware de Autenticação**: Validação de token JWT
  - **Rate Limiting**: 100 eventos por minuto por usuário
  - **Heartbeat**: Ping/pong a cada 25 segundos
  - **Timeout**: Desconexão após 60 segundos de inatividade
- **Eventos Laravel Broadcasting**:
  - `VotingStarted`: Disparado ao iniciar votação
  - `VotingEnded`: Disparado ao encerrar votação
  - `VoteCast`: Disparado a cada novo voto
  - `ResultsUpdated`: Disparado ao atualizar resultados
  - `QuorumReached`: Disparado ao atingir quórum
- **Integração com Frontend**:
  - **Vue.js**: Socket.io-client para conexão
  - **React Native**: Socket.io-client mobile
  - **Reconnection**: Estratégia de reconexão automática
  - **State Sync**: Sincronização de estado em tempo real

#### Eloquent ORM
- **Active Record Pattern**: Cada model representa uma tabela do banco de dados
- **Relacionamentos**: Suporte completo a relacionamentos (hasOne, hasMany, belongsTo, belongsToMany)
- **Query Builder**: Interface fluente para construção de queries complexas
- **Migrations**: Controle de versão do schema do banco de dados
- **Seeders**: População inicial e de teste do banco de dados
- **Mutators e Accessors**: Transformação automática de dados
- **Scopes**: Reutilização de queries comuns
- **Soft Deletes**: Exclusão lógica de registros
- **Mass Assignment Protection**: Proteção contra atribuição em massa
- **Model Events**: Hooks para eventos do ciclo de vida dos models
- **Eager Loading**: Carregamento otimizado de relacionamentos
- **Pagination**: Paginação automática de resultados

### Frontend - Vue.js Ecosystem
- **Framework**: Vue.js 3
- **Gerenciamento de Estado**: Vuex
- **Roteamento**: Vue Router
- **HTTP Client**: Axios para consumo de APIs
- **WebSocket**: Socket.io-client para comunicação em tempo real
- **UI Framework**: Element Plus
- **Estilização**: Bootstrap
- **Ícones**: Font Awesome
- **Build Tool**: Vue CLI com plugins específicos

#### Integração WebSocket (Vue.js)
- **Socket.io Client**: Conexão com servidor Socket.io
- **Vuex Integration**: Estado reativo para eventos em tempo real
- **Component Reactivity**: Atualizações automáticas da interface
- **Event Handling**: Listeners para eventos de votação
- **Connection Management**: Gerenciamento de conexão e reconexão
- **Real-time Components**:
  - **VotingDashboard**: Dashboard com atualizações em tempo real
  - **VotingResults**: Gráficos e contadores dinâmicos
  - **VotingStatus**: Indicadores de status e conectividade
  - **ParticipantCounter**: Contador de participantes online
  - **ProgressBar**: Barra de progresso em tempo real

### Aplicativo Mobile - React Native com Expo

#### Arquitetura Mobile
- **Framework**: React Native com Expo SDK
- **Linguagem**: TypeScript para tipagem estática e maior segurança
- **Plataformas**: iOS e Android (build universal)
- **Gerenciamento de Estado**: Redux Toolkit + RTK Query
- **Navegação**: React Navigation 6
- **HTTP Client**: Axios com interceptors para autenticação
- **UI Framework**: React Native Elements + NativeBase
- **Ícones**: Expo Vector Icons
- **Build Tool**: Expo CLI com EAS Build
- **Deployment**: Expo Application Services (EAS)
- **Tipagem**: Interfaces TypeScript para todas as entidades e APIs
- **Linting**: ESLint + TypeScript ESLint para qualidade de código

#### Funcionalidades Principais

**Sistema de Autenticação**:
- **Login/Registro**: Interface nativa com validação em tempo real
- **Biometria**: Touch ID/Face ID para acesso rápido
- **JWT Storage**: Armazenamento seguro com Expo SecureStore
- **Auto-login**: Persistência de sessão com refresh automático
- **Logout Seguro**: Limpeza completa de dados locais

**Carteirinha Digital de Associado**:
- **QR Code Dinâmico**: Geração em tempo real com dados criptografados
- **Dados do Associado**: Nome, foto, número de matrícula, validade
- **Status de Associação**: Indicador visual de situação (ativo/inativo)
- **Foto de Perfil**: Sincronização automática com backend
- **Validação Offline**: Cache local para funcionamento sem internet
- **Design Responsivo**: Layout adaptável para diferentes tamanhos de tela
- **Animações**: Transições suaves e feedback visual
- **Compartilhamento**: Opção de compartilhar QR code via apps nativos

**Sistema de Convênios**:
- **Catálogo de Convênios**: Lista com filtros e busca
- **Detalhes do Convênio**: Informações completas, localização, contatos
- **QR Code de Utilização**: Geração para validação pelo parceiro
- **Histórico de Uso**: Lista de convênios utilizados com datas
- **Favoritos**: Sistema de marcação de convênios preferidos
- **Notificações Push**: Alertas sobre novos convênios e promoções
- **Mapa Integrado**: Localização de parceiros com GPS
- **Avaliações**: Sistema de rating e comentários

**Sistema de Notícias**:
- **Feed de Notícias**: Lista com imagens, títulos e resumos
- **Leitura Completa**: Visualização de notícias com formatação rica
- **Categorias**: Filtros por tipo de notícia
- **Busca**: Pesquisa por título e conteúdo
- **Compartilhamento**: Integração com apps nativos (WhatsApp, email, redes sociais)
- **Favoritos**: Sistema de bookmark para notícias
- **Modo Offline**: Cache de notícias para leitura sem internet
- **Push Notifications**: Alertas sobre notícias importantes

**Sistema de Votações**:
- **Lista de Votações Ativas**: Votações disponíveis para o usuário
- **Interface de Votação**: Telas intuitivas para diferentes tipos de voto
- **Confirmação Biométrica**: Validação por biometria antes do voto
- **Histórico de Participação**: Lista de votações já participadas
- **Resultados em Tempo Real**: Visualização instantânea via WebSocket
- **Notificações Push**: Alertas sobre novas votações e resultados
- **Validação de Elegibilidade**: Verificação automática de permissões
- **Voto Seguro**: Criptografia end-to-end para proteção do voto
- **Conexão WebSocket**: Atualizações em tempo real de resultados e status
- **Indicadores Visuais**: Animações e feedback instantâneo para novos votos
- **Sincronização Automática**: Estado consistente entre dispositivos
- **Modo Offline**: Fallback gracioso quando sem conexão
- **Sistema Híbrido de Votação**:
  - **Modo Tempo Real (WebSocket)**: Atualizações instantâneas quando conectado
  - **Modo Polling (Fallback)**: Consultas periódicas à API quando WebSocket falha
  - **Detecção Automática**: Sistema detecta falhas de WebSocket e alterna automaticamente
  - **Reconexão Inteligente**: Tentativas automáticas de reconexão WebSocket
  - **Cache Local**: Armazenamento de resultados para exibição offline
  - **Sincronização Diferida**: Votos são enviados quando conexão é restaurada

#### Arquitetura Técnica

**Estrutura de Pastas**:
```
src/
├── components/          # Componentes reutilizáveis
│   ├── common/         # Componentes genéricos
│   ├── forms/          # Componentes de formulário
│   └── ui/             # Componentes de interface
├── screens/            # Telas da aplicação
│   ├── auth/          # Telas de autenticação
│   ├── home/          # Tela inicial
│   ├── profile/       # Perfil do usuário
│   ├── convenios/     # Sistema de convênios
│   ├── news/          # Sistema de notícias
│   ├── voting/        # Sistema de votações
│   └── identity/      # Carteirinha digital
├── navigation/         # Configuração de navegação
├── services/          # Serviços e APIs
├── store/             # Redux store e slices
├── utils/             # Utilitários e helpers
├── hooks/             # Custom hooks
└── constants/         # Constantes da aplicação
```

**Gerenciamento de Estado**:
- **Redux Toolkit**: Gerenciamento centralizado de estado da aplicação
- **Interfaces TypeScript**: Tipagem para User, AuthState e outras entidades
- **Auth Slice**: Controle de autenticação, login, logout e biometria
- **Actions**: loginSuccess, logout, enableBiometric, setLoading, setError
- **Estado Inicial**: Configuração padrão para usuário não autenticado

**API Integration**:
- **Interfaces de API**: Tipagem para LoginCredentials, LoginResponse
- **Entidades**: Convenio, News, Voting com suas respectivas propriedades
- **Autenticação**: Credenciais de login e resposta com token
- **Convênios**: Estrutura com parceiro, desconto, validade
- **Notícias**: Conteúdo, categoria, autor, data de publicação
- **Votações**: Tipos (single, multiple, ranked), opções, datas
- **Estrutura de Votação**: Status (active, ended, scheduled), palavra-chave, resultados
- **Opções de Votação**: ID, texto, contagem de votos
- **Resultados**: Total de votos, participação, opções
- **Serviços de API**: RTK Query para comunicação com backend
- **Endpoints**: Login, convênios, notícias, votações ativas
- **Autenticação**: Headers automáticos com Bearer token
- **Cache**: Tags para invalidação automática de dados
- **Mutations**: Login, submissão de votos
- **Queries**: Busca de convênios, notícias paginadas, votações, resultados

**WebSocket Integration com Sistema de Fallback**:
- **Eventos WebSocket**: Tipagem para eventos de votação em tempo real
- **Status de Conexão**: Controle de estado (websocket, polling, offline)
- **Fallback Automático**: Sistema de polling quando WebSocket falha
- **Reconexão**: Tentativas automáticas com limite máximo
- **Salas de Votação**: Gerenciamento de rooms para votações específicas
- **Eventos Suportados**: voting.started, voting.ended, vote.cast, results.updated, quorum.reached
- **Integração Redux**: Atualização automática do estado da aplicação
- **Timeout**: Configuração de timeout para conexões
- **Autenticação**: Token JWT para autenticação WebSocket
        resolve();
      });

      this.socket.on('disconnect', (reason) => {
        clearTimeout(connectTimeout);
        this.isConnected = false;
        this.updateConnectionStatus('websocket', false);
        console.log('WebSocket desconectado:', reason);
        
        // Start polling as fallback
        this.startPollingMode();
        
        // Attempt to reconnect
        this.attemptReconnect(token);
      });

      this.socket.on('connect_error', (error) => {
        clearTimeout(connectTimeout);
        console.error('Erro de conexão WebSocket:', error);
        reject(error);
      });

      this.setupEventListeners();
    });
  }

  private setupEventListeners(): void {
    if (!this.socket) return;

    // Eventos de votação em tempo real
    this.socket.on('voting.started', (data: WebSocketEvents['voting.started']) => {
      store.dispatch(addNewVoting(data.voting));
    });

    this.socket.on('voting.ended', (data: WebSocketEvents['voting.ended']) => {
      store.dispatch(endVoting(data.votingId));
    });

    this.socket.on('vote.cast', (data: WebSocketEvents['vote.cast']) => {
      store.dispatch(addNewVote(data));
    });

    this.socket.on('results.updated', (data: WebSocketEvents['results.updated']) => {
      store.dispatch(updateVotingResults(data));
    });

    this.socket.on('quorum.reached', (data: WebSocketEvents['quorum.reached']) => {
      store.dispatch(setQuorumReached(data.votingId));
    });
  }

  private startPollingMode(): void {
    if (this.pollingInterval) return;
    
    this.updateConnectionStatus('polling', true);
    console.log('Iniciando modo polling para votações');
    
    this.pollingInterval = setInterval(async () => {
      try {
        await this.pollVotingUpdates();
      } catch (error) {
        console.error('Erro no polling:', error);
        this.updateConnectionStatus('offline', false);
      }
    }, this.pollingIntervalMs);
  }

  private stopPollingMode(): void {
    if (this.pollingInterval) {
      clearInterval(this.pollingInterval);
      this.pollingInterval = null;
      console.log('Modo polling interrompido');
    }
  }

  private async pollVotingUpdates(): Promise<void> {
    // Poll active votings for updates
    for (const votingId of this.currentVotingRooms) {
      try {
        const results = await this.votingService.getVotingResults(votingId);
        store.dispatch(updateVotingResults({ votingId, results }));
      } catch (error) {
        console.error(`Erro ao buscar resultados da votação ${votingId}:`, error);
      }
    }
  }

  private attemptReconnect(token: string): void {
    if (this.reconnectAttempts >= this.maxReconnectAttempts) {
      console.log('Máximo de tentativas de reconexão atingido');
      return;
    }

    this.reconnectAttempts++;
    const delay = Math.min(1000 * Math.pow(2, this.reconnectAttempts), 30000); // Exponential backoff
    
    setTimeout(async () => {
      console.log(`Tentativa de reconexão ${this.reconnectAttempts}/${this.maxReconnectAttempts}`);
      try {
        await this.connectWebSocket(token);
      } catch (error) {
        console.warn('Reconexão WebSocket falhou, continuando com polling');
      }
    }, delay);
  }

  private rejoinVotingRooms(): void {
    for (const votingId of this.currentVotingRooms) {
      this.joinVotingRoom(votingId);
    }
  }

  private updateConnectionStatus(type: ConnectionStatus['connectionType'], connected: boolean): void {
    const status: ConnectionStatus = {
      isConnected: connected,
      connectionType: type,
      lastConnected: connected ? new Date() : undefined,
      reconnectAttempts: this.reconnectAttempts,
    };
    store.dispatch(setConnectionStatus(status));
  }

  joinVotingRoom(votingId: string): void {
    this.currentVotingRooms.add(votingId);
    
    if (this.socket && this.isConnected) {
      this.socket.emit('join-voting', { votingId });
      console.log(`Entrou na sala de votação: ${votingId}`);
    } else {
      console.log(`Sala de votação ${votingId} será rejoinada quando conectar`);
    }
  }

  leaveVotingRoom(votingId: string): void {
    this.currentVotingRooms.delete(votingId);
    
    if (this.socket && this.isConnected) {
      this.socket.emit('leave-voting', { votingId });
      console.log(`Saiu da sala de votação: ${votingId}`);
    }
  }

  disconnect(): void {
    this.stopPollingMode();
    this.currentVotingRooms.clear();
    
    if (this.socket) {
      this.socket.disconnect();
      this.socket = null;
      this.isConnected = false;
    }
    
    this.updateConnectionStatus('offline', false);
  }

  // Método para forçar atualização manual
  async forceUpdate(): Promise<void> {
    if (this.pollingInterval) {
      await this.pollVotingUpdates();
    }
  }

  // Getter para status de conexão
  get connectionStatus(): ConnectionStatus {
    return {
      isConnected: this.isConnected,
      connectionType: this.isConnected ? 'websocket' : (this.pollingInterval ? 'polling' : 'offline'),
      lastConnected: this.isConnected ? new Date() : undefined,
      reconnectAttempts: this.reconnectAttempts,
    };
  }
}

export default new WebSocketService();
```

**Serviço de Votação com Fallback**:
- **Cache Local**: AsyncStorage para armazenamento offline
- **Fallback Automático**: Uso de cache quando API falha
- **Votações Ativas**: Busca e cache de votações em andamento
- **Resultados**: Recuperação de resultados com fallback
- **Submissão de Votos**: Armazenamento para envio posterior em caso de falha
- **Expiração de Cache**: Controle de validade dos dados (30 segundos)
- **Votos Pendentes**: Sistema de fila para votos offline
- **Sincronização**: Reenvio automático quando conexão é restaurada




#### Carteirinha Digital - Implementação Detalhada

**Interfaces TypeScript**:
- **QRCodeData**: Estrutura para dados do QR code (userId, membershipNumber, timestamp, hash)
- **DigitalCardUser**: Informações do usuário para carteirinha
- **CardStyles**: Tipagem para estilos do componente
- **Status**: Estados da carteirinha (active, inactive, suspended)
- **Validação**: Controle de validade e autenticidade

**Componente Principal**:
- **React Native**: Componente funcional com hooks
- **Estado Local**: QR code data e animações
- **Redux Integration**: Acesso aos dados do usuário autenticado
- **QR Code Dinâmico**: Geração com timestamp e hash de segurança
- **Animações**: Spring animation para entrada da carteirinha
- **Gradiente**: LinearGradient para visual profissional
- **Loading State**: Tela de carregamento enquanto busca dados
- **Responsividade**: Layout adaptável para diferentes telas
- **Segurança**: Hash criptográfico para validação


**Segurança e Validação**:
- **Hash Criptográfico**: HMAC-SHA256 para validação de QR codes
- **Secure Store**: Armazenamento seguro de dados sensíveis
- **Variáveis de Ambiente**: Configuração de secrets via EXPO_PUBLIC
- **Tratamento de Erros**: Logs e exceções para falhas de segurança
- **Timestamp**: Validação temporal para evitar replay attacks
- **Criptografia**: CryptoJS para operações criptográficas
- **Validação**: Verificação de integridade dos dados


#### Sistema de Notificações Push

**Interfaces TypeScript**:
- **NotificationData**: Estrutura para dados de notificação (type, id, title, body, data)
- **PushTokenResponse**: Resposta do token de push notifications
- **NotificationPermissionStatus**: Status de permissões de notificação
- **Tipos**: voting, news, general, convenio para categorização

**Configuração Expo Notifications**:
- **Handler Configuration**: Configuração de comportamento das notificações
- **Registro de Token**: Obtenção do Expo Push Token
- **Verificação de Dispositivo**: Validação se é dispositivo físico
- **Permissões**: Solicitação e verificação de permissões
- **Tratamento de Erros**: Logs e fallbacks para falhas
- **Project ID**: Configuração via Constants do Expo
- **Notificações Locais**: Agendamento de notificações offline
- **Configuração de Som**: shouldShowAlert, shouldPlaySound, shouldSetBadge


#### Funcionalidades Offline

**Interfaces TypeScript**:
- **CachedData**: Estrutura genérica para dados em cache com timestamp
- **Offline Storage**: Gerenciamento de dados offline
- **Sincronização**: Controle de dados pendentes para sincronização
- **PendingAction**: Interface para ações pendentes (vote, sync_profile, upload_data)
- **NetworkState**: Estado da conexão de rede
- **OfflineManagerConfig**: Configurações de retry, delay e cache

**Cache e Sincronização**:
- **OfflineManager Class**: Gerenciamento de estado offline
- **Network Listener**: Monitoramento de conectividade
- **Pending Actions**: Fila de ações para sincronização
- **Auto Sync**: Sincronização automática quando conecta
- **Retry Logic**: Tentativas com delay configurável
- **Cache Management**: Controle de idade máxima do cache
- **Cache Data**: Armazenamento de dados com timestamp
- **Get Cached Data**: Recuperação com validação de idade
- **Pending Actions**: Gerenciamento de ações pendentes
- **Load/Save Actions**: Persistência no AsyncStorage
- **Auto Cleanup**: Remoção automática de cache expirado
- **Error Handling**: Tratamento de erros de storage
- **Sync Pending Actions**: Sincronização automática quando online
- **Execute Action**: Lógica específica por tipo (vote, sync_profile, upload_data)
- **Retry Management**: Controle de tentativas com limite máximo
- **Network State**: Getter para estado atual da conexão
- **Action Processing**: Processamento sequencial de ações pendentes
- **Error Recovery**: Remoção de ações que falharam múltiplas vezes
  }

  getPendingActionsCount(): number {
    return this.pendingActions.length;
  }

  async clearCache(): Promise<void> {
    try {
      const keys = await AsyncStorage.getAllKeys();
      const cacheKeys = keys.filter(key => key.startsWith('cache_'));
      await AsyncStorage.multiRemove(cacheKeys);
    } catch (error) {
      console.error('Erro ao limpar cache:', error);
      throw error;
    }
  }

  destroy(): void {
    if (this.networkUnsubscribe) {
      this.networkUnsubscribe();
    }
  }
}

export default OfflineManager;
```

#### Configuração TypeScript

**tsconfig.json**:
- **Extends**: Configuração base do Expo
- **Strict Mode**: TypeScript rigoroso habilitado
- **Target**: ES2020 para compatibilidade moderna
- **Module**: ESNext com resolução Node
- **Path Mapping**: Aliases para imports limpos (@/, @components/, @screens/, etc.)
- **JSX**: React JSX transform
- **Include/Exclude**: Arquivos TypeScript e exclusões

**types/index.ts (Arquivo principal de tipos)**:
- **Exports**: Centralização de todos os tipos (auth, digitalCard, notifications, offline, api, navigation, store)
- **BaseEntity**: Interface base com id, createdAt, updatedAt
- **ApiResponse**: Estrutura padrão de resposta da API
- **PaginatedResponse**: Resposta paginada com metadata
- **LoadingState**: Estados de carregamento (idle, loading, succeeded, failed)
- **AsyncState**: Estado assíncrono genérico com data, loading, error

**types/navigation.ts**:
- **RootStackParamList**: Navegação principal (Auth, Main, VotingDetails, ConvenioDetails, NewsDetails)
- **AuthStackParamList**: Stack de autenticação (Login, Register, ForgotPassword, BiometricSetup)
- **MainTabParamList**: Tabs principais (Home, DigitalCard, Convenios, News, Voting, Profile)
- **Screen Props**: Tipagem para props de telas (RootStackScreenProps, AuthStackScreenProps, MainTabScreenProps)
- **Navigation Props**: Props genéricas de navegação

**types/store.ts**:
- **RootState**: Tipo do estado global do Redux
- **AppDispatch**: Tipo do dispatch do store
- **TypedUseSelectorHook**: Hook tipado para useSelector

**babel.config.js (Configuração para TypeScript)**:
- **Presets**: babel-preset-expo para compatibilidade
- **Module Resolver**: Aliases para imports simplificados (@, @components, @screens, @services, @store, @types, @utils, @navigation)
- **Extensions**: Suporte para .js, .ts, .tsx, .json
- **Plugins**: react-native-reanimated/plugin para animações

#### Módulo de Gestão de Associados

**Tipos de Associados**:
- **Associados Ativos**: Membros em atividade profissional
- **Associados Aposentados**: Membros aposentados que mantêm vínculo com o sindicato

**Importante**: Ambos os tipos de associados (ativos e aposentados) pagam mensalidade do sindicato, mantendo seus direitos e benefícios.

**Funcionalidades Principais**:

**Upload e Processamento de Arquivo de Associados**:
- **Formato de Arquivo**: Arquivo de texto com layout fixo contendo dados dos associados
- **Processamento Assíncrono**: Parser executado em background via Laravel Queue
- **Notificação de Conclusão**: Toast notification para o fundador após processamento
- **Validação de Dados**: Verificação de integridade dos dados durante o parser
- **Log de Processamento**: Registro detalhado de sucessos e erros
- **Backup Automático**: Cópia de segurança do arquivo original

**Layout do Arquivo de Associados Ativos**:
- **Posições 1-3**: Código Sequencial (3 dígitos)
- **Posições 4-14**: CPF (11 dígitos, sem formatação)
- **Posições 15-17**: Código Adicional (3 dígitos)
- **Posições 18-20**: UF (2 caracteres)
- **Posições 21-70**: Nome Completo (50 caracteres)
- **Posições 71-81**: RG (11 dígitos)
- **Posições 82-92**: Código Bancário (11 dígitos)
- **Posições 93-103**: Conta Bancária (11 dígitos)
- **Posições 104-114**: Valor Contribuição em centavos (11 dígitos)
- **Posições 115-122**: Data Última Contribuição DDMMAAAA (8 dígitos)
- **Posições 123-130**: Status do associado (8 caracteres)
- **Posições 131-150**: Código Identificação único (20 caracteres)
- **Posições 151-158**: Flags de controle (8 caracteres)

**Layout do Arquivo de Associados Aposentados**:
- **Posições 1-3**: Código Sequencial "262" (identificador de aposentados)
- **Posições 4-14**: CPF (11 dígitos, sem formatação)
- **Posições 15-25**: Código Interno do sistema (11 dígitos)
- **Posições 26-28**: Código Categoria (021=Aposentado Comum, 054=Aposentado Especial)
- **Posições 29-30**: UF "PR" (Paraná)
- **Posições 31-80**: Nome Completo (50 caracteres)
- **Posições 81-91**: RG (11 dígitos)
- **Posições 92-102**: Código Bancário (11 dígitos)
- **Posições 103-113**: Conta Bancária (11 dígitos)
- **Posições 114-124**: Valor Benefício em centavos (11 dígitos)
- **Posições 125-130**: Código do tipo de benefício (6 dígitos)
- **Posições 131-141**: Status "NAO ESTAVEL" (11 caracteres)
- **Posições 142-161**: Código Identificação "Y00-XXXXXZ" (20 caracteres)
- **Posições 162-168**: Flags específicas de aposentados (7 caracteres)

**Exemplo de Registro de Aposentado**:
- Registro completo com 168 caracteres contendo todos os campos do layout

**Detalhamento dos Campos Específicos para Aposentados**:
- **Posição 1-3**: Sempre "262" para identificar registros de aposentados
- **Posição 26-28**: Categoria (021 = Aposentado Comum, 054 = Aposentado Especial)
- **Posição 29-30**: Estado (PR = Paraná)
- **Posição 114-124**: Valor do benefício de aposentadoria em centavos
- **Posição 125-130**: Código do benefício (153808, 153079)
- **Posição 131-141**: Status "NAO ESTAVEL" para aposentados
- **Posição 142-161**: Código de identificação único no formato Y00-XXXXXZ
- **Posição 162-168**: Flags específicas (2100000)

**Processamento Específico para Aposentados**:

**Validações Especiais**:
- **Código Sequencial**: Validação obrigatória do código "262" para aposentados
- **Categoria de Aposentadoria**: Verificação dos códigos 021 (Comum) e 054 (Especial)
- **Status Fixo**: Validação do status "NAO ESTAVEL" para todos os aposentados
- **Formato de Identificação**: Validação do padrão Y00-XXXXXZ nos códigos
- **Valores de Benefício**: Verificação de consistência dos valores em centavos
- **Códigos de Benefício**: Validação dos códigos 153808 e 153079

**Tratamento Diferenciado**:
- **Mensalidade Mantida**: Aposentados continuam pagando mensalidade do sindicato
- **Benefícios Mantidos**: Acesso completo aos convênios e serviços
- **Carteirinha Especial**: Identificação visual diferenciada para aposentados
- **Relatórios Separados**: Estatísticas específicas para aposentados
- **Comunicação Direcionada**: Mensagens específicas para este grupo
- **Direitos Preservados**: Manutenção de todos os direitos sindicais

**Cálculos de Benefícios**:
- **Valor do Benefício**: Processamento do valor em centavos (posição 114-124)
- **Tipo de Aposentadoria**: Diferenciação entre comum (021) e especial (054)
- **Histórico de Benefícios**: Registro de alterações nos valores
- **Reajustes Automáticos**: Sistema de atualização de valores
- **Relatórios Previdenciários**: Documentos específicos para aposentados

**Sistema de Identificação e Vinculação**:
- **Matching por CPF**: Identificação automática de usuários cadastrados que são associados
- **Criação de Vínculo**: Associação automática entre registro de usuário e dados de associado
- **Atualização de Status**: Sincronização do status de associação no perfil do usuário
- **Histórico de Vinculações**: Log de todas as associações realizadas
- **Resolução de Conflitos**: Tratamento de CPFs duplicados ou inconsistências
- **Identificação de Aposentados**: Marcação automática de usuários aposentados
- **Migração de Status**: Processo de transição de ativo para aposentado

**Histórico de Pagamentos**:
- **Registro de Contribuições**: Histórico completo de pagamentos do associado
- **Status de Adimplência**: Controle de situação financeira (em dia/inadimplente)
- **Cálculo de Débitos**: Apuração automática de valores em atraso
- **Relatórios Financeiros**: Dashboards com situação financeira dos associados
- **Notificações de Cobrança**: Sistema automatizado de lembretes de pagamento
- **Integração Bancária**: Conciliação automática com extratos bancários

**Estatísticas e Métricas do Associado**:
- **Perfil de Engajamento**: Análise de participação em votações, eventos e atividades
- **Histórico de Atividades**: Timeline completa de interações do associado
- **Uso de Convênios**: Estatísticas de utilização de benefícios
- **Participação em Votações**: Percentual de participação e histórico de votos
- **Acesso ao App**: Frequência de uso e funcionalidades mais utilizadas
- **Score de Fidelidade**: Pontuação baseada em engajamento e adimplência

**Métricas Específicas para Aposentados**:
- **Tempo de Aposentadoria**: Cálculo do período desde a aposentadoria
- **Histórico de Benefícios**: Evolução dos valores de benefícios recebidos
- **Participação Pós-Aposentadoria**: Engajamento após a aposentadoria
- **Uso de Serviços**: Utilização de convênios e benefícios específicos
- **Comparativo Geracional**: Análise por faixa etária e tempo de aposentadoria
- **Satisfação**: Pesquisas específicas para aposentados
- **Atividades Sociais**: Participação em eventos e atividades do sindicato
- **Suporte Utilizado**: Uso de canais de atendimento e suporte
- **Benefícios Previdenciários**: Acompanhamento de questões previdenciárias

**Módulo de Comparação Usuário vs Associado**:

**Dashboard de Conversão**:
- **Taxa de Conversão**: Percentual de usuários que se tornaram associados
- **Funil de Conversão**: Visualização do processo de associação
- **Tempo Médio de Conversão**: Análise temporal do processo
- **Abandono de Processo**: Identificação de pontos de desistência
- **Campanhas de Conversão**: Efetividade de ações promocionais

**Métricas de Engajamento**:
- **Usuários Ativos**: Comparação entre usuários e associados ativos
- **Retenção**: Taxa de retenção de usuários vs associados
- **Lifetime Value**: Valor médio gerado por usuário/associado
- **Churn Rate**: Taxa de cancelamento/desistência
- **Net Promoter Score**: Satisfação e recomendação

**Análise Comportamental**:
- **Padrões de Uso**: Diferenças de comportamento entre grupos
- **Funcionalidades Preferidas**: Features mais utilizadas por cada grupo
- **Sazonalidade**: Variações de engajamento ao longo do tempo
- **Segmentação**: Perfis demográficos e comportamentais
- **Jornada do Usuário**: Mapeamento completo da experiência

**Relatórios e Dashboards**:

**Dashboard Executivo**:
- **KPIs Principais**: Métricas-chave em tempo real
- **Gráficos Interativos**: Visualizações dinâmicas com drill-down
- **Comparativos Temporais**: Evolução histórica dos indicadores
- **Alertas Automáticos**: Notificações sobre mudanças significativas
- **Exportação de Dados**: Relatórios em PDF, Excel e CSV

**Relatórios Detalhados**:
- **Relatório de Associados**: Lista completa com filtros avançados
- **Relatório Financeiro**: Situação de pagamentos e inadimplência
- **Relatório de Engajamento**: Análise detalhada de participação
- **Relatório de Conversão**: Métricas de transformação usuário→associado
- **Relatório de Auditoria**: Log completo de operações e alterações

**Relatórios Específicos para Aposentados**:
- **Relatório de Aposentados**: Lista completa com categorias e benefícios
- **Relatório Previdenciário**: Análise de benefícios e reajustes
- **Relatório de Transição**: Acompanhamento de migração ativo→aposentado
- **Relatório Comparativo**: Análise entre associados ativos e aposentados
- **Relatório de Satisfação**: Pesquisas e feedback específicos
- **Relatório de Utilização**: Uso de convênios e serviços por aposentados
- **Relatório Demográfico**: Perfil etário e regional dos aposentados
- **Relatório de Benefícios**: Histórico e evolução dos valores pagos

**Processamento em Background**:

**Sistema de Filas (Laravel Queue)**:
- **Job de Parser**: Processamento assíncrono do arquivo de associados
- **Job de Matching**: Identificação e vinculação de usuários
- **Job de Cálculos**: Atualização de estatísticas e métricas
- **Job de Notificações**: Envio de alertas e comunicações
- **Job de Backup**: Rotinas automáticas de backup

**Jobs Específicos para Aposentados**:
- **Job de Parser de Aposentados**: Processamento específico para layout de aposentados
- **Job de Validação de Benefícios**: Verificação de valores e códigos de benefício
- **Job de Migração de Status**: Transição automática de ativo para aposentado
- **Job de Cálculo de Reajustes**: Atualização automática de valores de benefícios
- **Job de Relatórios Previdenciários**: Geração de documentos específicos
- **Job de Sincronização INSS**: Integração com dados previdenciários
- **Job de Notificação de Aposentados**: Comunicações específicas para este grupo

**Monitoramento de Jobs**:
- **Status de Processamento**: Acompanhamento em tempo real
- **Log de Erros**: Registro detalhado de falhas
- **Retry Automático**: Reprocessamento de jobs falhados
- **Alertas de Falha**: Notificações para administradores
- **Métricas de Performance**: Tempo de processamento e throughput

**Notificações e Comunicação**:

**Sistema de Toast Notifications**:
- **Notificação de Upload**: Confirmação de recebimento do arquivo
- **Progresso de Processamento**: Atualizações durante o parser
- **Conclusão de Processamento**: Resumo final com estatísticas
- **Alertas de Erro**: Notificações de problemas encontrados
- **Sugestões de Ação**: Orientações para resolução de pendências

**Comunicação com Associados**:
- **Email Automático**: Boas-vindas para novos associados identificados
- **SMS de Confirmação**: Validação de dados via mensagem
- **Push Notifications**: Alertas no app mobile
- **Newsletter**: Comunicação periódica com associados
- **Campanhas Segmentadas**: Comunicação direcionada por perfil

**Segurança e Compliance**:

**Proteção de Dados (LGPD)**:
- **Criptografia de CPF**: Proteção de dados sensíveis
- **Anonimização**: Opção de anonimizar dados para relatórios
- **Consentimento**: Gestão de permissões de uso de dados
- **Auditoria de Acesso**: Log de consultas a dados pessoais
- **Direito ao Esquecimento**: Processo de exclusão de dados

**Controle de Acesso**:
- **Permissões Granulares**: Controle detalhado de acesso por funcionalidade
- **Auditoria de Ações**: Log completo de operações realizadas
- **Sessões Seguras**: Controle de tempo e local de acesso
- **Autenticação Multifator**: Segurança adicional para operações sensíveis
- **Backup Criptografado**: Proteção de dados em backups

**Integração com Outros Módulos**:

**Sistema de Votações**:
- **Elegibilidade Automática**: Verificação de direito a voto baseada em associação
- **Peso de Voto**: Diferenciação por categoria de associado
- **Histórico de Participação**: Integração com métricas de engajamento
- **Votação de Aposentados**: Direitos específicos para aposentados
- **Quórum Diferenciado**: Cálculo separado para ativos e aposentados

**Sistema de Convênios**:
- **Benefícios Exclusivos**: Convênios disponíveis apenas para associados
- **Desconto Diferenciado**: Valores especiais por categoria
- **Uso Prioritário**: Preferência para associados em dia
- **Convênios para Aposentados**: Benefícios específicos para terceira idade
- **Desconto Aposentado**: Valores especiais para aposentados
- **Prioridade de Atendimento**: Atendimento preferencial para aposentados

**Sistema Financeiro**:
- **Cobrança Automática**: Integração com sistema de pagamentos
- **Conciliação Bancária**: Matching automático de pagamentos
- **Relatórios Fiscais**: Documentos para prestação de contas
- **Mensalidade de Aposentados**: Cobrança e controle de mensalidades específicas
- **Controle de Benefícios**: Gestão de pagamentos de benefícios
- **Relatórios Previdenciários**: Documentos específicos para aposentados
- **Integração INSS**: Sincronização com dados previdenciários
- **Histórico de Pagamentos**: Controle completo de mensalidades pagas
- **Cobrança Diferenciada**: Valores e prazos específicos para aposentados

**Sistema de Comunicação**:
- **Canais Específicos**: Comunicação direcionada para aposentados
- **Newsletter Aposentados**: Conteúdo específico para terceira idade
- **Eventos Exclusivos**: Atividades voltadas para aposentados
- **Suporte Prioritário**: Atendimento preferencial para aposentados
- **Campanhas Segmentadas**: Marketing direcionado por faixa etária

#### Configuração de Build e Deploy

**app.json (Expo Configuration)**:
- **Nome**: "Sindicato App" com slug "sindicato-app"
- **Versão**: 1.0.0 com orientação portrait
- **Ícones**: Configuração de ícone e splash screen
- **iOS**: Suporte a tablet, bundle identifier, Face ID usage description
- **Android**: Configurações específicas para Android
- **Plugins**: Expo plugins necessários (notifications, biometrics, etc.)
- **Permissions**: Permissões para câmera, notificações, biometria

**eas.json (EAS Build Configuration)**:
- **CLI Version**: >= 3.0.0
- **Build Profiles**: development, preview, production
- **Development**: Development client com distribuição interna
- **Preview**: Build de teste com distribuição interna
- **Production**: Build de produção para stores
- **Environment Variables**: URLs de API por ambiente
- **Submit**: Configuração para envio às stores

#### Integração com Backend Laravel

**Endpoints Específicos para Mobile**:
- **Autenticação**: /mobile/auth/login, /mobile/auth/register, /mobile/auth/biometric-setup
- **Usuário**: /mobile/user/profile, /mobile/user/digital-card, /mobile/user/update-push-token
- **Convênios**: /mobile/convenios (GET), /mobile/convenios/{id} (GET), /mobile/convenios/{id}/generate-qr (POST)
- **Notícias**: /mobile/news (GET), /mobile/news/{id} (GET)
- **Votações**: /mobile/votings/active (GET), /mobile/votings/{id}/vote (POST), /mobile/votings/{id}/results (GET)
- **Middleware**: auth:sanctum para rotas protegidas
- **Prefixo**: /mobile para todas as rotas mobile

**Controller para Carteirinha Digital**:
- **MobileUserController**: Controller específico para funcionalidades mobile
- **digitalCard()**: Método que retorna dados da carteirinha digital
- **Dados Retornados**: ID, nome, número de matrícula, avatar, status, validade
- **Autenticação**: Requer token válido via Sanctum
- **Response**: JSON com dados formatados para o app mobile
                'institution' => $user->institution?->name,
                'department' => $user->department?->name,
            ],
            'qrSecret' => config('app.qr_secret'),
        ]);
    }
    
    public function updatePushToken(Request $request)
    {
        $request->validate([
            'push_token' => 'required|string',
            'device_type' => 'required|in:ios,android',
        ]);
        
        $user = $request->user();
        $user->update([
            'push_token' => $request->push_token,
            'device_type' => $request->device_type,
        ]);
        
        return response()->json(['message' => 'Push token atualizado com sucesso']);
    }
}
```

#### Testes e Qualidade

**Configuração de Testes**:
```javascript
// __tests__/components/DigitalCard.test.js
import React from 'react';
import { render } from '@testing-library/react-native';
import { Provider } from 'react-redux';
import { store } from '../src/store';
import DigitalCard from '../src/screens/identity/DigitalCard';

const renderWithProvider = (component) => {
  return render(
    <Provider store={store}>
      {component}
    </Provider>
  );
};

describe('DigitalCard', () => {
  it('renders user information correctly', () => {
    const { getByText } = renderWithProvider(<DigitalCard />);
    expect(getByText('CARTEIRINHA DIGITAL')).toBeTruthy();
  });
  
  it('generates QR code with valid data', () => {
    // Test QR code generation
  });
});
```

**Scripts de Build**:
```json
// package.json
{
  "scripts": {
    "start": "expo start",
    "android": "expo start --android",
    "ios": "expo start --ios",
    "web": "expo start --web",
    "test": "jest",
    "test:watch": "jest --watch",
    "lint": "eslint . --ext .js,.jsx,.ts,.tsx",
    "build:android": "eas build --platform android",
    "build:ios": "eas build --platform ios",
    "submit:android": "eas submit --platform android",
    "submit:ios": "eas submit --platform ios"
  }
}
```

### Sistema de Logs Centralizados e Telemetria

#### Arquitetura de Observabilidade
- **Stack de Observabilidade**: ELK Stack + Grafana + Prometheus
- **Containerização**: Todos os serviços de observabilidade dockerizados
- **Coleta Distribuída**: Logs, métricas e traces centralizados
- **Retenção de Dados**: Políticas configuráveis por ambiente
- **Alta Disponibilidade**: Cluster resiliente com failover automático

#### Sistema de Logs (ELK Stack)

**Elasticsearch**:
- **Cluster**: Multi-node para alta disponibilidade
- **Índices**: Separação por aplicação, ambiente e data
- **Mapping**: Schema otimizado para logs estruturados
- **Retenção**: Políticas automáticas de lifecycle management
- **Backup**: Snapshots automáticos para recuperação

**Logstash**:
- **Pipelines**: Processamento paralelo de logs
- **Filtros**: Parsing, enrichment e transformação
- **Inputs**: Múltiplas fontes (filebeat, syslog, http)
- **Outputs**: Elasticsearch, alertas e arquivamento
- **Grok Patterns**: Parsing customizado para logs da aplicação

**Kibana**:
- **Dashboards**: Visualizações interativas de logs
- **Discover**: Busca e análise exploratória
- **Alerting**: Alertas baseados em queries
- **Canvas**: Relatórios visuais customizados
- **Machine Learning**: Detecção de anomalias automática

#### Sistema de Métricas (Prometheus + Grafana)

**Prometheus**:
- **Coleta**: Pull-based metrics collection
- **Targets**: Auto-discovery de serviços
- **Storage**: Time-series database otimizado
- **PromQL**: Query language para análise de métricas
- **Alertmanager**: Sistema de alertas integrado

**Grafana**:
- **Dashboards**: Visualizações em tempo real
- **Data Sources**: Prometheus, Elasticsearch, PostgreSQL
- **Alerting**: Alertas visuais e notificações
- **Templating**: Dashboards dinâmicos e reutilizáveis
- **Plugins**: Extensões para funcionalidades específicas

#### Coleta de Logs da Aplicação

**Laravel Logging**:
- **Channels**: Múltiplos canais de log configuráveis
- **Formatters**: JSON structured logging
- **Levels**: DEBUG, INFO, WARNING, ERROR, CRITICAL
- **Context**: Metadata automático (user_id, request_id, session_id)
- **Performance**: Logging assíncrono para alta performance

**Tipos de Logs Coletados**:
- **Application Logs**: Eventos da aplicação Laravel
- **Access Logs**: Nginx/Apache access logs
- **Error Logs**: PHP errors e exceptions
- **Database Logs**: PostgreSQL query logs
- **Queue Logs**: Jobs e workers do sistema de filas
- **Security Logs**: Tentativas de login, ações sensíveis
- **Audit Logs**: Trilha de auditoria completa
- **Performance Logs**: Métricas de response time e throughput

#### Métricas de Sistema e Aplicação

**Métricas de Infraestrutura**:
- **Sistema**: CPU, memória, disco, rede
- **Containers**: Docker metrics (CPU, memory, I/O)
- **Database**: PostgreSQL performance metrics
- **Cache**: Redis metrics (hit rate, memory usage)
- **Web Server**: Nginx/Apache metrics

**Métricas de Aplicação**:
- **HTTP Requests**: Rate, latency, status codes
- **Database Queries**: Execution time, query count
- **Queue Jobs**: Processing time, success/failure rate
- **User Sessions**: Active users, session duration
- **Business Metrics**: Registros, logins, ações por módulo
- **Error Rates**: Exception rate por endpoint
- **Cache Performance**: Hit/miss ratio, response times

#### Dashboards Grafana

**Dashboard de Infraestrutura**:
- **System Overview**: CPU, RAM, Disk, Network
- **Container Metrics**: Docker containers health
- **Database Performance**: PostgreSQL metrics
- **Cache Performance**: Redis metrics
- **Network Traffic**: Bandwidth e latência

**Dashboard de Aplicação**:
- **HTTP Traffic**: Requests/sec, response times
- **Error Monitoring**: Error rates e stack traces
- **User Activity**: Active users, page views
- **Queue Monitoring**: Job processing metrics
- **Security Events**: Login attempts, security alerts

**Dashboard de Negócio**:
- **User Registration**: Novos usuários por período
- **Module Usage**: Utilização por módulo do sistema
- **Conversion Metrics**: Taxa de conversão usuário→associado
- **Engagement**: Tempo de sessão, páginas por visita
- **Content Performance**: Visualizações de notícias

**Dashboard de Performance**:
- **Response Times**: P50, P95, P99 latencies
- **Throughput**: Requests per second
- **Database Performance**: Query execution times
- **Cache Efficiency**: Hit rates e performance
- **Resource Utilization**: CPU, memory trends

#### Sistema de Alertas

**Alertas de Infraestrutura**:
- **High CPU Usage**: > 80% por 5 minutos
- **Memory Usage**: > 85% por 3 minutos
- **Disk Space**: > 90% utilização
- **Database Connections**: Pool exhaustion
- **Service Down**: Health check failures

**Alertas de Aplicação**:
- **High Error Rate**: > 5% em 5 minutos
- **Slow Response Time**: P95 > 2s por 3 minutos
- **Queue Backlog**: > 1000 jobs pendentes
- **Failed Jobs**: > 10% failure rate
- **Security Events**: Multiple failed logins

**Alertas de Negócio**:
- **Registration Drop**: < 50% da média diária
- **Low Engagement**: Sessões < 2 minutos
- **System Abuse**: Rate limiting triggers
- **Data Anomalies**: Unusual patterns detected

**Canais de Notificação**:
- **Email**: Alertas críticos para administradores
- **Slack**: Notificações em tempo real
- **SMS**: Alertas críticos de infraestrutura
- **Webhook**: Integração com sistemas externos
- **Dashboard**: Alertas visuais no Grafana

#### Configuração e Deployment

**Docker Compose**:
- **Elasticsearch Cluster**: 3 nodes para HA
- **Logstash**: Pipeline de processamento
- **Kibana**: Interface web para logs
- **Prometheus**: Coleta de métricas
- **Grafana**: Dashboards e alertas
- **Filebeat**: Coleta de logs dos containers
- **Node Exporter**: Métricas do sistema host

**Configuração de Rede**:
- **Reverse Proxy**: Nginx para acesso seguro
- **SSL/TLS**: Certificados para todos os serviços
- **Authentication**: LDAP/OAuth integration
- **Network Isolation**: Containers em rede dedicada

**Backup e Recuperação**:
- **Elasticsearch Snapshots**: Backup automático diário
- **Grafana Dashboards**: Export/import automático
- **Prometheus Data**: Backup de métricas históricas
- **Configuration Backup**: Versionamento de configs

#### Segurança e Compliance

**Controle de Acesso**:
- **RBAC**: Role-based access control
- **Authentication**: SSO integration
- **Authorization**: Permissões granulares
- **Audit Trail**: Log de acessos aos dashboards

**Proteção de Dados**:
- **Encryption**: Dados em trânsito e repouso
- **Data Masking**: Ocultação de dados sensíveis
- **Retention Policies**: Conformidade com LGPD
- **Access Logs**: Auditoria de acesso aos logs

**Compliance**:
- **LGPD**: Tratamento adequado de dados pessoais
- **Audit Requirements**: Trilha completa de auditoria
- **Data Retention**: Políticas de retenção configuráveis
- **Privacy Controls**: Anonimização de dados sensíveis

#### Monitoramento de Performance

**Application Performance Monitoring (APM)**:
- **Distributed Tracing**: Rastreamento de requests
- **Code Profiling**: Análise de performance do código
- **Database Monitoring**: Query performance analysis
- **Memory Profiling**: Detecção de memory leaks
- **Error Tracking**: Stack traces detalhados

**Real User Monitoring (RUM)**:
- **Frontend Performance**: Page load times
- **User Experience**: Core Web Vitals
- **Browser Metrics**: Compatibility e performance
- **Mobile Performance**: Responsive design metrics
- **User Journey**: Análise de fluxo de usuários

**Synthetic Monitoring**:
- **Health Checks**: Monitoramento proativo
- **API Testing**: Testes automatizados de endpoints
- **User Journey Testing**: Simulação de fluxos críticos
- **Performance Baselines**: Comparação histórica
- **Availability Monitoring**: Uptime tracking

## Segurança e Autenticação

### Sistema de Autenticação
- **JWT (JSON Web Tokens)** para autenticação stateless
- **Validação de email** obrigatória no registro
- **Sessão persistente** via cookies seguros
- **CSRF Protection** com tokens validados
- **Chaves RSA**: Par de chaves pública/privada para JWT e CSRF
  - Validação automática na inicialização do servidor
  - Auto-geração de chaves RSA caso inválidas ou inexistentes
- **Sistema de Blacklist JWT**:
  - Revogação automática de todos os tokens anteriores ao logout
  - Invalidação forçada de sessões ativas
  - Controle de versão de tokens por usuário
  - Armazenamento de tokens revogados em cache (Redis)
  - Verificação de blacklist em cada requisição autenticada
  - Limpeza automática de tokens expirados da blacklist
  - Logout forçado em caso de atividade suspeita
  - Obrigatoriedade de novo login após logout

### Revogação Automática de JWT em Caso de Erro

**Política de Revogação Automática**:
- **Revogação Imediata**: Qualquer erro relacionado ao JWT resulta em revogação automática do token
- **Limpeza de Cookies**: Remoção instantânea do JWT dos cookies do navegador
- **Reset de Sessão**: Limpeza completa do estado de autenticação no frontend
- **Redirecionamento Forçado**: Usuário é automaticamente enviado para tela de login
- **Mensagem de Erro**: Exibição de mensagem informativa sobre a necessidade de novo login

**Cenários de Revogação Automática**:
- **Token Expirado**: JWT ultrapassou seu tempo de vida útil
- **Token Inválido**: Assinatura do token não pode ser verificada
- **Token Malformado**: Formato do JWT está corrompido ou incompleto
- **Token na Blacklist**: JWT foi previamente revogado e está na lista negra
- **Versão Incompatível**: Token possui versão diferente da atual do usuário
- **Permissões Insuficientes**: Tentativa de acesso a recursos não autorizados
- **Usuário Inativo**: Conta do usuário foi desativada ou suspensa
- **Alteração de Senha**: Senha foi alterada, invalidando todos os tokens
- **Múltiplas Sessões**: Detecção de logins simultâneos suspeitos
- **Atividade Anômala**: Padrões de uso que indicam comprometimento

**Fluxo de Revogação**:
1. **Detecção**: Sistema identifica erro relacionado ao JWT
2. **Validação**: Verificação se o erro requer revogação do token
3. **Blacklist**: Adição do JWT à lista negra no Redis
4. **Invalidação**: Marcação do token como inválido no servidor
5. **Limpeza Frontend**: Remoção do token dos cookies e localStorage
6. **Reset Estado**: Limpeza do estado de autenticação (Vuex/Redux)
7. **Redirecionamento**: Envio para tela de login com mensagem apropriada
8. **Auditoria**: Registro do evento para análise de segurança

**Implementação de Segurança**:
- **Middleware de Verificação**: Interceptação de todas as requisições autenticadas
- **Rate Limiting**: Limitação de tentativas de login após revogação
- **Notificação de Segurança**: Alertas para administradores em casos suspeitos
- **Log Detalhado**: Registro completo de eventos de revogação
- **Análise de Padrões**: Detecção automática de atividades maliciosas
- **Recuperação Segura**: Processo seguro para restabelecimento de sessão

### Proteções Anti-Abuse
- **hCaptcha** na validação de registro
- **Rate Limiting**:
  - Recuperação de senha
  - Tentativas de login
  - Envio de emails
- **Proteção contra ataques de força bruta**

### Sistema de Emails
- **Processamento assíncrono**: Todos os emails são enviados em segundo plano
- **Sistema de filas (Queues)**:
  - Fila de emails de alta prioridade (recuperação de senha, verificação)
  - Fila de emails de média prioridade (notificações importantes)
  - Fila de emails de baixa prioridade (newsletters, relatórios)
  - Processamento paralelo com workers dedicados
- **Provedores de email configuráveis**:
  - **MailHog**: Servidor de email para desenvolvimento e testes
    - Interface web para visualização de emails enviados
    - Captura todos os emails sem envio real
    - Ideal para desenvolvimento e debugging
  - **Google SMTP**: Servidor de produção via Gmail
    - Configuração via credenciais OAuth2 ou App Password
    - Alta confiabilidade e deliverability
    - Suporte a autenticação de dois fatores
- **Configuração dinâmica via Dashboard Admin**:
  - Seleção do provedor ativo (MailHog/Google SMTP)
  - Interface para configurar credenciais SMTP
  - Teste de conectividade em tempo real
  - Histórico de alterações de configuração
  - Backup automático de configurações
- **Tipos de email automatizados**:
  - Verificação de email no registro
  - Recuperação de senha
  - Notificações de votações
  - Alertas de convênios
  - Confirmações de ações importantes
  - Relatórios periódicos
- **Controles de entrega**:
  - Retry automático em caso de falha
  - Blacklist de emails inválidos
  - Logs detalhados de envio
  - Monitoramento de taxa de entrega
  - Throttling para evitar spam

## Sistema de Usuários e Permissões

### Hierarquia de Níveis de Acesso
1. **Admin** (Nível mais alto)
   - Gerencia todos os usuários do sistema
   - Pode promover usuários a fundadores
   - Acesso total ao sistema

2. **Fundador**
   - Gerencia associados e colaboradores
   - Pode rebaixar associados para usuários
   - Dashboard com painel de gestão de permissões
   - Painel de gerenciamento de associados
   - **Gestão organizacional**:
     - Adicionar/editar/excluir instituições
     - Adicionar/editar/excluir endereços
     - Adicionar/editar/excluir departamentos
     - Visualizar estrutura hierárquica completa
   - **Sistema de votações**:
     - Criar/editar/excluir votações e enquetes
     - Definir elegibilidade por nível de usuário
     - Configurar filtros organizacionais (instituição/endereço/departamento)
     - Acessar relatórios completos de votações
     - Gerenciar configurações avançadas de votação

3. **Colaborador** (com subníveis)
   - **Diretor**: Máximo nível de colaborador
     - Aprovação de convênios criados por parceiros
     - Gestão completa do sistema de convênios
   - **Administrador**: Gestão administrativa
   - **Jornalista**: Gestão de conteúdo e notícias
   - **Atendimento**: Suporte aos associados
   - Painel para gerenciamento de usuários
   - Painel para adição de notícias

4. **Parceiro**
   - Empresas ou entidades parceiras do sindicato
   - Dashboard específico para gestão de convênios
   - **Gestão de convênios**:
     - Criar novos convênios e benefícios
     - Editar convênios próprios (pendentes ou rejeitados)
     - Visualizar histórico de convênios criados
     - Acompanhar status de aprovação
     - Gerenciar dados da empresa parceira
   - **Gestão de colaboradores**:
     - Cadastrar colaboradores próprios (subnível)
     - Gerenciar permissões de acesso
     - Controlar acesso ao sistema de validação
   - **Relatórios de utilização**:
     - Visualizar estatísticas de uso dos convênios
     - Relatórios de associados que utilizaram benefícios
     - Métricas de engajamento

   **4.1. Colaborador de Parceiro** (Subnível)
   - Funcionários ou representantes da empresa parceira
   - Acesso limitado ao sistema do parceiro
   - **Validação de convênios**:
     - Acesso ao módulo validador de QR codes
     - Validar utilização de convênios por associados
     - Registrar confirmação de uso do benefício
     - Visualizar histórico de validações realizadas

5. **Associado**
   - Membro ativo do sindicato
   - Dashboard personalizado
   - Acesso a serviços exclusivos
   - **Utilização de convênios**:
     - Visualizar convênios disponíveis
     - Gerar QR Code para validação de uso
     - Histórico de convênios utilizados

6. **Usuário** (Nível básico)
   - Acesso limitado ao sistema
   - Dashboard básico
   - **Visualização de convênios**:
     - Ver convênios disponíveis (sem poder utilizar)
     - Informações sobre como se tornar associado

### Gestão de Permissões
- **Fundador**: Atribui permissões específicas aos colaboradores
- **Sistema flexível** de permissões por módulo
- **Auditoria** de alterações de permissões

## Perfil de Usuário

### Dados Pessoais
- **Informações básicas**: Nome, email, CPF (obrigatórios)
- **Contatos múltiplos**:
  - Emails adicionais
  - Telefones (múltiplos)
  - Endereços (múltiplos)
- **Dados pessoais**:
  - Sexo/Gênero
  - Data de nascimento
  - **Sistema de Avatar/Foto de perfil**:
    - Upload de imagem com validação de formato (JPG, PNG, GIF)
    - Ferramenta de crop interativa no frontend
    - Pré-visualização em tempo real do crop
    - Redimensionamento automático para 300x300 pixels
    - Processamento em segundo plano no servidor
    - Otimização de qualidade e compressão
    - Fallback para avatar padrão se não houver upload
    - Validação de tamanho máximo de arquivo (ex: 5MB)
- **Dados organizacionais** (opcionais no registro):
  - Instituição
  - Endereço
  - Departamento

### Validações
- **Email único** por usuário principal
- **CPF único** por usuário no sistema
- **Validação de CPF**:
  - Formato válido (XXX.XXX.XXX-XX)
  - Algoritmo de validação de dígitos verificadores
  - Prevenção de CPFs inválidos (000.000.000-00, 111.111.111-11, etc.)
  - Máscara automática no frontend
- **Validação de formato** para todos os campos
- **Verificação de email** obrigatória

## Sistema de Instituições, Endereços e Departamentos

### Estrutura Hierárquica
```
Instituição
├── Endereço 1
│   ├── Departamento 1
│   └── Departamento 2
├── Endereço 2
│   └── Departamento 3
└── ...
```

### 1. Cadastro de Instituições
- **Campos obrigatórios**:
  - Nome longo (único no sistema)
  - Nome curto (único no sistema)
- **Validações**:
  - Unicidade garantida para ambos os nomes
  - Prevenção de duplicatas
- **Interface**:
  - Formulário intuitivo com feedback visual
  - CRUD completo com confirmações de segurança
  - Contadores inteligentes mostrando uso em registros

### 2. Cadastro de Endereços
- **Campos obrigatórios**:
  - Título (único por instituição)
  - Cidade
  - Estado
  - Instituição (relacionamento obrigatório)
- **Validações**:
  - Título único por instituição
  - Validação de relacionamento com instituição
- **Interface**:
  - Seleção dinâmica de instituições via dropdown
  - Feedback visual sobre relacionamentos ativos
- **Relacionamento**: Múltiplos endereços por instituição

### 3. Cadastro de Departamentos
- **Campos obrigatórios**:
  - Nome (único por endereço)
  - Instituição
  - Endereço
- **Validações**:
  - Nome único por endereço
  - Validação em cascata (instituição → endereço → departamento)
- **Interface**:
  - Seleção em cascata inteligente
  - Carregamento dinâmico baseado em seleções
- **Relacionamento**: Departamento pertence a uma instituição e endereço

### 4. Registro de Usuários com Dados Organizacionais
- **Campos obrigatórios**: Nome, email, CPF
- **Campos opcionais**:
  - Instituição
  - Endereço (filtrado pela instituição selecionada)
  - Departamento (filtrado pelo endereço selecionado)
- **Interface**:
  - Seleção em cascata inteligente
  - Filtros dinâmicos baseados em seleções anteriores
  - Possibilidade de deixar campos em branco

### Regras de Negócio

#### Unicidade de Dados
- **Instituição**: Nomes únicos no sistema inteiro
- **Endereço**: Título único por instituição
- **Departamento**: Nome único por endereço

#### Contadores Inteligentes
- Contadores azuis mostrando quantos usuários estão vinculados
- Feedback visual sobre relacionamentos ativos
- Prevenção de exclusão quando há usuários vinculados

#### Sistema de Notificações
- **Sucesso**: Toast verde com mensagens claras
- **Erro**: Toast vermelho com detalhes do problema
- **Validação**: Toast laranja para erros de formulário
- **Confirmação**: Modais de segurança para exclusões

### Interface e Experiência

#### Layout Responsivo Universal
- Remoção de todas as regras @media complexas
- Layout flexível que se adapta naturalmente
- Mesma experiência de 320px a 4K
- Elementos com proporções fixas que escalam proporcionalmente

#### Barra Lateral Retrátil
- **Posição**: Fixa na esquerda
- **Itens**: Home 🏠, Cadastro 📄, Registro 👤
- **Comportamento**: Transições suaves com hover
- **Indicador**: Visual de página atual ativa

#### Cards Hierárquicos
- Visualização em árvore da estrutura organizacional
- Expansão/colapso de níveis
- Indicadores visuais de relacionamentos
- Ações contextuais por nível

## Módulos do Sistema

### 1. Dashboard Personalizado
- **Admin Dashboard**: Gestão completa do sistema
  - **Módulo de Gestão Completa de Usuários**:
    - Interface centralizada para gestão de todos os usuários do sistema
    - **Filtros avançados por nível de usuário**:
      - Admin (visualização e gestão completa)
      - Fundador (promoção/rebaixamento)
      - Colaborador (por subnível: Diretor, Administrador, Jornalista, Atendimento)
      - Parceiro (gestão de empresas parceiras)
      - Colaborador de Parceiro (funcionários de empresas)
      - Associado (membros ativos do sindicato)
      - Usuário (nível básico)
    - **Funcionalidades de gestão**:
      - Busca avançada por nome, email, CPF, nível, instituição
      - Filtros combinados (nível + instituição + status)
      - Visualização em lista e cards com informações detalhadas
      - Ações em lote (ativar/desativar, alterar nível)
      - Histórico de alterações por usuário
      - Exportação de relatórios de usuários
      - Logs de auditoria de todas as ações administrativas
    - **Gestão de permissões globais**:
      - Definir permissões específicas por nível
      - Configurar restrições de acesso por módulo
      - Controle de funcionalidades ativas por tipo de usuário
  - **Módulo de Configuração de Email**:
    - Interface para seleção de provedor de email (MailHog/Google SMTP)
    - Formulário de configuração de credenciais SMTP
    - Teste de conectividade em tempo real
    - Histórico de configurações e alterações
    - Backup e rollback de configurações
    - Logs de auditoria de alterações de email
  - **Módulo de Gestão Administrativa de Conteúdo**:
    - **Controle Total de Notícias**:
      - Visualização de todas as notícias do sistema
      - Aprovação/rejeição de conteúdo de qualquer nível
      - Edição e moderação de conteúdo publicado
      - Controle de categorias e tags globais
      - Gestão de mídia centralizada
    - **Gestão Avançada de Categorias**:
      - CRUD completo de categorias (criar, editar, excluir)
      - Configuração de cores e ícones personalizados
      - Ordenação manual e hierarquia de categorias
      - Ativação/desativação de categorias
      - Migração de notícias entre categorias
      - Estatísticas de uso por categoria
      - Controle de visibilidade pública das categorias
    - **Analytics Administrativas Avançadas**:
      - Dashboard executivo com métricas globais
      - Relatórios de performance por autor e categoria
      - Análise de engajamento por tipo de usuário
      - Métricas de moderação e aprovação
      - Estatísticas de uso do sistema de publicação
      - Relatórios de tendências e sazonalidade
    - **Sistema de Moderação**:
      - Fila de conteúdo pendente de aprovação
      - Histórico completo de moderações
      - Configuração de regras de auto-aprovação
      - Sistema de alertas para conteúdo sensível
      - Logs de auditoria de todas as ações
    - **Configurações Globais de Publicação**:
      - Definir políticas de publicação por nível de usuário
      - Configurar fluxos de aprovação personalizados
      - Gerenciar templates de notícias
      - Controlar funcionalidades de interação (likes, compartilhamentos)
      - Configurar limites de publicação por usuário
- **Fundador Dashboard**: 
  - **Módulo de Gestão de Usuários**:
    - **Gestão de Colaboradores**:
      - Interface para cadastro e edição de colaboradores
      - Definição de subníveis (Diretor, Administrador, Jornalista, Atendimento)
      - Atribuição de permissões específicas por colaborador
      - **Sistema de Delegação**:
        - Delegar gestão de usuários para colaboradores específicos
        - Definir escopo de delegação (quais níveis podem gerenciar)
        - Controle de permissões delegadas (temporárias ou permanentes)
        - Histórico de delegações realizadas
        - Revogação de delegações a qualquer momento
      - Relatórios de atividade por colaborador
      - Controle de acesso por módulo do sistema
    - **Gestão de Associados**:
      - Interface para promoção de usuários para associados
      - Rebaixamento de associados para usuários
      - Visualização de dados completos dos associados
      - Filtros por instituição, endereço e departamento
      - Relatórios de associados ativos/inativos
      - Histórico de alterações de status
      - Gestão de benefícios e convênios por associado
    - **Gestão de Conveniados (Parceiros)**:
      - Interface para cadastro de empresas parceiras
      - Aprovação/rejeição de solicitações de parceria
      - Gestão de contratos e acordos comerciais
      - Definição de tipos de convênios permitidos
      - Controle de colaboradores por parceiro
      - Relatórios financeiros e de performance
      - Histórico de relacionamento comercial
    - **Filtros e Relatórios Unificados**:
      - Busca unificada por todos os tipos de usuário (nome, email, CPF)
      - Filtros combinados (nível + instituição + status + data)
      - Exportação de relatórios personalizados
      - Dashboard com métricas de usuários por categoria
      - Gráficos de crescimento e engajamento
  - **Módulo de Gestão Organizacional**:
    - Interface para cadastro de instituições
    - Interface para cadastro de endereços
    - Interface para cadastro de departamentos
    - Visualização hierárquica da estrutura organizacional
    - Relatórios de uso e vinculação de usuários
  - **Módulo de Gestão de Votações**:
    - Interface para criação de votações e enquetes
    - Configuração de elegibilidade e filtros organizacionais
    - Monitoramento de votações ativas em tempo real
    - Relatórios e análises de participação
    - Histórico completo de votações realizadas
    - **Página de Estatísticas de Votação**:
      - Dashboard analítico com métricas detalhadas em tempo real
      - Gráficos interativos de participação e resultados por votação
      - Análise demográfica completa dos participantes
      - Métricas de performance e engajamento por período
      - Heatmap de participação por horário e estrutura organizacional
      - Relatórios comparativos entre diferentes votações
      - Análise de tendências e padrões de comportamento
      - Exportação de dados estatísticos em múltiplos formatos
      - Filtros avançados para segmentação personalizada
  - **Módulo de Analytics de Convênios**:
    - Dashboard com estatísticas completas de uso de convênios
    - Métricas de performance por parceiro
    - Relatórios de utilização por período
    - Análise de ROI dos convênios para o sindicato
    - Gráficos de tendências e sazonalidade
    - Ranking de convênios mais utilizados
    - Dados demográficos dos usuários de convênios
  - **Módulo de Convites**:
    - **Convite Individual**:
      - Interface para convidar um único usuário
      - Formulário com campos: nome e email do convidado
      - Personalização da mensagem de convite
      - Pré-visualização do email antes do envio
      - Histórico de convites individuais enviados
    - **Convite em Lote (CSV)**:
      - Upload de arquivo CSV com lista de usuários
      - Formato padrão: nome, email (colunas obrigatórias)
      - Validação automática do arquivo CSV
      - Pré-visualização da lista antes do envio
      - Processamento em lote com barra de progresso
      - Relatório de sucessos e falhas no processamento
    - **Sistema de Filas de Email**:
      - Processamento assíncrono de todos os convites
      - Fila de alta prioridade para convites do fundador
      - Retry automático em caso de falha de envio
      - Status de entrega em tempo real
      - Logs detalhados de envio por convite
    - **Gestão de Convites**:
      - Dashboard com todos os convites enviados
      - Filtros por status (enviado, entregue, aceito, expirado)
      - Reenvio de convites não entregues
      - Cancelamento de convites pendentes
      - Configuração de prazo de validade dos convites
  - **Módulo de Estatísticas e Engajamento**:
    - **Analytics de Usuários**:
      - Dashboard com métricas de crescimento de usuários
      - Gráficos de novos registros por período
      - Taxa de conversão de usuários para associados
      - Análise de retenção e churn rate
      - Segmentação por dados organizacionais
      - Heatmap de atividade por horário e dia
    - **Analytics de Associados**:
      - Métricas específicas de engajamento de associados
      - Utilização de benefícios e convênios
      - Participação em votações e enquetes
      - Tempo médio de sessão e frequência de acesso
      - Análise de comportamento e preferências
    - **Estatísticas de Convites por Terceiros**:
      - Dashboard de convites enviados por associados e usuários
      - Ranking de usuários que mais enviam convites
      - Taxa de conversão de convites por usuário
      - Análise de rede de indicações
      - Métricas de viralidade e crescimento orgânico
      - Relatórios de performance de programa de indicações
    - **Relatórios Executivos**:
      - Relatórios mensais e anuais de crescimento
      - Análise comparativa de períodos
      - Projeções de crescimento baseadas em tendências
      - Exportação de dados para análise externa
      - Dashboard executivo com KPIs principais
- **Colaborador Dashboard**: 
  - **Módulo de Gestão de Usuários** (Delegado pelo Fundador):
    - **Permissões Delegadas**:
      - Gestão limitada conforme escopo definido pelo fundador
      - Acesso apenas aos níveis de usuário autorizados
      - Interface adaptada às permissões concedidas
      - Indicador visual das permissões ativas
    - **Gestão de Usuários Básicos**:
      - Promoção de usuários para associados (se autorizado)
      - Edição de dados pessoais e organizacionais
      - Ativação/desativação de contas
      - Resetar senhas de usuários
    - **Gestão de Associados** (se delegado):
      - Visualização de dados completos dos associados
      - Relatórios de atividade e engajamento
      - Gestão de benefícios individuais
      - Histórico de utilização de convênios
    - **Relatórios e Auditoria**:
      - Relatórios das ações realizadas
      - Histórico de alterações feitas
      - Logs de acesso ao módulo de gestão
      - Métricas de usuários gerenciados
    - **Limitações e Controles**:
      - Não pode alterar permissões de outros colaboradores
      - Não pode promover usuários a colaboradores
      - Ações auditadas e reportadas ao fundador
      - Tempo limite para permissões temporárias
  - **Módulo de Gestão de Conteúdo**:
    - Interface de criação e edição de notícias
    - Sistema de categorias e tags
    - Aprovação de conteúdo por níveis superiores
    - Publicação programada
    - Gestão de mídia (imagens, vídeos)
    - **Gestão de Categorias** (Jornalista/Administrador):
      - Criação e edição de categorias de notícias
      - Configuração de cores e ícones para categorias
      - Ativação/desativação de categorias
      - Visualização de estatísticas por categoria
      - Seleção obrigatória de categoria na publicação
      - Preview de como a categoria aparece publicamente
    - **Sistema de Programação Avançada**:
      - Calendário visual de publicações e remoções
      - Agendamento automático com fuso horário
      - Gestão de status (rascunho, agendado, publicado, arquivado)
      - Notificações de confirmação e lembretes
    - **Dashboard de Analytics de Notícias**:
      - Estatísticas de visualizações e cliques em tempo real
      - Métricas de engajamento e tempo de leitura
      - Analytics demográficas por nível de usuário
      - Relatórios de performance e tendências
      - Ranking de notícias mais populares
    - **Sistema de Interação Social**:
      - Gestão de likes e comentários
      - Monitoramento de compartilhamentos (email, WhatsApp, link)
      - Métricas de viralização e alcance
      - Notificações de interações dos usuários
    - **Ferramentas de Otimização**:
      - Editor WYSIWYG com preview em tempo real
      - Configurações de SEO e meta tags
      - Otimização para dispositivos móveis
      - Galeria de mídia integrada com editor de imagens
  - **Módulo de Aprovação de Convênios** (Diretor):
    - Lista de convênios pendentes de aprovação
    - Interface para aprovar/rejeitar convênios
    - Histórico de aprovações realizadas
    - Relatórios de convênios por parceiro
- **Parceiro Dashboard**:
  - **Módulo de Gestão de Convênios**:
    - Interface para criação de novos convênios
    - Edição de convênios próprios (pendentes/rejeitados)
    - Visualização de status de aprovação
    - Upload de materiais promocionais
  - **Módulo de Gestão de Colaboradores**:
    - Cadastro de colaboradores da empresa
    - Definição de permissões de acesso
    - Controle de acesso ao sistema de validação
    - Histórico de atividades dos colaboradores
  - **Módulo de Relatórios**:
    - Estatísticas de uso dos convênios
    - Relatórios de associados que utilizaram
    - Métricas de engajamento e conversão
    - Relatórios de validações por colaborador
  - **Módulo de Perfil da Empresa**:
    - Gestão de dados da empresa parceira
    - Configurações de contato e suporte

- **Colaborador de Parceiro Dashboard**:
  - **Módulo Validador de Convênios**:
    - Interface para escaneamento de QR codes
    - Validação em tempo real da autenticidade
    - Confirmação de utilização do benefício
    - Histórico de validações realizadas
    - Relatórios de atividade diária
- **Associado Dashboard**: 
  - Serviços e informações
  - **Módulo de Convênios**:
    - Catálogo de convênios disponíveis
    - Geração de QR Code para utilização
    - Histórico de convênios utilizados
  - **Módulo de Convites**:
    - **Envio de Convites**:
      - Interface para convidar outras pessoas
      - Formulário simples: nome e email do convidado
      - Mensagem personalizada opcional
      - Limite de convites por período (configurável pelo fundador)
      - Histórico de convites enviados pelo associado
    - **Programa de Indicações**:
      - Dashboard pessoal de indicações realizadas
      - Status dos convites enviados (pendente, aceito, expirado)
      - Contador de pessoas indicadas que se tornaram usuários/associados
      - Sistema de recompensas por indicações bem-sucedidas
      - Ranking pessoal no programa de indicações
- **Usuário Dashboard**: 
  - Informações básicas
  - **Visualização de Convênios**:
    - Catálogo público de convênios
    - Informações sobre benefícios da associação
  - **Módulo de Convites**:
    - **Envio de Convites**:
      - Interface para convidar outras pessoas
      - Formulário simples: nome e email do convidado
      - Mensagem de incentivo à participação no sindicato
      - Limite de convites por período (menor que associados)
      - Histórico de convites enviados pelo usuário
    - **Programa de Indicações**:
      - Dashboard básico de indicações realizadas
      - Status dos convites enviados
      - Contador de pessoas indicadas
      - Incentivo para se tornar associado e ter mais benefícios no programa

### 2. Gestão de Notícias
- **Interface de criação** de notícias
- **Sistema de categorias** e tags
- **Aprovação de conteúdo** por níveis superiores
- **Publicação programada**
- **Gestão de mídia** (imagens, vídeos)
- **Acesso público** para usuários não logados

#### Sistema de Categorias
- **Gestão de Categorias**:
  - Criação, edição e exclusão de categorias
  - Nome único e descrição para cada categoria
  - Cores personalizadas para identificação visual
  - Ícones opcionais para cada categoria
  - Ordenação manual das categorias
  - Status ativo/inativo por categoria
- **Seleção de Categoria na Publicação**:
  - Campo obrigatório de categoria durante criação
  - Seleção única de categoria por notícia
  - Preview visual da categoria selecionada
  - Validação de categoria ativa antes da publicação
- **Sistema de Navegação por Categorias**:
  - Menu de navegação público com todas as categorias ativas
  - Filtro automático de notícias por categoria selecionada
  - Contador de notícias por categoria
  - Breadcrumb de navegação (Home > Categoria > Notícia)
  - URL amigável por categoria (/noticias/categoria-nome)

#### Acesso Público para Usuários Não Logados
- **Página Pública de Notícias**:
  - Acesso livre sem necessidade de login
  - Listagem de todas as notícias publicadas
  - Paginação automática para performance
  - Sistema de busca por título e conteúdo
  - Ordenação por data (mais recentes primeiro)
- **Navegação por Categorias**:
  - Menu horizontal ou sidebar com categorias
  - Filtro dinâmico ao clicar em categoria
  - Indicador visual da categoria ativa
  - Opção "Todas as Categorias" para remover filtro
- **Visualização de Notícia Individual**:
  - Página dedicada para cada notícia
  - Exibição da categoria da notícia
  - Navegação para outras notícias da mesma categoria
  - Botões de compartilhamento (sem necessidade de login)
  - Contador público de visualizações
- **Funcionalidades Limitadas para Não Logados**:
  - Visualização e navegação completa
  - Compartilhamento por link, email e WhatsApp
  - Busca e filtros por categoria
  - **Restrições**: Não podem curtir, comentar ou acessar conteúdo restrito
- **Interface Responsiva**:
  - Design adaptado para dispositivos móveis
  - Menu de categorias colapsível em mobile
  - Cards de notícias otimizados para touch
  - Carregamento otimizado de imagens

#### Sistema de Programação e Calendário
- **Calendário de Publicação**:
  - Interface de calendário visual para agendamento
  - Data e hora específica para publicação automática
  - Fuso horário configurável por notícia
  - Visualização mensal/semanal/diária de publicações agendadas
  - Notificações de confirmação de publicação
- **Calendário de Remoção**:
  - Agendamento automático de remoção de publicação
  - Data e hora específica para despublicar notícia
  - Opção de arquivamento ao invés de exclusão
  - Notificações antes da remoção programada
  - Histórico de notícias removidas automaticamente
- **Gestão de Status**:
  - Rascunho (não publicado)
  - Agendado para publicação
  - Publicado
  - Agendado para remoção
  - Arquivado/Removido
  - Pausado (temporariamente despublicado)

#### Sistema de Estatísticas e Analytics
- **Métricas de Visualização**:
  - Contador de visualizações únicas por usuário
  - Contador de visualizações totais
  - Tempo médio de leitura por notícia
  - Taxa de engajamento (tempo na página)
  - Origem do tráfego (direto, compartilhamento, busca)
- **Estatísticas de Cliques**:
  - Rastreamento de cliques em links internos
  - Cliques em imagens e mídia
  - Cliques em categorias e tags
  - Heatmap de interações na página
- **Analytics Demográficas**:
  - Visualizações por nível de usuário
  - Distribuição por instituição/endereço/departamento
  - Horários de maior engajamento
  - Dispositivos utilizados (desktop/mobile)
- **Relatórios Detalhados**:
  - Dashboard de performance por notícia
  - Comparativo entre notícias por período
  - Ranking de notícias mais populares
  - Análise de tendências de conteúdo
  - Exportação de dados em PDF/Excel/CSV

#### Sistema de Interação Social
- **Sistema de Likes**:
  - Botão de curtir para usuários autenticados
  - Contador público de likes por notícia
  - Histórico de likes por usuário
  - Ranking de notícias mais curtidas
  - Notificações para autores sobre novos likes
- **Sistema de Compartilhamento**:
  - **Compartilhamento por Email**:
    - Formulário integrado para envio por email
    - Template personalizado com preview da notícia
    - Rastreamento de emails enviados
    - Lista de destinatários sugeridos
  - **Compartilhamento por WhatsApp**:
    - Integração com WhatsApp Web/App
    - Mensagem pré-formatada com título e link
    - Preview automático da notícia no WhatsApp
    - Contador de compartilhamentos via WhatsApp
  - **Compartilhamento por Link**:
    - Geração de link direto copiável
    - QR Code para acesso rápido
    - Links encurtados com rastreamento
    - Estatísticas de acessos via link compartilhado
- **Funcionalidades Avançadas de Compartilhamento**:
  - Botões de compartilhamento em redes sociais
  - Compartilhamento interno entre usuários do sistema
  - Histórico de compartilhamentos por usuário
  - Métricas de viralização e alcance
  - Notificações de compartilhamentos para autores

#### Interface de Gestão de Notícias
- **Dashboard de Analytics**:
  - Visão geral de performance de todas as notícias
  - Gráficos interativos de visualizações e engajamento
  - Métricas em tempo real
  - Alertas de performance (alta/baixa)
- **Editor Avançado**:
  - Interface WYSIWYG completa
  - Preview em tempo real
  - Configurações de SEO (meta tags, descrições)
  - Otimização para dispositivos móveis
- **Gestão de Mídia Integrada**:
  - Upload múltiplo de imagens
  - Editor de imagens básico
  - Galeria de mídia reutilizável
  - Otimização automática de imagens
- **Página de Configuração de Notícias**:
  - **Configurações de Paginação**:
    - Definição do número de notícias por página na listagem pública
    - Opções predefinidas: 5, 10, 15, 20, 25, 30 notícias por página
    - Campo personalizado para valores específicos (mín: 5, máx: 50)
    - Preview em tempo real da paginação
    - Aplicação automática nas páginas públicas
  - **Configurações de Exibição**:
    - Formato de data de publicação (dd/mm/aaaa, dd/mm/aa, relativo)
    - Tamanho do resumo/chamada (caracteres ou palavras)
    - Exibição de autor nas listagens (sim/não)
    - Exibição de categoria nas listagens (sim/não)
    - Exibição de contador de visualizações (sim/não)
  - **Configurações de SEO**:
    - Meta título padrão para páginas de notícias
    - Meta descrição padrão
    - Estrutura de URL amigável (/noticias/ano/mes/titulo ou /noticias/titulo)
    - Configuração de Open Graph para compartilhamento
  - **Configurações de Cache**:
    - Tempo de cache para listagem de notícias (minutos)
    - Tempo de cache para notícias individuais (minutos)
    - Opção de limpeza manual do cache
    - Status do cache (ativo/inativo)
  - **Interface de Configuração**:
    - Formulário organizado em abas (Paginação, Exibição, SEO, Cache)
    - Validação em tempo real dos campos
    - Botão "Salvar Configurações" com confirmação
    - Botão "Restaurar Padrões" com confirmação
    - Preview das alterações antes de salvar
    - Log de alterações de configuração com data/hora e usuário
  - **Permissões de Acesso**:
    - Acesso restrito a usuários Admin e Fundador
    - Colaboradores com subnível "Administrador" podem visualizar
    - Log de auditoria para todas as alterações de configuração
  - **Configuração Técnica**:
    - Armazenamento em tabela `news_settings`
    - Cache Redis para configurações frequentemente acessadas
    - API endpoint: `/admin/news/settings` (GET/POST)
    - Middleware de validação de permissões
    - Backup automático das configurações antes de alterações

### 3. Sistema de Votações
- **Criação de enquetes** e votações pelo fundador
- **Controle de elegibilidade** por nível de usuário e estrutura organizacional
- **Resultados em tempo real** com WebSocket
- **Histórico de votações**
- **Relatórios detalhados**
- **Sistema de tempo real** com Socket.io para atualizações instantâneas

#### Funcionalidades de Criação (Fundador)
- **Criação de votações**: Interface completa para criação de enquetes e votações
- **Configuração de elegibilidade**:
  - Por nível de usuário (Usuário, Associado, Colaborador)
  - Por estrutura organizacional:
    - Instituição específica
    - Endereço específico
    - Departamento específico
    - Combinações múltiplas
- **Tipos de votação**:
  - Enquetes simples (múltipla escolha)
  - Votações oficiais (sim/não)
  - Eleições (candidatos)
- **Configurações avançadas**:
  - Título da votação (obrigatório)
  - Data/hora de início e fim (validade da votação)
  - Votação anônima ou identificada
  - Resultados públicos ou privados
  - Quórum mínimo
  - **Sistema de palavra-chave**:
    - Geração automática de palavra-chave única
    - Configuração se palavra-chave é obrigatória ou opcional
    - Exibição da palavra-chave para usuários elegíveis
    - Validação da palavra-chave no momento do voto

#### Sistema de Elegibilidade
- **Filtros por nível**:
  - Todos os usuários
  - Apenas associados
  - Apenas colaboradores
  - Combinações personalizadas
- **Filtros organizacionais**:
  - Usuários de instituição específica
  - Usuários de endereço específico
  - Usuários de departamento específico
  - Múltiplas seleções simultâneas
- **Validação automática**: Sistema verifica elegibilidade no momento do voto

#### Interface de Votação
- **Dashboard de votações ativas** para usuários elegíveis
- **Exibição de palavra-chave** (quando configurada como obrigatória)
- **Campo de entrada** para palavra-chave durante o voto
- **Validação em tempo real** da palavra-chave inserida
- **Histórico de participação** individual
- **Notificações automáticas** para novas votações
- **Interface responsiva** para todos os dispositivos
- **Atualizações em tempo real** via WebSocket para resultados e status

#### Sistema de Tempo Real
- **Arquitetura WebSocket**:
  - Servidor Socket.io integrado ao Laravel
  - Conexões persistentes para usuários ativos
  - Salas de votação específicas por ID da votação
  - Autenticação de socket via JWT token
  - Reconexão automática em caso de perda de conexão
- **Eventos em Tempo Real**:
  - `voting.started`: Nova votação iniciada
  - `voting.ended`: Votação encerrada
  - `vote.cast`: Novo voto computado (sem identificação)
  - `results.updated`: Atualização de resultados parciais
  - `participant.joined`: Novo participante elegível conectado
  - `quorum.reached`: Quórum mínimo atingido
  - `voting.extended`: Prazo de votação estendido
- **Funcionalidades de Tempo Real**:
  - **Contadores dinâmicos**: Número de votos em tempo real
  - **Gráficos atualizados**: Visualização instantânea de resultados
  - **Status de participação**: Indicador de usuários online
  - **Notificações push**: Alertas instantâneos sobre eventos
  - **Sincronização automática**: Estado consistente entre dispositivos
  - **Indicador de atividade**: Mostra quando outros usuários estão votando
- **Interface de Tempo Real**:
  - **Barra de progresso dinâmica**: Atualização automática da participação
  - **Contador regressivo**: Timer em tempo real para encerramento
  - **Indicadores visuais**: Animações para novos votos
  - **Status de conectividade**: Indicador de conexão WebSocket
  - **Modo offline**: Fallback para quando não há conexão
  - **Sincronização**: Atualização automática ao reconectar
- **Segurança em Tempo Real**:
  - **Autenticação de socket**: Validação de usuário por token
  - **Autorização por sala**: Acesso apenas a votações elegíveis
  - **Rate limiting**: Prevenção de spam de eventos
  - **Validação de eventos**: Verificação de integridade dos dados
  - **Logs de auditoria**: Registro de todas as ações em tempo real
  - **Detecção de anomalias**: Identificação de comportamentos suspeitos

#### Relatórios e Análises
- **Relatórios por estrutura organizacional**
- **Taxa de participação** por grupo
- **Análise demográfica** dos votos
- **Exportação de dados** em múltiplos formatos

#### Página de Estatísticas de Votação
- **Dashboard Analítico Completo**:
  - Métricas em tempo real de todas as votações
  - Gráficos interativos de participação e resultados
  - Análise de tendências e padrões de comportamento
  - Comparativo histórico entre votações
- **Métricas de Participação**:
  - Taxa de participação geral e por segmento
  - Participação por nível de usuário (Associado, Colaborador, etc.)
  - Distribuição geográfica por instituição/endereço/departamento
  - Análise temporal (horários e dias de maior engajamento)
- **Análise Demográfica**:
  - Perfil dos participantes por votação
  - Segmentação por estrutura organizacional
  - Padrões de comportamento por grupo demográfico
  - Correlação entre perfil e tipo de voto
- **Métricas de Performance**:
  - Tempo médio de participação por votação
  - Taxa de abandono durante o processo
  - Efetividade de notificações e lembretes
  - Impacto da palavra-chave na participação
- **Relatórios Avançados**:
  - Heatmap de participação por horário e dia da semana
  - Análise de sazonalidade e tendências
  - Comparativo de engajamento entre tipos de votação
  - Métricas de alcance e penetração por segmento
- **Funcionalidades de Exportação**:
  - Relatórios personalizáveis em PDF, Excel e CSV
  - Dashboards imprimíveis para apresentações
  - Dados brutos para análises externas
  - Agendamento de relatórios automáticos
- **Filtros e Segmentação**:
  - Filtros avançados por período, tipo e categoria
  - Segmentação por múltiplos critérios simultâneos
  - Comparação entre períodos específicos
  - Análise de subgrupos personalizados

### 4. Gestão de Convênios

#### Sistema de Parceiros
- **Cadastro de empresas parceiras** com nível de usuário "Parceiro"
- **Validação de dados empresariais** (CNPJ, razão social, contatos)
- **Perfil completo da empresa** com informações de contato e suporte
- **Dashboard exclusivo** para gestão de convênios
- **Sistema de colaboradores**:
  - Cadastro de funcionários como "Colaboradores de Parceiro"
  - Controle de permissões e acesso ao sistema
  - Gestão de equipe para validação de convênios
  - Histórico de atividades por colaborador

#### Criação e Gestão de Convênios (Parceiros)
- **Interface de criação** de convênios e benefícios
- **Campos obrigatórios**:
  - Nome do convênio
  - Descrição detalhada do benefício
  - Tipo de desconto (percentual, valor fixo, promoção especial)
  - Validade do convênio (data início/fim)
  - Categoria do benefício
  - Termos e condições de uso
  - Material promocional (imagens, logos)
- **Status de convênios**:
  - Rascunho: Em edição pelo parceiro
  - Pendente: Aguardando aprovação do diretor
  - Aprovado: Ativo e disponível para uso
  - Rejeitado: Negado pelo diretor (pode ser reeditado)
  - Expirado: Convênio com validade vencida
  - Suspenso: Temporariamente indisponível

#### Sistema de Aprovação (Diretores)
- **Fila de aprovação** com convênios pendentes
- **Interface de análise** com todos os detalhes do convênio
- **Ações disponíveis**:
  - Aprovar convênio
  - Rejeitar com motivo detalhado
  - Solicitar alterações específicas
  - Suspender convênio ativo
- **Notificações automáticas** para parceiros sobre status
- **Histórico de aprovações** e decisões tomadas

#### Utilização de Convênios (Associados)
- **Catálogo de convênios** aprovados e ativos
- **Filtros por categoria** e tipo de benefício
- **Sistema de QR Code**:
  - Geração de QR Code único por utilização
  - QR Code contém: ID do associado, ID do convênio, timestamp, hash de validação
  - Validade limitada do QR Code (ex: 15 minutos)
  - Criptografia para prevenir falsificação
- **Validação pelo parceiro**:
  - **Sistema de validação por colaboradores**:
    - Colaboradores de parceiro acessam módulo validador
    - Interface web responsiva para escaneamento de QR codes
    - Verificação automática da validade e autenticidade
    - Confirmação da identidade do associado
    - Registro da utilização no sistema com dados do validador
  - **Controles de segurança**:
    - Autenticação obrigatória do colaborador validador
    - Log completo de todas as validações realizadas
    - Prevenção de validações duplicadas
    - Notificação automática para o parceiro sobre uso
- **Histórico de utilização** individual do associado

#### Visualização Pública (Usuários)
- **Catálogo público** de convênios disponíveis
- **Informações sobre benefícios** da associação
- **Call-to-action** para se tornar associado
- **Filtros e busca** por categoria e empresa

#### Relatórios e Analytics
- **Para Colaboradores de Parceiro**:
  - Histórico de validações realizadas
  - Relatórios de atividade diária/semanal
  - Estatísticas de convênios validados
- **Para Parceiros**:
  - Número de visualizações do convênio
  - Quantidade de utilizações por período
  - Perfil demográfico dos usuários
  - Taxa de conversão e engajamento
  - Performance por colaborador validador
  - Relatórios de equipe de validação
- **Para Diretores**:
  - Relatórios de aprovações por período
  - Convênios mais utilizados
  - Performance por parceiro
  - Métricas de satisfação
- **Para Fundadores/Admins**:
  - **Dashboard de Analytics Completo**:
    - Estatísticas consolidadas de todo o sistema
    - ROI dos convênios para o sindicato
    - Análise de parceiros mais ativos
    - Métricas de utilização por região/departamento
    - Gráficos de tendências e sazonalidade
    - Ranking de convênios por categoria
    - Dados demográficos detalhados dos usuários
    - Performance de colaboradores validadores
    - Análise de eficiência do sistema de validação

#### Categorias de Benefícios
- **Saúde e Bem-estar**: Clínicas, farmácias, academias
- **Educação**: Cursos, faculdades, treinamentos
- **Alimentação**: Restaurantes, supermercados, delivery
- **Lazer e Entretenimento**: Cinemas, teatros, parques
- **Serviços**: Oficinas, salões, consultorias
- **Tecnologia**: Eletrônicos, software, telecomunicações
- **Viagens**: Hotéis, agências, transporte
- **Outros**: Categoria flexível para diversos benefícios

#### Controles de Segurança
- **Validação de QR Code** com criptografia
- **Prevenção de fraudes** e uso indevido
- **Logs de auditoria** para todas as utilizações
- **Controle de frequência** de uso por associado
- **Blacklist** para convênios ou parceiros problemáticos

### 5. Gestão de Eventos
- **Calendário de eventos** sindicais
- **Sistema de inscrições**
- **Controle de participantes**
- **Notificações automáticas**
- **Relatórios de presença**

## Especificações Técnicas Adicionais

### Sistema de Gestão de Usuários e Delegação de Permissões

### Infraestrutura de Gestão de Usuários
- **Banco de dados relacional**: Tabelas para usuários, permissões, delegações e auditoria
- **Tabela `users`** (estrutura principal):
  - `id` (chave primária)
  - `name` (nome completo - obrigatório)
  - `email` (email principal - único e obrigatório)
  - `cpf` (CPF - único e obrigatório)
  - `email_verified_at` (verificação de email)
  - `password` (senha criptografada)
  - `user_level` (nível de acesso)
  - `institution_id`, `address_id`, `department_id` (dados organizacionais)
  - `is_active` (status ativo/inativo)
  - `created_at`, `updated_at`
- **Sistema de cache**: Redis para cache de permissões e sessões ativas
- **Middleware de autorização**: Validação de permissões em tempo real
- **Sistema de logs**: Auditoria completa de todas as ações administrativas

### Gestão Completa de Usuários (Admin)
- **Interface unificada**: Dashboard centralizado para todos os tipos de usuário
- **Filtros avançados**:
  - Por nível de usuário (Admin, Fundador, Colaborador, Parceiro, Associado, Usuário)
  - Por subnível de colaborador (Diretor, Administrador, Jornalista, Atendimento)
  - Por dados organizacionais (instituição, endereço, departamento)
  - Por status (ativo, inativo, pendente, suspenso)
  - Por data de cadastro e última atividade
- **Funcionalidades administrativas**:
  - Busca em tempo real com autocomplete
  - Visualização em lista paginada ou cards
  - Ações em lote para múltiplos usuários
  - Exportação de relatórios em CSV/PDF
  - Histórico completo de alterações por usuário
  - Logs de auditoria com timestamp e responsável

### Sistema de Delegação de Permissões (Fundador)
- **Delegação granular**:
  - Seleção específica de colaboradores para receber delegação
  - Definição de escopo (quais níveis de usuário podem gerenciar)
  - Configuração de permissões específicas (visualizar, editar, promover, desativar)
  - Definição de tempo de validade (permanente ou temporária)
- **Controles de segurança**:
  - Aprovação obrigatória para delegações sensíveis
  - Notificação automática sobre delegações ativas
  - Revogação imediata de permissões
  - Auditoria completa de uso das delegações
- **Interface de gestão**:
  - Dashboard de delegações ativas
  - Histórico de delegações por colaborador
  - Relatórios de uso das permissões delegadas
  - Alertas sobre uso inadequado ou suspeito

### Gestão Delegada de Usuários (Colaboradores)
- **Interface adaptativa**: Mostra apenas funcionalidades autorizadas
- **Validação de permissões**: Verificação em tempo real das ações permitidas
- **Limitações técnicas**:
  - Não pode alterar usuários de nível igual ou superior
  - Não pode conceder permissões que não possui
  - Ações limitadas ao escopo definido pelo fundador
  - Tempo limite para sessões com permissões delegadas
- **Auditoria e controle**:
  - Log detalhado de todas as ações realizadas
  - Notificação automática ao fundador sobre ações críticas
  - Relatórios periódicos de atividade
  - Sistema de alertas para comportamento anômalo

### Jobs Assíncronos
- **ProcessUserBulkActions**: Processamento de ações em lote
- **GenerateUserReports**: Geração de relatórios complexos
- **AuditUserChanges**: Processamento de logs de auditoria
- **NotifyDelegationChanges**: Notificações sobre alterações de delegação
- **CleanupExpiredDelegations**: Limpeza de delegações expiradas

### Monitoramento e Métricas
- **Dashboard de métricas**: Estatísticas de usuários por categoria
- **Relatórios de crescimento**: Gráficos de evolução de usuários
- **Análise de engajamento**: Métricas de atividade por tipo de usuário
- **Alertas automáticos**: Notificações sobre anomalias ou problemas
- **Performance monitoring**: Monitoramento de performance das operações

## Sistema de Blacklist JWT e Controle de Sessões
- **Infraestrutura de blacklist**: Redis para armazenamento de alta performance
- **Controle de versão de tokens**:
  - Cada usuário possui um `token_version` incrementado a cada logout
  - JWT contém versão do token para validação
  - Tokens com versão anterior são automaticamente inválidos
- **Armazenamento de tokens revogados**:
  - Chave Redis: `jwt_blacklist:{user_id}:{jti}` (TTL = tempo de expiração do token)
  - Estrutura de dados otimizada para consultas rápidas
  - Limpeza automática de tokens expirados
- **Middleware de validação**:
  - `JWTBlacklistMiddleware`: Verifica blacklist em cada requisição
  - Validação de versão do token contra versão atual do usuário
  - Resposta automática 401 para tokens inválidos/revogados
- **Funcionalidades de segurança**:
  - Logout de todas as sessões ativas
  - Revogação forçada por suspeita de comprometimento
  - Logs de auditoria para ações de revogação
  - Rate limiting para tentativas de uso de tokens inválidos
- **Jobs assíncronos**:
  - `RevokeAllUserTokensJob`: Revoga todos os tokens de um usuário
  - `CleanExpiredTokensJob`: Limpeza periódica da blacklist
  - `AuditSuspiciousActivityJob`: Análise de atividade suspeita

### Sistema de Configuração de Email
- **Infraestrutura de provedores**: Sistema modular para múltiplos provedores
- **MailHog (Desenvolvimento)**:
  - Configuração automática via Docker Compose
  - Interface web acessível em `http://localhost:8025`
  - Captura de emails sem envio real
  - API REST para integração e testes automatizados
  - Armazenamento temporário em memória
- **Google SMTP (Produção)**:
  - Configuração via `MAIL_MAILER=smtp`
  - Suporte a OAuth2 e App Passwords
  - Configurações: `MAIL_HOST=smtp.gmail.com`, `MAIL_PORT=587`
  - Criptografia TLS obrigatória
  - Rate limiting respeitando limites do Gmail
- **Dashboard de Configuração (Admin)**:
  - **Interface de seleção de provedor**:
    - Radio buttons para MailHog/Google SMTP
    - Status visual do provedor ativo
    - Indicador de conectividade em tempo real
  - **Formulário de credenciais SMTP**:
    - Campos: Host, Port, Username, Password, Encryption
    - Validação de formato e obrigatoriedade
    - Criptografia de senhas no banco de dados
    - Mascaramento de senhas na interface
  - **Funcionalidades avançadas**:
    - Botão "Testar Conexão" com feedback imediato
    - Envio de email de teste para validação
    - Histórico de alterações com timestamp e usuário
    - Backup automático antes de alterações
    - Rollback para configuração anterior
- **Segurança e armazenamento**:
  - Criptografia AES-256 para credenciais sensíveis
  - Armazenamento seguro no banco de dados
  - Logs de auditoria para alterações de configuração
  - Validação de permissões (apenas Admin)
  - Rate limiting para tentativas de configuração

### Sistema de Filas e Processamento Assíncrono
- **Infraestrutura de Filas (Laravel Queues + Redis)**:
  - Redis como driver principal para filas
  - Múltiplas filas com prioridades diferentes
  - Workers dedicados para cada tipo de processamento
  - Supervisord para monitoramento e restart automático
- **Filas de Email**:
  - `emails-high`: Verificação, recuperação de senha (prioridade máxima)
  - `emails-medium`: Notificações importantes, alertas (prioridade média)
  - `emails-low`: Newsletters, relatórios periódicos (prioridade baixa)
  - Processamento paralelo com múltiplos workers
- **Jobs Assíncronos**:
  - `SendEmailVerificationJob`: Envio de email de verificação
  - `SendPasswordResetJob`: Envio de recuperação de senha
  - `SendVotingNotificationJob`: Notificações de votações
  - `SendConvenioAlertJob`: Alertas de novos convênios
  - `ProcessImageUploadJob`: Processamento de imagens de avatar
  - `GenerateReportJob`: Geração de relatórios periódicos
- **Monitoramento e Controle**:
  - Dashboard de monitoramento de filas
  - Métricas de performance e throughput
  - Alertas para filas congestionadas
  - Logs detalhados de execução
  - Retry automático com backoff exponencial
  - Dead letter queue para jobs falhados

### Sistema de Avatar e Upload de Imagens
- **Frontend (Vue.js)**:
  - Componente de upload com drag & drop
  - Ferramenta de crop interativa usando canvas HTML5
  - Pré-visualização em tempo real da área selecionada
  - Validação de formato e tamanho antes do upload
  - Progress bar para acompanhamento do upload
  - Interface responsiva para dispositivos móveis
- **Backend (Laravel)**:
  - Validação de arquivo (formato, tamanho, tipo MIME)
  - Processamento assíncrono via jobs/queues
  - Redimensionamento automático para 300x300 pixels
  - Otimização de qualidade e compressão de imagem
  - Armazenamento seguro com nomes únicos
  - API endpoints para upload e recuperação
  - Limpeza automática de arquivos temporários
- **Segurança**:
  - Validação rigorosa de tipos de arquivo
  - Sanitização de nomes de arquivo
  - Proteção contra upload de scripts maliciosos
  - Rate limiting para uploads
  - Verificação de integridade da imagem

### Performance
- **Cache distribuído** com Redis
- **Otimização de queries** no banco
- **Lazy loading** no frontend
- **Compressão de assets**

### Monitoramento
- **Logs estruturados** para auditoria
- **Métricas de performance**
- **Alertas automáticos** para falhas
- **Backup automático** do banco de dados

### Escalabilidade
- **Arquitetura de microserviços** preparada
- **Load balancing** configurável
- **CDN** para assets estáticos
- **Database sharding** preparado

## Fluxos de Trabalho

### 1. Fluxo de Blacklist JWT e Controle de Sessões

#### 1.1. Processo de Login
1. **Autenticação do usuário**: Validação de credenciais
2. **Geração do JWT**: Token contém `user_id`, `token_version`, `jti` (JWT ID único)
3. **Verificação de versão**: Consulta versão atual do token do usuário
4. **Emissão do token**: JWT válido com versão atual
5. **Registro de sessão**: Log da nova sessão ativa

#### 1.2. Processo de Logout
1. **Recebimento da solicitação**: Usuário solicita logout
2. **Extração do token**: Obtenção do JWT do cabeçalho Authorization
3. **Adição à blacklist**: Token adicionado ao Redis com TTL
4. **Incremento da versão**: `token_version` do usuário é incrementado
5. **Invalidação global**: Todos os tokens anteriores tornam-se inválidos
6. **Confirmação**: Resposta de logout bem-sucedido
7. **Limpeza de sessão**: Remoção de cookies e dados de sessão

#### 1.3. Validação de Requisições Autenticadas
1. **Interceptação pelo middleware**: `JWTBlacklistMiddleware` processa requisição
2. **Extração do token**: Obtenção do JWT do cabeçalho
3. **Verificação básica**: Validação de assinatura e expiração
4. **Consulta na blacklist**: Verificação se token está revogado
5. **Validação de versão**: Comparação da versão do token com versão atual do usuário
6. **Decisão de acesso**:
   - **Token válido**: Requisição prossegue
   - **Token inválido**: Resposta 401 Unauthorized
7. **Log de auditoria**: Registro de tentativas de acesso

#### 1.4. Revogação Forçada de Tokens
1. **Detecção de atividade suspeita**: Sistema identifica comportamento anômalo
2. **Trigger de revogação**: Acionamento automático ou manual
3. **Execução do job**: `RevokeAllUserTokensJob` é disparado
4. **Incremento de versão**: `token_version` é incrementado
5. **Notificação ao usuário**: Email de alerta sobre revogação
6. **Redirecionamento**: Usuário é forçado a fazer novo login
7. **Auditoria**: Log detalhado da ação de revogação

### 2. Registro de Usuário
1. Preenchimento de formulário (nome, email, CPF)
2. Validação de hCaptcha
3. Envio de email de confirmação
4. Ativação da conta
5. Definição de perfil inicial

### Gestão de Permissões
1. Fundador acessa painel de colaboradores
2. Seleciona colaborador específico
3. Define permissões por módulo
4. Sistema registra alterações
5. Notificação ao colaborador

### Publicação de Conteúdo
1. Colaborador cria conteúdo
2. Sistema valida permissões
3. Conteúdo entra em fila de aprovação (se necessário)
4. Publicação automática ou manual
5. Notificação aos usuários relevantes

### Criação e Gestão de Votações
1. Fundador acessa módulo de votações no dashboard
2. Define informações básicas:
   - Título da votação (obrigatório)
   - Tipo de votação (enquete, votação oficial, eleição)
3. Configura elegibilidade:
   - Seleciona níveis de usuário (usuário/associado/colaborador)
   - Define filtros organizacionais (instituição/endereço/departamento)
4. Configura parâmetros da votação:
   - Período de votação (início/fim) - validade da votação
   - Tipo de resultado (anônimo/identificado, público/privado)
   - Quórum mínimo (se aplicável)
5. **Configuração de palavra-chave**:
   - Sistema gera automaticamente palavra-chave única
   - Fundador define se palavra-chave é obrigatória ou opcional
   - Palavra-chave é exibida para usuários elegíveis (se obrigatória)
6. Sistema valida configurações e ativa votação
7. Notificações automáticas enviadas aos usuários elegíveis
8. Monitoramento em tempo real da participação
9. Encerramento automático e geração de relatórios

### Criação de Convênios (Parceiros)
1. Parceiro acessa dashboard específico
2. Acessa módulo de gestão de convênios
3. Clica em "Criar Novo Convênio"
4. Preenche formulário com dados obrigatórios:
   - Nome e descrição do convênio
   - Tipo e valor do desconto
   - Período de validade
   - Categoria do benefício
   - Termos e condições
5. Upload de materiais promocionais (opcional)
6. Salva como rascunho ou submete para aprovação
7. Sistema gera notificação para diretores (se submetido)
8. Parceiro acompanha status via dashboard

### Aprovação de Convênios (Diretores)
1. Diretor recebe notificação de novo convênio pendente
2. Acessa módulo de aprovação no dashboard
3. Visualiza lista de convênios pendentes
4. Seleciona convênio para análise detalhada
5. Revisa todas as informações e materiais
6. Toma decisão:
   - Aprovar: Convênio fica ativo imediatamente
   - Rejeitar: Adiciona motivo detalhado da rejeição
   - Solicitar alterações: Especifica mudanças necessárias
7. Sistema notifica parceiro automaticamente
8. Convênio aprovado aparece no catálogo público

### Utilização de Convênios (Associados)
1. Associado acessa catálogo de convênios no dashboard
2. Navega ou filtra convênios por categoria
3. Seleciona convênio desejado
4. Clica em "Gerar QR Code para Uso"
5. Sistema gera QR Code único com validade limitada
6. Associado apresenta QR Code ao parceiro
7. **Validação pelo Colaborador do Parceiro**:
   - Colaborador acessa módulo validador no dashboard
   - Escaneia QR Code com interface web responsiva
   - Sistema valida automaticamente:
     - Autenticidade do QR Code
     - Validade temporal
     - Status do associado
     - Disponibilidade do convênio
   - Colaborador confirma a utilização do benefício
   - Sistema registra validação com dados do colaborador
8. Confirmação da utilização é registrada
9. Associado e parceiro recebem confirmação
10. Utilização é adicionada ao histórico do associado

### Cadastro de Colaboradores (Parceiros)
1. Parceiro acessa módulo de gestão de colaboradores
2. Clica em "Cadastrar Novo Colaborador"
3. Preenche dados do funcionário:
   - Nome completo
   - Email corporativo
   - Cargo/função
   - Permissões de acesso
4. Sistema envia convite por email ao colaborador
5. Colaborador ativa conta e define senha
6. Acesso liberado ao módulo validador
7. Parceiro pode monitorar atividades do colaborador

### Validação de QR Code (Colaboradores de Parceiro)
1. Colaborador faz login no sistema
2. Acessa módulo "Validador de Convênios"
3. Cliente/associado apresenta QR Code
4. Colaborador escaneia código via interface web
5. Sistema processa validação em tempo real:
   - Verifica autenticidade criptográfica
   - Confirma validade temporal
   - Valida status do associado
   - Verifica disponibilidade do convênio
6. Resultado exibido instantaneamente:
   - ✅ Válido: Libera uso do benefício
   - ❌ Inválido: Exibe motivo da rejeição
7. Se válido, colaborador confirma aplicação do benefício
8. Sistema registra utilização com timestamp e dados do validador
9. Notificações automáticas enviadas:
   - Para o associado (confirmação de uso)
   - Para o parceiro (relatório de utilização)
10. Histórico atualizado em tempo real

### Upload e Configuração de Avatar
1. **Acesso à configuração de perfil**:
   - Usuário acessa seção "Meu Perfil"
   - Clica em "Alterar Avatar" ou área de foto
   - Modal/página de upload é exibida

2. **Processo de upload**:
   - Usuário seleciona arquivo ou arrasta para área de drop
   - Validação imediata de formato (JPG, PNG, GIF) e tamanho
   - Pré-visualização da imagem carregada
   - Ferramenta de crop é ativada automaticamente

3. **Ferramenta de crop interativa**:
   - Interface com área de seleção redimensionável
   - Pré-visualização em tempo real do resultado final
   - Controles para ajustar posição e zoom
   - Botões para rotacionar imagem se necessário
   - Preview do avatar em tamanho 300x300 pixels

4. **Processamento no servidor**:
   - Upload da área selecionada via AJAX
   - Job assíncrono processa a imagem:
     - Redimensionamento para 300x300 pixels
     - Otimização de qualidade e compressão
     - Geração de nome único para arquivo
     - Armazenamento seguro no sistema
   - Atualização do perfil do usuário
   - Notificação de sucesso para o frontend

5. **Finalização**:
   - Avatar atualizado em tempo real na interface
   - Limpeza de arquivos temporários
   - Log da alteração no histórico do usuário

### 3. Configuração de Provedor de Email (Admin)

#### 3.1. Acesso ao Módulo de Configuração
1. **Login como Admin**: Autenticação com nível de acesso Admin
2. **Navegação**: Acesso ao dashboard administrativo
3. **Seleção do módulo**: Clique em "Configurações de Email"
4. **Verificação de permissões**: Sistema valida nível de acesso
5. **Carregamento da interface**: Exibição da configuração atual

#### 3.2. Seleção do Provedor de Email
1. **Visualização dos provedores**: Lista com MailHog e Google SMTP
2. **Status atual**: Indicador visual do provedor ativo
3. **Seleção**: Radio button para escolher provedor desejado
4. **Validação**: Sistema verifica compatibilidade com ambiente
5. **Confirmação**: Modal de confirmação para alteração

#### 3.3. Configuração de Credenciais SMTP
1. **Formulário de credenciais**: Campos para Host, Port, Username, Password, Encryption
2. **Validação em tempo real**: Verificação de formato dos campos
3. **Mascaramento de senha**: Ocultação de credenciais sensíveis
4. **Pré-validação**: Verificação básica de conectividade
5. **Criptografia**: Dados sensíveis são criptografados antes do armazenamento

#### 3.4. Teste de Conectividade
1. **Botão "Testar Conexão"**: Acionamento do teste
2. **Validação SMTP**: Tentativa de conexão com o servidor
3. **Feedback visual**: Indicador de sucesso/falha em tempo real
4. **Envio de email de teste**: Opcional para validação completa
5. **Log de resultado**: Registro detalhado do teste realizado

#### 3.5. Aplicação e Backup
1. **Backup automático**: Configuração atual é salva antes da alteração
2. **Aplicação das mudanças**: Nova configuração é ativada
3. **Atualização do sistema**: Recarregamento das configurações de email
4. **Confirmação**: Notificação de sucesso da alteração
5. **Log de auditoria**: Registro da alteração com timestamp e usuário

#### 3.6. Rollback (se necessário)
1. **Detecção de problema**: Sistema ou admin identifica falha
2. **Acesso ao histórico**: Visualização de configurações anteriores
3. **Seleção da versão**: Escolha da configuração para restaurar
4. **Confirmação de rollback**: Modal de segurança
5. **Restauração**: Aplicação da configuração anterior
6. **Validação**: Teste automático da configuração restaurada

### 4. Gestão de Usuários e Delegação de Permissões

#### 4.1. Gestão Completa de Usuários (Admin)
1. **Acesso ao módulo**: Login como Admin e navegação para gestão de usuários
2. **Aplicação de filtros**: Seleção de filtros por nível, instituição, status ou data
3. **Busca e visualização**: Busca em tempo real e visualização em lista ou cards
4. **Seleção de usuários**: Seleção individual ou múltipla para ações em lote
5. **Execução de ações**: Alteração de nível, ativação/desativação, edição de dados
6. **Confirmação e auditoria**: Confirmação das ações e registro em logs de auditoria
7. **Geração de relatórios**: Exportação de relatórios personalizados em CSV/PDF

#### 4.2. Delegação de Permissões (Fundador)
1. **Seleção do colaborador**: Escolha do colaborador para receber delegação
2. **Definição de escopo**: Configuração dos níveis de usuário que pode gerenciar
3. **Configuração de permissões**: Definição das ações específicas permitidas
4. **Definição de validade**: Configuração se é permanente ou temporária
5. **Aprovação e ativação**: Confirmação da delegação e ativação imediata
6. **Notificação**: Envio automático de notificação ao colaborador
7. **Monitoramento**: Acompanhamento do uso das permissões delegadas

#### 4.3. Gestão Delegada de Usuários (Colaborador)
1. **Verificação de permissões**: Sistema valida permissões ativas do colaborador
2. **Interface adaptativa**: Carregamento da interface com funcionalidades autorizadas
3. **Seleção de usuários**: Visualização apenas dos usuários no escopo permitido
4. **Execução de ações**: Realização de ações dentro das permissões delegadas
5. **Validação em tempo real**: Sistema verifica cada ação antes da execução
6. **Auditoria automática**: Registro detalhado de todas as ações realizadas
7. **Notificação ao fundador**: Envio de relatório sobre ações críticas realizadas

#### 4.4. Revogação de Delegação (Fundador)
1. **Identificação da necessidade**: Fundador decide revogar delegação
2. **Acesso ao painel**: Navegação para dashboard de delegações ativas
3. **Seleção da delegação**: Escolha da delegação específica para revogar
4. **Confirmação de revogação**: Modal de segurança para confirmar ação
5. **Revogação imediata**: Sistema remove permissões instantaneamente
6. **Notificação**: Envio automático de notificação ao colaborador afetado
7. **Auditoria**: Registro da revogação nos logs do sistema

#### 4.5. Monitoramento e Relatórios
1. **Coleta de métricas**: Sistema coleta dados de uso e performance
2. **Processamento assíncrono**: Jobs processam dados para relatórios
3. **Geração de dashboards**: Criação de gráficos e métricas em tempo real
4. **Alertas automáticos**: Sistema detecta anomalias e envia alertas
5. **Relatórios periódicos**: Geração automática de relatórios de atividade
6. **Análise de tendências**: Identificação de padrões de uso e crescimento
7. **Otimização**: Sugestões para melhorias baseadas nos dados coletados

### 5. Sistema de Envio de Emails em Segundo Plano
1. **Trigger de envio de email**:
   - Ação do usuário dispara necessidade de email
   - Sistema cria job específico para o tipo de email
   - Job é adicionado à fila apropriada (high/medium/low)
   - Resposta imediata para o usuário (não aguarda envio)

2. **Processamento na fila**:
   - Worker dedicado pega job da fila por prioridade
   - Validação dos dados do destinatário
   - Verificação de blacklist e rate limiting
   - Geração do conteúdo do email (template + dados)

3. **Envio do email**:
   - Conexão com servidor SMTP configurado
   - Envio do email com headers apropriados
   - Registro de log detalhado (sucesso/falha)
   - Atualização de métricas de entrega

4. **Tratamento de falhas**:
   - Em caso de falha temporária:
     - Job retorna para fila com delay (backoff exponencial)
     - Máximo de 3 tentativas de reenvio
   - Em caso de falha permanente:
     - Job movido para dead letter queue
     - Email adicionado à blacklist se necessário
     - Notificação para administradores

5. **Monitoramento e métricas**:
   - Dashboard atualizado em tempo real
   - Alertas automáticos para filas congestionadas
   - Relatórios de taxa de entrega por período
   - Logs acessíveis para auditoria

### Participação em Votações (Usuários Elegíveis)
1. Usuário elegível recebe notificação de nova votação
2. Acessa dashboard e visualiza votações ativas
3. Seleciona votação para participar
4. Sistema exibe:
   - Título e descrição da votação
   - Período de validade
   - Palavra-chave (se configurada como obrigatória)
   - Opções de voto disponíveis
5. **Processo de votação**:
   - Usuário seleciona sua opção de voto
   - Se palavra-chave obrigatória: insere palavra-chave no campo
   - Sistema valida palavra-chave em tempo real
   - Confirmação do voto (se palavra-chave válida ou não obrigatória)
6. Voto registrado no sistema
7. Confirmação exibida ao usuário
8. Histórico de participação atualizado

## 6. Sistema Técnico de Gestão de Notícias com Analytics e Interação

### Infraestrutura de Dados
- **Banco de Dados**:
  - Tabela `news` (notícias principais)
  - Tabela `news_categories` (categorias de notícias)
  - Tabela `news_views` (registro de visualizações)
  - Tabela `news_likes` (sistema de curtidas)
  - Tabela `news_shares` (compartilhamentos)
  - Tabela `news_schedule` (programação de publicação/remoção)
  - Tabela `news_analytics` (métricas agregadas)
  - **Sistema de Convites**:
    - Tabela `invitations` (convites enviados)
    - Tabela `invitation_batches` (lotes de convites CSV)
    - Tabela `invitation_analytics` (estatísticas de convites)
    - Tabela `invitation_limits` (limites por usuário/período)
- **Cache Redis**:
  - Contadores de visualizações em tempo real
  - Cache de estatísticas frequentemente acessadas
  - Sessões de usuários para controle de visualizações únicas

### Infraestrutura de Logs e Telemetria

#### Configuração Docker Compose

**docker-compose.observability.yml**:
```yaml
version: '3.8'
services:
  elasticsearch:
    image: docker.elastic.co/elasticsearch/elasticsearch:8.11.0
    container_name: elasticsearch
    environment:
      - cluster.name=sindicato-logs
      - node.name=es-node-1
      - discovery.type=single-node
      - "ES_JAVA_OPTS=-Xms2g -Xmx2g"
      - xpack.security.enabled=false
    volumes:
      - elasticsearch_data:/usr/share/elasticsearch/data
      - ./config/elasticsearch.yml:/usr/share/elasticsearch/config/elasticsearch.yml
    ports:
      - "9200:9200"
    networks:
      - observability
    restart: unless-stopped

  logstash:
    image: docker.elastic.co/logstash/logstash:8.11.0
    container_name: logstash
    volumes:
      - ./config/logstash/pipeline:/usr/share/logstash/pipeline
      - ./config/logstash/logstash.yml:/usr/share/logstash/config/logstash.yml
      - /var/log:/var/log:ro
    ports:
      - "5044:5044"
      - "9600:9600"
    environment:
      - "LS_JAVA_OPTS=-Xms1g -Xmx1g"
    networks:
      - observability
    depends_on:
      - elasticsearch
    restart: unless-stopped

  kibana:
    image: docker.elastic.co/kibana/kibana:8.11.0
    container_name: kibana
    ports:
      - "5601:5601"
    environment:
      - ELASTICSEARCH_HOSTS=http://elasticsearch:9200
      - SERVER_NAME=kibana.sindicato.local
    volumes:
      - ./config/kibana.yml:/usr/share/kibana/config/kibana.yml
    networks:
      - observability
    depends_on:
      - elasticsearch
    restart: unless-stopped

  prometheus:
    image: prom/prometheus:v2.47.0
    container_name: prometheus
    ports:
      - "9090:9090"
    volumes:
      - ./config/prometheus.yml:/etc/prometheus/prometheus.yml
      - ./config/alert_rules.yml:/etc/prometheus/alert_rules.yml
      - prometheus_data:/prometheus
    command:
      - '--config.file=/etc/prometheus/prometheus.yml'
      - '--storage.tsdb.path=/prometheus'
      - '--web.console.libraries=/etc/prometheus/console_libraries'
      - '--web.console.templates=/etc/prometheus/consoles'
      - '--storage.tsdb.retention.time=30d'
      - '--web.enable-lifecycle'
    networks:
      - observability
    restart: unless-stopped

  grafana:
    image: grafana/grafana:10.2.0
    container_name: grafana
    ports:
      - "3000:3000"
    environment:
      - GF_SECURITY_ADMIN_PASSWORD=admin123
      - GF_USERS_ALLOW_SIGN_UP=false
      - GF_INSTALL_PLUGINS=grafana-piechart-panel,grafana-worldmap-panel
    volumes:
      - grafana_data:/var/lib/grafana
      - ./config/grafana/provisioning:/etc/grafana/provisioning
      - ./config/grafana/dashboards:/var/lib/grafana/dashboards
    networks:
      - observability
    restart: unless-stopped

  filebeat:
    image: docker.elastic.co/beats/filebeat:8.11.0
    container_name: filebeat
    user: root
    volumes:
      - ./config/filebeat.yml:/usr/share/filebeat/filebeat.yml:ro
      - /var/lib/docker/containers:/var/lib/docker/containers:ro
      - /var/run/docker.sock:/var/run/docker.sock:ro
      - /var/log:/var/log:ro
    networks:
      - observability
    depends_on:
      - logstash
    restart: unless-stopped

  node-exporter:
    image: prom/node-exporter:v1.6.1
    container_name: node-exporter
    ports:
      - "9100:9100"
    volumes:
      - /proc:/host/proc:ro
      - /sys:/host/sys:ro
      - /:/rootfs:ro
    command:
      - '--path.procfs=/host/proc'
      - '--path.rootfs=/rootfs'
      - '--path.sysfs=/host/sys'
      - '--collector.filesystem.mount-points-exclude=^/(sys|proc|dev|host|etc)($$|/)'
    networks:
      - observability
    restart: unless-stopped

volumes:
  elasticsearch_data:
  prometheus_data:
  grafana_data:

networks:
  observability:
    driver: bridge
```

#### Configuração Laravel para Logs Estruturados

**config/logging.php**:
```php
'channels' => [
    'stack' => [
        'driver' => 'stack',
        'channels' => ['single', 'elasticsearch'],
        'ignore_exceptions' => false,
    ],
    
    'elasticsearch' => [
        'driver' => 'custom',
        'via' => App\Logging\ElasticsearchLogger::class,
        'level' => 'debug',
        'index' => 'sindicato-logs',
    ],
    
    'structured' => [
        'driver' => 'single',
        'path' => storage_path('logs/structured.log'),
        'level' => 'debug',
        'formatter' => App\Logging\StructuredFormatter::class,
    ],
],
```

**app/Logging/StructuredFormatter.php**:
```php
class StructuredFormatter implements FormatterInterface
{
    public function format(LogRecord $record): string
    {
        $data = [
            'timestamp' => $record->datetime->format('Y-m-d\TH:i:s.uP'),
            'level' => $record->level->getName(),
            'message' => $record->message,
            'context' => $record->context,
            'extra' => [
                'user_id' => auth()->id(),
                'session_id' => session()->getId(),
                'request_id' => request()->header('X-Request-ID'),
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent(),
                'url' => request()->fullUrl(),
                'method' => request()->method(),
            ],
        ];
        
        return json_encode($data) . "\n";
    }
}
```

#### Middleware de Logging e Métricas

**app/Http/Middleware/RequestLogging.php**:
```php
class RequestLogging
{
    public function handle($request, Closure $next)
    {
        $startTime = microtime(true);
        $requestId = Str::uuid();
        $request->headers->set('X-Request-ID', $requestId);
        
        Log::info('Request started', [
            'request_id' => $requestId,
            'method' => $request->method(),
            'url' => $request->fullUrl(),
            'ip' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'user_id' => auth()->id(),
        ]);
        
        $response = $next($request);
        
        $duration = (microtime(true) - $startTime) * 1000;
        
        Log::info('Request completed', [
            'request_id' => $requestId,
            'status_code' => $response->getStatusCode(),
            'duration_ms' => round($duration, 2),
            'memory_usage' => memory_get_peak_usage(true),
        ]);
        
        // Enviar métricas para Prometheus
        $this->recordMetrics($request, $response, $duration);
        
        return $response;
    }
    
    private function recordMetrics($request, $response, $duration)
    {
        $labels = [
            'method' => $request->method(),
            'route' => $request->route()?->getName() ?? 'unknown',
            'status_code' => $response->getStatusCode(),
        ];
        
        // Incrementar contador de requests
        Prometheus::counter('http_requests_total', 'Total HTTP requests')
            ->labels($labels)
            ->inc();
            
        // Registrar duração
        Prometheus::histogram('http_request_duration_ms', 'HTTP request duration')
            ->labels($labels)
            ->observe($duration);
    }
}
```

#### Configuração Prometheus

**config/prometheus.yml**:
```yaml
global:
  scrape_interval: 15s
  evaluation_interval: 15s

rule_files:
  - "alert_rules.yml"

scrape_configs:
  - job_name: 'prometheus'
    static_configs:
      - targets: ['localhost:9090']

  - job_name: 'node-exporter'
    static_configs:
      - targets: ['node-exporter:9100']

  - job_name: 'laravel-app'
    static_configs:
      - targets: ['app:8080']
    metrics_path: '/metrics'
    scrape_interval: 30s

  - job_name: 'nginx'
    static_configs:
      - targets: ['nginx:9113']

  - job_name: 'postgres'
    static_configs:
      - targets: ['postgres-exporter:9187']

  - job_name: 'redis'
    static_configs:
      - targets: ['redis-exporter:9121']

alerting:
  alertmanagers:
    - static_configs:
        - targets:
          - alertmanager:9093
```

#### Regras de Alertas

**config/alert_rules.yml**:
```yaml
groups:
  - name: infrastructure
    rules:
      - alert: HighCPUUsage
        expr: 100 - (avg by(instance) (rate(node_cpu_seconds_total{mode="idle"}[5m])) * 100) > 80
        for: 5m
        labels:
          severity: warning
        annotations:
          summary: "High CPU usage detected"
          description: "CPU usage is above 80% for more than 5 minutes"

      - alert: HighMemoryUsage
        expr: (node_memory_MemTotal_bytes - node_memory_MemAvailable_bytes) / node_memory_MemTotal_bytes * 100 > 85
        for: 3m
        labels:
          severity: critical
        annotations:
          summary: "High memory usage detected"
          description: "Memory usage is above 85% for more than 3 minutes"

  - name: application
    rules:
      - alert: HighErrorRate
        expr: rate(http_requests_total{status_code=~"5.."}[5m]) / rate(http_requests_total[5m]) * 100 > 5
        for: 5m
        labels:
          severity: critical
        annotations:
          summary: "High error rate detected"
          description: "Error rate is above 5% for more than 5 minutes"

      - alert: SlowResponseTime
        expr: histogram_quantile(0.95, rate(http_request_duration_ms_bucket[5m])) > 2000
        for: 3m
        labels:
          severity: warning
        annotations:
          summary: "Slow response time detected"
          description: "95th percentile response time is above 2 seconds"
```

#### Dashboards Grafana Pré-configurados

**config/grafana/dashboards/infrastructure.json**:
- Dashboard de infraestrutura com métricas de sistema
- Painéis para CPU, memória, disco e rede
- Alertas visuais integrados
- Drill-down para análise detalhada

**config/grafana/dashboards/application.json**:
- Dashboard de aplicação com métricas de performance
- Painéis para requests/sec, response times, error rates
- Métricas de banco de dados e cache
- Análise de usuários ativos

**config/grafana/dashboards/business.json**:
- Dashboard de métricas de negócio
- Painéis para registros, conversões, engajamento
- Análise de uso por módulo
- Métricas de crescimento

#### Scripts de Deployment e Manutenção

**scripts/deploy-observability.sh**:
```bash
#!/bin/bash

# Deploy do stack de observabilidade
docker-compose -f docker-compose.observability.yml up -d

# Aguardar serviços ficarem prontos
echo "Aguardando Elasticsearch..."
until curl -s http://localhost:9200/_cluster/health | grep -q '"status":"green"'; do
  sleep 5
done

# Configurar índices do Elasticsearch
curl -X PUT "localhost:9200/_index_template/sindicato-logs" -H 'Content-Type: application/json' -d @config/elasticsearch-template.json

# Importar dashboards do Grafana
echo "Importando dashboards do Grafana..."
for dashboard in config/grafana/dashboards/*.json; do
  curl -X POST "http://admin:admin123@localhost:3000/api/dashboards/db" \
    -H "Content-Type: application/json" \
    -d @"$dashboard"
done

echo "Stack de observabilidade deployado com sucesso!"
echo "Kibana: http://localhost:5601"
echo "Grafana: http://localhost:3000 (admin/admin123)"
echo "Prometheus: http://localhost:9090"
```

### Sistema de Programação e Calendário
- **Agendamento Automático**:
  - Jobs assíncronos para publicação/remoção automática
  - Fila de tarefas com prioridade por data/hora
  - Sistema de retry para falhas de publicação
  - Notificações de confirmação via email/sistema
- **Interface de Calendário**:
  - Visualização mensal/semanal/diária
  - Drag & drop para reagendamento
  - Códigos de cor por status (rascunho, agendado, publicado)
  - Filtros por autor, categoria e tipo de ação

### Analytics e Métricas Avançadas
- **Coleta de Dados em Tempo Real**:
  - Tracking de visualizações com IP e user-agent
  - Registro de tempo de permanência na página
  - Análise de origem do tráfego (direto, compartilhado, busca)
  - Métricas de engajamento (scroll, cliques em links)
- **Processamento de Dados**:
  - Jobs noturnos para agregação de métricas
  - Cálculo de tendências e padrões de consumo
  - Geração de relatórios automatizados
  - Alertas de performance anômala

### Sistema de Interação Social Técnico
- **Sistema de Likes**:
  - Endpoint REST para curtir/descurtir
  - Validação de usuário autenticado
  - Prevenção de múltiplos likes do mesmo usuário
  - Atualização em tempo real via WebSockets
- **Sistema de Compartilhamento**:
  - **Email**: Integração com sistema de filas de email
  - **WhatsApp**: Deep links com preview automático
  - **Link**: Geração de URLs com tracking parameters
  - **Redes Sociais**: Meta tags Open Graph e Twitter Cards

### Fluxo de Trabalho Técnico

#### Criação e Programação de Notícias
1. **Interface de Criação**:
   - **Editor de Texto Rico (WYSIWYG)**: TinyMCE Community Edition (gratuito)
     - **Formatação de Texto**: Negrito, itálico, sublinhado, tachado
     - **Estrutura de Conteúdo**: Títulos (H1-H6), parágrafos, listas ordenadas/não ordenadas
     - **Alinhamento**: Esquerda, centro, direita, justificado
     - **Cores**: Seletor de cor para texto e fundo
     - **Links**: Inserção e edição de links internos e externos
     - **Tabelas**: Criação e edição de tabelas com formatação
     - **Mídia**: Upload e inserção de imagens com redimensionamento
     - **Código**: Inserção de blocos de código com syntax highlighting
     - **Símbolos**: Inserção de caracteres especiais e emojis
     - **Desfazer/Refazer**: Histórico completo de alterações
     - **Buscar/Substituir**: Ferramenta de busca e substituição de texto
     - **Contagem de Palavras**: Contador em tempo real
     - **Modo Tela Cheia**: Editor expandido para melhor experiência
     - **Preview**: Visualização em tempo real do conteúdo formatado
     - **Responsivo**: Interface adaptável para diferentes tamanhos de tela
   - **Configuração do TinyMCE**:
     ```javascript
     tinymce.init({
       selector: '#news-content',
       height: 500,
       menubar: true,
       plugins: [
         'advlist', 'autolink', 'lists', 'link', 'image', 'charmap', 'preview',
         'anchor', 'searchreplace', 'visualblocks', 'code', 'fullscreen',
         'insertdatetime', 'media', 'table', 'help', 'wordcount', 'emoticons'
       ],
       toolbar: 'undo redo | blocks | bold italic underline strikethrough | ' +
                'alignleft aligncenter alignright alignjustify | ' +
                'bullist numlist outdent indent | removeformat | ' +
                'forecolor backcolor | link image media | ' +
                'table tabledelete | tableprops tablerowprops tablecellprops | ' +
                'tableinsertrowbefore tableinsertrowafter tabledeleterow | ' +
                'tableinsertcolbefore tableinsertcolafter tabledeletecol | ' +
                'code preview fullscreen | emoticons charmap | help',
       content_style: 'body { font-family: Arial, sans-serif; font-size: 14px; }',
       language: 'pt_BR',
       branding: false,
       promotion: false,
       image_upload_handler: function (blobInfo, success, failure) {
         // Integração com upload de imagens do Laravel
         const formData = new FormData();
         formData.append('image', blobInfo.blob(), blobInfo.filename());
         
         fetch('/admin/news/upload-image', {
           method: 'POST',
           body: formData,
           headers: {
             'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
           }
         })
         .then(response => response.json())
         .then(result => {
           if (result.success) {
             success(result.url);
           } else {
             failure('Erro no upload: ' + result.message);
           }
         })
         .catch(() => failure('Erro de conexão'));
       },
       setup: function (editor) {
         editor.on('change', function () {
           // Auto-save do conteúdo
           const content = editor.getContent();
           localStorage.setItem('news_draft_' + newsId, content);
         });
       }
     });
     ```
   - **Funcionalidades Avançadas**:
     - **Auto-save**: Salvamento automático a cada 30 segundos
     - **Recuperação de Rascunho**: Restauração automática em caso de perda de sessão
     - **Validação de Conteúdo**: Verificação de HTML válido antes da publicação
     - **Sanitização**: Limpeza automática de código malicioso
     - **Compressão de Imagens**: Otimização automática de imagens inseridas
     - **SEO Helper**: Sugestões automáticas para otimização de conteúdo

2. **Sistema de Gerenciamento de Imagens**:
   - **Upload Múltiplo de Imagens**:
     - **Interface Drag & Drop**: Área de arrastar e soltar para múltiplas imagens
     - **Seleção em Lote**: Upload simultâneo de até 20 imagens
     - **Formatos Suportados**: JPG, PNG, WebP, GIF (máx. 10MB por imagem)
     - **Preview Instantâneo**: Visualização imediata das imagens selecionadas
     - **Barra de Progresso**: Indicador visual do progresso do upload
     - **Validação Automática**: Verificação de formato, tamanho e dimensões
     - **Compressão Inteligente**: Redução automática de tamanho mantendo qualidade
     - **Geração de Thumbnails**: Criação automática de miniaturas em múltiplos tamanhos
   
   - **Galeria de Imagens da Notícia**:
     - **Organização Visual**: Grid responsivo com thumbnails
     - **Reordenação**: Drag & drop para alterar ordem das imagens
     - **Imagem Principal**: Seleção da imagem de destaque da notícia
     - **Legendas Individuais**: Campo de texto para cada imagem
     - **Alt Text**: Descrição alternativa para acessibilidade
     - **Créditos**: Campo para atribuição de direitos autorais
     - **Tags**: Sistema de marcação para organização
     - **Filtros**: Busca por nome, data, tags ou tipo
   
   - **Editor de Imagens Integrado**:
     - **Crop Tool**: Ferramenta de recorte com proporções predefinidas
     - **Redimensionamento**: Ajuste de dimensões mantendo proporção
     - **Filtros**: Brilho, contraste, saturação, nitidez
     - **Rotação**: Giro em 90°, 180°, 270° e rotação livre
     - **Flip**: Espelhamento horizontal e vertical
     - **Texto sobre Imagem**: Inserção de texto com fontes e cores
     - **Formas**: Adição de retângulos, círculos e setas
     - **Marca d'água**: Aplicação automática de logo do sindicato
     - **Histórico**: Desfazer/refazer alterações
     - **Comparação**: Visualização antes/depois das edições
   
   - **Carrossel de Imagens**:
     - **Configuração Flexível**: Definição de quantas imagens exibir
     - **Transições**: Fade, slide, zoom com velocidade configurável
     - **Navegação**: Setas, dots, thumbnails e swipe touch
     - **Autoplay**: Reprodução automática com pausa configurável
     - **Responsivo**: Adaptação automática para mobile e desktop
     - **Lazy Loading**: Carregamento sob demanda para performance
     - **Fullscreen**: Modo tela cheia com zoom
     - **Compartilhamento**: Botões para redes sociais em cada imagem

3. **Estrutura de Conteúdo da Notícia**:
   - **Campos Obrigatórios**:
     - **Título**: Campo de texto com contador de caracteres (máx. 120)
     - **Resumo/Chamada**: Textarea com formatação básica (máx. 300 caracteres)
       - **Formatação**: Negrito, itálico, links
       - **Preview**: Visualização em tempo real
       - **SEO Score**: Indicador de otimização para mecanismos de busca
       - **Contagem de Caracteres**: Indicador visual com cores (verde/amarelo/vermelho)
     - **Texto Completo**: Editor TinyMCE com todas as funcionalidades
     - **Categoria**: Seleção obrigatória de categoria
     - **Status**: Rascunho, Revisão, Publicado, Arquivado
   
   - **Campos Opcionais**:
     - **Subtítulo**: Campo adicional para complementar o título
     - **Tags**: Sistema de marcação para organização e busca
     - **Data de Publicação**: Agendamento de publicação futura
     - **Data de Expiração**: Remoção automática após data específica
     - **Autor**: Seleção do autor da notícia
     - **Fonte**: Referência da fonte original da notícia
     - **Link Externo**: URL para matéria completa em site externo
   
   - **Configurações de SEO**:
     - **Meta Título**: Título otimizado para mecanismos de busca
     - **Meta Descrição**: Descrição para resultados de busca
     - **URL Amigável**: Slug personalizado baseado no título
     - **Palavras-chave**: Tags para otimização de busca
     - **Open Graph**: Configuração para compartilhamento em redes sociais
     - **Schema Markup**: Estruturação de dados para Google

4. **Integração de Imagens no Corpo do Texto**:
   - **Inserção Inline**: Imagens diretamente no fluxo do texto
     - **Alinhamento**: Esquerda, centro, direita, justificado
     - **Quebra de Texto**: Texto ao redor da imagem
     - **Margens**: Espaçamento configurável
     - **Bordas**: Estilos de borda personalizáveis
     - **Sombras**: Efeitos de sombra para destaque
   
   - **Galerias Inline**: Inserção de galerias no meio do texto
     - **Layout Grid**: Disposição em grade 2x2, 3x3, etc.
     - **Slideshow**: Carrossel integrado no texto
     - **Lightbox**: Visualização ampliada ao clicar
     - **Legendas Coletivas**: Descrição para toda a galeria
   
   - **Imagens Responsivas**: Adaptação automática para diferentes dispositivos
     - **Breakpoints**: Diferentes tamanhos para mobile, tablet, desktop
     - **WebP Support**: Formato otimizado com fallback para JPG/PNG
     - **Lazy Loading**: Carregamento progressivo conforme scroll
     - **CDN Integration**: Distribuição via Content Delivery Network

5. **Configuração Técnica do Sistema de Imagens**:
   - **Estrutura de Armazenamento**:
     ```
     storage/app/public/news/
     ├── originals/          # Imagens originais
     ├── thumbnails/         # Miniaturas (150x150, 300x300)
     ├── medium/             # Tamanho médio (800x600)
     ├── large/              # Tamanho grande (1200x900)
     └── carousel/           # Imagens otimizadas para carrossel
     ```
   
   - **Processamento de Imagens (Laravel)**:
     ```php
     // Configuração de upload e processamento
     'image_processing' => [
         'driver' => 'imagick', // ou 'gd'
         'quality' => 85,
         'formats' => ['jpg', 'png', 'webp', 'gif'],
         'max_size' => 10240, // 10MB
         'thumbnails' => [
             'small' => [150, 150],
             'medium' => [300, 300],
             'large' => [800, 600],
             'carousel' => [1200, 800]
         ],
         'watermark' => [
             'enabled' => true,
             'image' => 'watermark.png',
             'position' => 'bottom-right',
             'opacity' => 0.7
         ]
     ]
     ```
   
   - **API Endpoints para Imagens**:
     ```php
     // routes/web.php
     Route::prefix('admin/news')->group(function () {
         Route::post('upload-image', [NewsController::class, 'uploadImage']);
         Route::post('upload-multiple', [NewsController::class, 'uploadMultiple']);
         Route::post('reorder-images', [NewsController::class, 'reorderImages']);
         Route::delete('delete-image/{id}', [NewsController::class, 'deleteImage']);
         Route::post('edit-image/{id}', [NewsController::class, 'editImage']);
         Route::get('gallery/{newsId}', [NewsController::class, 'getGallery']);
     });
     ```

6. **Interface Frontend do Carrossel**:
   - **Configuração JavaScript**:
     ```javascript
     // Inicialização do carrossel Swiper.js
     const newsCarousel = new Swiper('.news-carousel', {
       slidesPerView: 1,
       spaceBetween: 10,
       loop: true,
       autoplay: {
         delay: 5000,
         disableOnInteraction: false,
       },
       pagination: {
         el: '.swiper-pagination',
         clickable: true,
       },
       navigation: {
         nextEl: '.swiper-button-next',
         prevEl: '.swiper-button-prev',
       },
       breakpoints: {
         640: {
           slidesPerView: 2,
           spaceBetween: 20,
         },
         768: {
           slidesPerView: 3,
           spaceBetween: 30,
         },
         1024: {
           slidesPerView: 4,
           spaceBetween: 40,
         },
       },
       lazy: {
         loadPrevNext: true,
       },
       zoom: {
         maxRatio: 3,
       }
     });
     ```
   
   - **Template HTML do Carrossel**:
     ```html
     <div class="news-carousel-container">
       <div class="swiper news-carousel">
         <div class="swiper-wrapper">
           @foreach($news->images as $image)
           <div class="swiper-slide">
             <div class="image-container">
               <img data-src="{{ $image->url }}" 
                    class="swiper-lazy" 
                    alt="{{ $image->alt_text }}">
               <div class="swiper-lazy-preloader"></div>
               @if($image->caption)
               <div class="image-caption">{{ $image->caption }}</div>
               @endif
               <div class="image-overlay">
                 <button class="btn-fullscreen" data-image="{{ $image->large_url }}">
                   <i class="fas fa-expand"></i>
                 </button>
                 <button class="btn-share" data-image="{{ $image->url }}">
                   <i class="fas fa-share"></i>
                 </button>
               </div>
             </div>
           </div>
           @endforeach
         </div>
         <div class="swiper-pagination"></div>
         <div class="swiper-button-next"></div>
         <div class="swiper-button-prev"></div>
       </div>
     </div>
     ```
     - **Sanitização**: Limpeza automática de código malicioso
     - **Compressão de Imagens**: Otimização automática de imagens inseridas
     - **SEO Helper**: Sugestões automáticas para otimização de SEO
   - Upload de mídia com compressão automática
   - Configuração de SEO (meta title, description, keywords)
   - Seleção de data/hora de publicação e remoção
2. **Validação e Aprovação**:
   - Sistema de workflow baseado em níveis de usuário
   - Notificações automáticas para aprovadores
   - Histórico de alterações e comentários
3. **Publicação Automática**:
   - Job scheduler verifica notícias agendadas
   - Atualização de status e índices de busca
   - Notificações de confirmação
   - Invalidação de cache relacionado

#### Coleta e Processamento de Analytics
1. **Registro de Visualização**:
   - Middleware captura dados da requisição
   - Validação de visualização única por sessão
   - Armazenamento em batch para otimização
2. **Processamento de Métricas**:
   - Jobs assíncronos agregam dados por período
   - Cálculo de métricas derivadas (taxa de engajamento, tempo médio)
   - Atualização de rankings e tendências
3. **Geração de Relatórios**:
   - Relatórios automáticos diários/semanais/mensais
   - Exportação em múltiplos formatos (PDF, Excel, CSV)
   - Dashboards interativos com filtros dinâmicos

#### Sistema de Interação e Engajamento
1. **Processamento de Likes**:
   - Validação de autenticação via middleware JWT
   - Atualização atômica de contadores
   - Notificação assíncrona para autores
   - Atualização de rankings de popularidade
2. **Processamento de Compartilhamentos**:
   - Registro de compartilhamento com origem
   - Geração de links únicos para tracking
   - Atualização de métricas de viralização
   - Análise de padrões de compartilhamento

### Monitoramento e Performance
- **Métricas de Sistema**:
  - Tempo de resposta das páginas de notícias
  - Taxa de conversão (visualização → engajamento)
  - Performance do sistema de cache
  - Utilização de recursos do servidor
- **Alertas Automáticos**:
  - Notícias com performance anômala
  - Falhas no sistema de agendamento
  - Picos de tráfego inesperados
  - Problemas de performance do banco de dados

### Sistema de Categorias e Acesso Público

#### Estrutura de Categorias
- **Tabela `news_categories`**:
  - `id` (chave primária)
  - `name` (nome da categoria)
  - `slug` (URL amigável)
  - `description` (descrição)
  - `color` (cor hexadecimal)
  - `icon` (ícone Font Awesome)
  - `is_active` (status ativo/inativo)
  - `sort_order` (ordem de exibição)
  - `created_at`, `updated_at`

#### Relacionamento com Notícias
- **Campo `category_id`** na tabela `news`
- **Relacionamento obrigatório**: Toda notícia deve ter uma categoria
- **Cascade delete**: Categoria não pode ser excluída se tiver notícias
- **Migração automática**: Sistema para migrar notícias entre categorias

#### API Pública para Usuários Não Logados
- **Endpoint `/api/public/news`**:
  - Listagem paginada de notícias publicadas
  - Filtro por categoria via query parameter
  - Ordenação por data, visualizações, likes
  - Busca por título e conteúdo
  - Cache Redis com TTL de 5 minutos

- **Endpoint `/api/public/news/{id}`**:
  - Visualização individual de notícia
  - Incremento automático do contador de visualizações
  - Dados da categoria associada
  - Botões de compartilhamento (sem autenticação)

- **Endpoint `/api/public/categories`**:
  - Lista de categorias ativas
  - Contador de notícias por categoria
  - Dados para navegação (nome, cor, ícone)
  - Cache Redis com TTL de 30 minutos

#### Interface Pública
- **Página `/noticias`**:
  - Layout responsivo sem necessidade de login
  - Navegação por categorias na sidebar
  - Grid de notícias com paginação
  - Filtros e busca em tempo real
  - SEO otimizado com meta tags dinâmicas

- **Página `/noticias/categoria/{slug}`**:
  - Listagem filtrada por categoria
  - Breadcrumb de navegação
  - Informações da categoria (nome, descrição)
  - Contadores de notícias

- **Página `/noticias/{id}`**:
  - Visualização completa da notícia
  - Informações da categoria
  - Botões de compartilhamento
  - Contador de visualizações
  - Sugestões de notícias relacionadas

#### Cache e Performance
- **Cache de categorias**: Redis com TTL de 30 minutos
- **Cache de listagens**: Redis com TTL de 5 minutos
- **Cache de contadores**: Atualização assíncrona via jobs
- **Otimização de queries**: Eager loading de relacionamentos
- **CDN**: Imagens e assets estáticos via CDN

### Sistema de Validação de CPF

#### Validações Backend (Laravel)
- **Regra de validação customizada**: `ValidCpf` rule
- **Algoritmo de validação**:
  - Verificação de formato (11 dígitos numéricos)
  - Cálculo dos dígitos verificadores
  - Rejeição de CPFs inválidos conhecidos (000.000.000-00, 111.111.111-11, etc.)
  - Validação de sequências numéricas
- **Unicidade no banco**: Constraint UNIQUE na coluna `cpf`
- **Normalização**: Remoção automática de pontos e hífens antes do armazenamento
- **Indexação**: Índice na coluna `cpf` para otimização de buscas

#### Validações Frontend (Vue.js)
- **Máscara automática**: Formatação XXX.XXX.XXX-XX durante digitação
- **Validação em tempo real**: Feedback visual imediato
- **Biblioteca de validação**: Integração com biblioteca de CPF JavaScript
- **Mensagens de erro**: Feedback específico para cada tipo de erro
- **Prevenção de submit**: Bloqueio do formulário com CPF inválido

#### Segurança e Privacidade
- **Criptografia**: CPF armazenado com hash adicional para buscas
- **Logs de auditoria**: Registro de tentativas de cadastro com CPF duplicado
- **Rate limiting**: Proteção contra tentativas automatizadas
- **LGPD compliance**: Tratamento adequado de dados pessoais sensíveis

#### API Endpoints
- **POST /api/validate-cpf**: Validação de CPF em tempo real

### Sistema de Convites

#### Estrutura de Tabelas

**Tabela `invitations`**:
- `id` (Primary Key)
- `inviter_id` (Foreign Key para users)
- `inviter_type` (enum: fundador, associado, usuario)
- `invited_name` (varchar)
- `invited_email` (varchar)
- `invitation_token` (varchar, unique)
- `custom_message` (text, nullable)
- `status` (enum: pending, sent, delivered, accepted, expired, cancelled)
- `sent_at` (timestamp, nullable)
- `accepted_at` (timestamp, nullable)
- `expires_at` (timestamp)
- `batch_id` (Foreign Key para invitation_batches, nullable)
- `created_at` (timestamp)
- `updated_at` (timestamp)

**Tabela `invitation_batches`**:
- `id` (Primary Key)
- `creator_id` (Foreign Key para users)
- `filename` (varchar)
- `total_invitations` (integer)
- `successful_invitations` (integer)
- `failed_invitations` (integer)
- `status` (enum: processing, completed, failed)
- `error_log` (json, nullable)
- `created_at` (timestamp)
- `updated_at` (timestamp)

**Tabela `invitation_analytics`**:
- `id` (Primary Key)
- `user_id` (Foreign Key para users)
- `period_type` (enum: daily, weekly, monthly)
- `period_date` (date)
- `invitations_sent` (integer)
- `invitations_accepted` (integer)
- `conversion_rate` (decimal)
- `created_at` (timestamp)
- `updated_at` (timestamp)

**Tabela `invitation_limits`**:
- `id` (Primary Key)
- `user_level` (enum: fundador, associado, usuario)
- `period_type` (enum: daily, weekly, monthly)
- `max_invitations` (integer)
- `is_active` (boolean)
- `created_at` (timestamp)
- `updated_at` (timestamp)

#### Sistema de Filas de Email

**Filas Específicas**:
- **invitation-high**: Convites do fundador (prioridade alta)
- **invitation-medium**: Convites de associados (prioridade média)
- **invitation-low**: Convites de usuários (prioridade baixa)
- **invitation-batch**: Processamento de lotes CSV (prioridade média)

**Workers Dedicados**:
- Worker para cada fila com configuração de retry
- Processamento paralelo com limite de tentativas
- Logs detalhados de sucesso/falha
- Notificação automática de falhas críticas

#### Validações e Regras de Negócio

**Validações de Email**:
- Formato válido de email
- Verificação de domínio existente
- Blacklist de emails temporários
- Prevenção de auto-convite
- Verificação de email já cadastrado no sistema

**Limites de Convites**:
- **Fundador**: Ilimitado
- **Associado**: Configurável (padrão: 50/mês)
- **Usuário**: Configurável (padrão: 10/mês)
- Reset automático por período
- Notificação quando próximo do limite

**Validação de CSV**:
- Formato obrigatório: nome, email
- Máximo de 1000 registros por arquivo
- Validação de encoding (UTF-8)
- Detecção de duplicatas no arquivo
- Relatório de erros linha por linha

#### API Endpoints

**Convites Individuais**:
- **POST /api/invitations**: Enviar convite individual
- **GET /api/invitations**: Listar convites enviados
- **PUT /api/invitations/{id}/resend**: Reenviar convite
- **DELETE /api/invitations/{id}**: Cancelar convite

**Convites em Lote**:
- **POST /api/invitations/batch**: Upload de arquivo CSV
- **GET /api/invitations/batches**: Listar lotes processados
- **GET /api/invitations/batches/{id}**: Detalhes do lote
- **GET /api/invitations/batches/{id}/download-errors**: Download de relatório de erros

**Estatísticas**:
- **GET /api/invitations/analytics**: Estatísticas pessoais
- **GET /api/invitations/analytics/global**: Estatísticas globais (fundador)
- **GET /api/invitations/limits**: Limites atuais do usuário

**Aceitação de Convites**:
- **GET /api/invitations/accept/{token}**: Página de aceitação
- **POST /api/invitations/accept/{token}**: Processar aceitação

#### Sistema de Recompensas

**Programa de Indicações**:
- Pontuação por convite aceito
- Bônus por conversão para associado
- Ranking mensal de indicadores
- Recompensas configuráveis pelo fundador
- Histórico de recompensas recebidas

**Métricas de Gamificação**:
- Badges por marcos de indicações
- Níveis de indicador (Bronze, Prata, Ouro)
- Desafios mensais de indicações
- Leaderboard público opcional

#### Segurança e Privacidade

**Proteções Anti-Spam**:
- Rate limiting por IP e usuário
- Captcha para usuários com muitos convites
- Detecção de padrões suspeitos
- Blacklist automática de emails problemáticos

**Privacidade de Dados**:
- Criptografia de emails convidados
- Expiração automática de tokens
- Logs com retenção limitada
- Compliance com LGPD
- Opt-out automático para emails rejeitados

**Auditoria**:
- Log de todas as ações de convite
- Rastreamento de origem dos registros
- Relatórios de compliance
- Monitoramento de uso abusivo

**Validação de CPF**:
- **POST `/api/validate-cpf`**: Validação de CPF em tempo real
- **Resposta**: `{"valid": true/false, "message": "...", "formatted": "XXX.XXX.XXX-XX"}`
- **Middleware**: Validação automática em rotas de registro e atualização

Este documento serve como base para o desenvolvimento e manutenção do sistema, devendo ser atualizado conforme evolução dos requisitos.
