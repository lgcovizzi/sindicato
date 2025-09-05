<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\UserPreference;
use App\Http\Requests\UserPreference\UpdatePreferenceRequest;
use App\Services\UserPreferenceService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;

class UserPreferenceController extends Controller
{
    protected $preferenceService;

    public function __construct(UserPreferenceService $preferenceService)
    {
        $this->preferenceService = $preferenceService;
        $this->middleware('auth:api');
    }

    /**
     * Get user preferences.
     */
    public function index(): JsonResponse
    {
        try {
            $user = Auth::user();
            $preferences = $this->preferenceService->getUserPreferences($user->id);

            return response()->json([
                'success' => true,
                'message' => 'Preferências recuperadas com sucesso',
                'data' => $preferences,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao recuperar preferências',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Update user preferences.
     */
    public function update(UpdatePreferenceRequest $request): JsonResponse
    {
        try {
            $user = Auth::user();
            $data = $request->validated();
            
            $preferences = $this->preferenceService->updatePreferences($user->id, $data);

            return response()->json([
                'success' => true,
                'message' => 'Preferências atualizadas com sucesso',
                'data' => $preferences,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao atualizar preferências',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Update theme preference.
     */
    public function updateTheme(Request $request): JsonResponse
    {
        $request->validate([
            'theme' => 'required|in:light,dark,system',
        ]);

        try {
            $user = Auth::user();
            $preferences = $this->preferenceService->updateTheme($user->id, $request->theme);

            return response()->json([
                'success' => true,
                'message' => 'Tema atualizado com sucesso',
                'data' => [
                    'theme' => $preferences->theme,
                    'updated_at' => $preferences->updated_at,
                ],
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao atualizar tema',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Update notification preferences.
     */
    public function updateNotifications(Request $request): JsonResponse
    {
        $request->validate([
            'email_notifications' => 'boolean',
            'push_notifications' => 'boolean',
            'sms_notifications' => 'boolean',
            'voting_notifications' => 'boolean',
            'news_notifications' => 'boolean',
            'convenio_notifications' => 'boolean',
            'system_notifications' => 'boolean',
        ]);

        try {
            $user = Auth::user();
            $preferences = $this->preferenceService->updateNotificationSettings(
                $user->id,
                $request->only([
                    'email_notifications',
                    'push_notifications',
                    'sms_notifications',
                    'voting_notifications',
                    'news_notifications',
                    'convenio_notifications',
                    'system_notifications',
                ])
            );

            return response()->json([
                'success' => true,
                'message' => 'Configurações de notificação atualizadas com sucesso',
                'data' => [
                    'notifications' => [
                        'email_notifications' => $preferences->email_notifications,
                        'push_notifications' => $preferences->push_notifications,
                        'sms_notifications' => $preferences->sms_notifications,
                        'voting_notifications' => $preferences->voting_notifications,
                        'news_notifications' => $preferences->news_notifications,
                        'convenio_notifications' => $preferences->convenio_notifications,
                        'system_notifications' => $preferences->system_notifications,
                    ],
                    'updated_at' => $preferences->updated_at,
                ],
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao atualizar configurações de notificação',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Update interface density.
     */
    public function updateDensity(Request $request): JsonResponse
    {
        $request->validate([
            'density' => 'required|in:compact,normal,spacious',
        ]);

        try {
            $user = Auth::user();
            $preferences = $this->preferenceService->updateDensity($user->id, $request->density);

            return response()->json([
                'success' => true,
                'message' => 'Densidade da interface atualizada com sucesso',
                'data' => [
                    'density' => $preferences->density,
                    'updated_at' => $preferences->updated_at,
                ],
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao atualizar densidade da interface',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Reset preferences to default.
     */
    public function reset(): JsonResponse
    {
        try {
            $user = Auth::user();
            $preferences = $this->preferenceService->resetToDefaults($user->id);

            return response()->json([
                'success' => true,
                'message' => 'Preferências resetadas para o padrão',
                'data' => $preferences,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao resetar preferências',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Sync preferences across devices.
     */
    public function sync(Request $request): JsonResponse
    {
        $request->validate([
            'device_id' => 'required|string',
            'platform' => 'required|in:web,android,ios,desktop',
        ]);

        try {
            $user = Auth::user();
            $preferences = $this->preferenceService->syncPreferences(
                $user->id,
                $request->device_id,
                $request->platform
            );

            return response()->json([
                'success' => true,
                'message' => 'Preferências sincronizadas com sucesso',
                'data' => $preferences,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao sincronizar preferências',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get default preferences.
     */
    public function defaults(): JsonResponse
    {
        try {
            $defaults = UserPreference::getDefaults();

            return response()->json([
                'success' => true,
                'message' => 'Preferências padrão recuperadas com sucesso',
                'data' => $defaults,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao recuperar preferências padrão',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}