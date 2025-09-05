<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\SystemNotification;
use App\Models\NotificationLog;
use App\Http\Requests\Notification\CreateNotificationRequest;
use App\Http\Requests\Notification\UpdateNotificationRequest;
use App\Services\NotificationService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;

class NotificationController extends Controller
{
    protected $notificationService;

    public function __construct(NotificationService $notificationService)
    {
        $this->notificationService = $notificationService;
        $this->middleware('auth:api');
    }

    /**
     * Get user notifications.
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $user = Auth::user();
            $perPage = $request->get('per_page', 15);
            $type = $request->get('type');
            $status = $request->get('status');
            $priority = $request->get('priority');

            $notifications = $this->notificationService->getUserNotifications(
                $user->id,
                $perPage,
                $type,
                $status,
                $priority
            );

            return response()->json([
                'success' => true,
                'message' => 'Notificações recuperadas com sucesso',
                'data' => $notifications,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao recuperar notificações',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get system notifications (public).
     */
    public function system(Request $request): JsonResponse
    {
        try {
            $perPage = $request->get('per_page', 15);
            $type = $request->get('type');
            $priority = $request->get('priority');

            $notifications = SystemNotification::active()
                ->public()
                ->when($type, fn($q) => $q->byType($type))
                ->when($priority, fn($q) => $q->byPriority($priority))
                ->latest()
                ->paginate($perPage);

            return response()->json([
                'success' => true,
                'message' => 'Notificações do sistema recuperadas com sucesso',
                'data' => $notifications,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao recuperar notificações do sistema',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Mark notification as read.
     */
    public function markAsRead(Request $request, $id): JsonResponse
    {
        try {
            $user = Auth::user();
            $result = $this->notificationService->markAsRead($user->id, $id);

            if (!$result) {
                return response()->json([
                    'success' => false,
                    'message' => 'Notificação não encontrada ou já lida',
                ], 404);
            }

            return response()->json([
                'success' => true,
                'message' => 'Notificação marcada como lida',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao marcar notificação como lida',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Mark all notifications as read.
     */
    public function markAllAsRead(): JsonResponse
    {
        try {
            $user = Auth::user();
            $count = $this->notificationService->markAllAsRead($user->id);

            return response()->json([
                'success' => true,
                'message' => "$count notificações marcadas como lidas",
                'data' => ['count' => $count],
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao marcar todas as notificações como lidas',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Delete notification.
     */
    public function destroy($id): JsonResponse
    {
        try {
            $user = Auth::user();
            $result = $this->notificationService->deleteNotification($user->id, $id);

            if (!$result) {
                return response()->json([
                    'success' => false,
                    'message' => 'Notificação não encontrada',
                ], 404);
            }

            return response()->json([
                'success' => true,
                'message' => 'Notificação excluída com sucesso',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao excluir notificação',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get unread notifications count.
     */
    public function unreadCount(): JsonResponse
    {
        try {
            $user = Auth::user();
            $count = $this->notificationService->getUnreadCount($user->id);

            return response()->json([
                'success' => true,
                'data' => ['count' => $count],
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao recuperar contagem de notificações não lidas',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Send toast notification.
     */
    public function sendToast(Request $request): JsonResponse
    {
        $request->validate([
            'message' => 'required|string|max:255',
            'type' => 'required|in:success,error,warning,info',
            'duration' => 'integer|min:1000|max:10000',
            'action' => 'nullable|array',
            'action.text' => 'required_with:action|string|max:50',
            'action.url' => 'required_with:action|url',
        ]);

        try {
            $user = Auth::user();
            $result = $this->notificationService->sendToast(
                $user->id,
                $request->message,
                $request->type,
                $request->duration ?? 5000,
                $request->action
            );

            return response()->json([
                'success' => true,
                'message' => 'Toast enviado com sucesso',
                'data' => $result,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao enviar toast',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get notification statistics.
     */
    public function statistics(): JsonResponse
    {
        try {
            $user = Auth::user();
            $stats = $this->notificationService->getUserStatistics($user->id);

            return response()->json([
                'success' => true,
                'message' => 'Estatísticas recuperadas com sucesso',
                'data' => $stats,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao recuperar estatísticas',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Create system notification (admin only).
     */
    public function create(CreateNotificationRequest $request): JsonResponse
    {
        try {
            $user = Auth::user();
            
            // Check if user has permission to create system notifications
            if (!$user->hasRole('admin') && !$user->hasPermissionTo('create_notifications')) {
                return response()->json([
                    'success' => false,
                    'message' => 'Acesso negado',
                ], 403);
            }

            $data = $request->validated();
            $notification = $this->notificationService->createSystemNotification($data);

            return response()->json([
                'success' => true,
                'message' => 'Notificação do sistema criada com sucesso',
                'data' => $notification,
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao criar notificação do sistema',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Update system notification (admin only).
     */
    public function update(UpdateNotificationRequest $request, $id): JsonResponse
    {
        try {
            $user = Auth::user();
            
            // Check if user has permission to update system notifications
            if (!$user->hasRole('admin') && !$user->hasPermissionTo('update_notifications')) {
                return response()->json([
                    'success' => false,
                    'message' => 'Acesso negado',
                ], 403);
            }

            $data = $request->validated();
            $notification = $this->notificationService->updateSystemNotification($id, $data);

            if (!$notification) {
                return response()->json([
                    'success' => false,
                    'message' => 'Notificação não encontrada',
                ], 404);
            }

            return response()->json([
                'success' => true,
                'message' => 'Notificação do sistema atualizada com sucesso',
                'data' => $notification,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao atualizar notificação do sistema',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get notification logs (admin only).
     */
    public function logs(Request $request): JsonResponse
    {
        try {
            $user = Auth::user();
            
            // Check if user has permission to view notification logs
            if (!$user->hasRole('admin') && !$user->hasPermissionTo('view_notification_logs')) {
                return response()->json([
                    'success' => false,
                    'message' => 'Acesso negado',
                ], 403);
            }

            $perPage = $request->get('per_page', 15);
            $channel = $request->get('channel');
            $status = $request->get('status');
            $platform = $request->get('platform');
            $userId = $request->get('user_id');

            $logs = NotificationLog::query()
                ->when($channel, fn($q) => $q->byChannel($channel))
                ->when($status, fn($q) => $q->byStatus($status))
                ->when($platform, fn($q) => $q->byPlatform($platform))
                ->when($userId, fn($q) => $q->where('user_id', $userId))
                ->with('user:id,name,email')
                ->latest()
                ->paginate($perPage);

            return response()->json([
                'success' => true,
                'message' => 'Logs de notificação recuperados com sucesso',
                'data' => $logs,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao recuperar logs de notificação',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}