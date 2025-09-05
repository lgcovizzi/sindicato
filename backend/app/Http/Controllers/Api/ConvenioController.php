<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Convenio;
use App\Http\Requests\Convenio\CreateConvenioRequest;
use App\Http\Requests\Convenio\UpdateConvenioRequest;
use App\Services\ConvenioService;
use App\Services\QRCodeService;
use App\Services\GeolocationService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;

class ConvenioController extends Controller
{
    protected $convenioService;
    protected $qrCodeService;
    protected $geolocationService;

    public function __construct(
        ConvenioService $convenioService,
        QRCodeService $qrCodeService,
        GeolocationService $geolocationService
    ) {
        $this->convenioService = $convenioService;
        $this->qrCodeService = $qrCodeService;
        $this->geolocationService = $geolocationService;
        $this->middleware('auth:api');
    }

    /**
     * Get all convenios with filters.
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $filters = $request->only([
                'category',
                'city',
                'state',
                'search',
                'status',
                'featured',
                'latitude',
                'longitude',
                'radius',
            ]);
            
            $perPage = $request->get('per_page', 15);
            $sortBy = $request->get('sort_by', 'name');
            $sortOrder = $request->get('sort_order', 'asc');

            // Cache key for this specific query
            $cacheKey = 'convenios_' . md5(serialize($filters) . $perPage . $sortBy . $sortOrder);
            
            $convenios = Cache::remember($cacheKey, 300, function () use ($filters, $perPage, $sortBy, $sortOrder) {
                return $this->convenioService->getConvenios($filters, $perPage, $sortBy, $sortOrder);
            });

            return response()->json([
                'success' => true,
                'message' => 'Convênios recuperados com sucesso',
                'data' => $convenios,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao recuperar convênios',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get convenio by ID.
     */
    public function show($id): JsonResponse
    {
        try {
            $convenio = Cache::remember("convenio_{$id}", 600, function () use ($id) {
                return $this->convenioService->getConvenioById($id);
            });

            if (!$convenio) {
                return response()->json([
                    'success' => false,
                    'message' => 'Convênio não encontrado',
                ], 404);
            }

            // Increment view count
            $this->convenioService->incrementViews($id);

            return response()->json([
                'success' => true,
                'message' => 'Convênio recuperado com sucesso',
                'data' => $convenio,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao recuperar convênio',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get nearby convenios based on user location.
     */
    public function nearby(Request $request): JsonResponse
    {
        $request->validate([
            'latitude' => 'required|numeric|between:-90,90',
            'longitude' => 'required|numeric|between:-180,180',
            'radius' => 'integer|min:1|max:100', // km
        ]);

        try {
            $latitude = $request->latitude;
            $longitude = $request->longitude;
            $radius = $request->get('radius', 10); // Default 10km
            $perPage = $request->get('per_page', 15);

            $convenios = $this->geolocationService->getNearbyConvenios(
                $latitude,
                $longitude,
                $radius,
                $perPage
            );

            return response()->json([
                'success' => true,
                'message' => 'Convênios próximos recuperados com sucesso',
                'data' => $convenios,
                'meta' => [
                    'search_center' => [
                        'latitude' => $latitude,
                        'longitude' => $longitude,
                    ],
                    'radius_km' => $radius,
                ],
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao recuperar convênios próximos',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get featured convenios.
     */
    public function featured(): JsonResponse
    {
        try {
            $convenios = Cache::remember('featured_convenios', 1800, function () {
                return $this->convenioService->getFeaturedConvenios();
            });

            return response()->json([
                'success' => true,
                'message' => 'Convênios em destaque recuperados com sucesso',
                'data' => $convenios,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao recuperar convênios em destaque',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get convenio categories.
     */
    public function categories(): JsonResponse
    {
        try {
            $categories = Cache::remember('convenio_categories', 3600, function () {
                return $this->convenioService->getCategories();
            });

            return response()->json([
                'success' => true,
                'message' => 'Categorias recuperadas com sucesso',
                'data' => $categories,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao recuperar categorias',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Generate QR Code for convenio.
     */
    public function generateQRCode($id): JsonResponse
    {
        try {
            $user = Auth::user();
            $convenio = $this->convenioService->getConvenioById($id);

            if (!$convenio) {
                return response()->json([
                    'success' => false,
                    'message' => 'Convênio não encontrado',
                ], 404);
            }

            $qrCode = $this->qrCodeService->generateConvenioQRCode($convenio, $user);

            return response()->json([
                'success' => true,
                'message' => 'QR Code gerado com sucesso',
                'data' => [
                    'qr_code' => $qrCode['qr_code'],
                    'expires_at' => $qrCode['expires_at'],
                    'usage_instructions' => 'Apresente este QR Code no estabelecimento para utilizar o desconto.',
                ],
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao gerar QR Code',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Validate QR Code usage.
     */
    public function validateQRCode(Request $request): JsonResponse
    {
        $request->validate([
            'qr_code' => 'required|string',
            'convenio_id' => 'required|exists:convenios,id',
        ]);

        try {
            $result = $this->qrCodeService->validateQRCode(
                $request->qr_code,
                $request->convenio_id
            );

            if (!$result['valid']) {
                return response()->json([
                    'success' => false,
                    'message' => $result['message'],
                ], 400);
            }

            return response()->json([
                'success' => true,
                'message' => 'QR Code válido',
                'data' => $result['data'],
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao validar QR Code',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Mark convenio as favorite.
     */
    public function favorite($id): JsonResponse
    {
        try {
            $user = Auth::user();
            $result = $this->convenioService->toggleFavorite($user->id, $id);

            if (!$result) {
                return response()->json([
                    'success' => false,
                    'message' => 'Convênio não encontrado',
                ], 404);
            }

            return response()->json([
                'success' => true,
                'message' => $result['action'] === 'added' ? 'Convênio adicionado aos favoritos' : 'Convênio removido dos favoritos',
                'data' => [
                    'is_favorite' => $result['is_favorite'],
                    'action' => $result['action'],
                ],
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao gerenciar favorito',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get user's favorite convenios.
     */
    public function favorites(Request $request): JsonResponse
    {
        try {
            $user = Auth::user();
            $perPage = $request->get('per_page', 15);
            
            $favorites = $this->convenioService->getUserFavorites($user->id, $perPage);

            return response()->json([
                'success' => true,
                'message' => 'Convênios favoritos recuperados com sucesso',
                'data' => $favorites,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao recuperar convênios favoritos',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Search convenios.
     */
    public function search(Request $request): JsonResponse
    {
        $request->validate([
            'query' => 'required|string|min:2|max:100',
        ]);

        try {
            $query = $request->query;
            $perPage = $request->get('per_page', 15);
            $filters = $request->only(['category', 'city', 'state']);

            $results = $this->convenioService->searchConvenios($query, $filters, $perPage);

            return response()->json([
                'success' => true,
                'message' => 'Busca realizada com sucesso',
                'data' => $results,
                'meta' => [
                    'query' => $query,
                    'total_results' => $results->total(),
                ],
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao realizar busca',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Create convenio (admin only).
     */
    public function store(CreateConvenioRequest $request): JsonResponse
    {
        try {
            $user = Auth::user();
            
            if (!$user->hasRole('admin') && !$user->hasPermissionTo('create_convenios')) {
                return response()->json([
                    'success' => false,
                    'message' => 'Acesso negado',
                ], 403);
            }

            $data = $request->validated();
            $convenio = $this->convenioService->createConvenio($data);

            // Clear cache
            Cache::tags(['convenios'])->flush();

            return response()->json([
                'success' => true,
                'message' => 'Convênio criado com sucesso',
                'data' => $convenio,
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao criar convênio',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Update convenio (admin only).
     */
    public function update(UpdateConvenioRequest $request, $id): JsonResponse
    {
        try {
            $user = Auth::user();
            
            if (!$user->hasRole('admin') && !$user->hasPermissionTo('update_convenios')) {
                return response()->json([
                    'success' => false,
                    'message' => 'Acesso negado',
                ], 403);
            }

            $data = $request->validated();
            $convenio = $this->convenioService->updateConvenio($id, $data);

            if (!$convenio) {
                return response()->json([
                    'success' => false,
                    'message' => 'Convênio não encontrado',
                ], 404);
            }

            // Clear cache
            Cache::tags(['convenios'])->flush();
            Cache::forget("convenio_{$id}");

            return response()->json([
                'success' => true,
                'message' => 'Convênio atualizado com sucesso',
                'data' => $convenio,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao atualizar convênio',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Delete convenio (admin only).
     */
    public function destroy($id): JsonResponse
    {
        try {
            $user = Auth::user();
            
            if (!$user->hasRole('admin') && !$user->hasPermissionTo('delete_convenios')) {
                return response()->json([
                    'success' => false,
                    'message' => 'Acesso negado',
                ], 403);
            }

            $result = $this->convenioService->deleteConvenio($id);

            if (!$result) {
                return response()->json([
                    'success' => false,
                    'message' => 'Convênio não encontrado',
                ], 404);
            }

            // Clear cache
            Cache::tags(['convenios'])->flush();
            Cache::forget("convenio_{$id}");

            return response()->json([
                'success' => true,
                'message' => 'Convênio excluído com sucesso',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao excluir convênio',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}