<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\News;
use App\Http\Requests\News\CreateNewsRequest;
use App\Http\Requests\News\UpdateNewsRequest;
use App\Services\NewsService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;

class NewsController extends Controller
{
    protected $newsService;

    public function __construct(NewsService $newsService)
    {
        $this->newsService = $newsService;
        $this->middleware('auth:api', ['except' => ['index', 'show', 'featured', 'categories']]);
    }

    /**
     * Get all news with filters.
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $filters = $request->only([
                'category',
                'status',
                'featured',
                'search',
                'author_id',
                'date_from',
                'date_to',
            ]);
            
            $perPage = $request->get('per_page', 15);
            $sortBy = $request->get('sort_by', 'published_at');
            $sortOrder = $request->get('sort_order', 'desc');

            // Cache key for this specific query
            $cacheKey = 'news_' . md5(serialize($filters) . $perPage . $sortBy . $sortOrder);
            
            $news = Cache::remember($cacheKey, 300, function () use ($filters, $perPage, $sortBy, $sortOrder) {
                return $this->newsService->getNews($filters, $perPage, $sortBy, $sortOrder);
            });

            return response()->json([
                'success' => true,
                'message' => 'Notícias recuperadas com sucesso',
                'data' => $news,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao recuperar notícias',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get news by ID or slug.
     */
    public function show($identifier): JsonResponse
    {
        try {
            $news = Cache::remember("news_{$identifier}", 600, function () use ($identifier) {
                return $this->newsService->getNewsByIdOrSlug($identifier);
            });

            if (!$news) {
                return response()->json([
                    'success' => false,
                    'message' => 'Notícia não encontrada',
                ], 404);
            }

            // Increment view count
            $this->newsService->incrementViews($news->id);

            return response()->json([
                'success' => true,
                'message' => 'Notícia recuperada com sucesso',
                'data' => $news,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao recuperar notícia',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get featured news.
     */
    public function featured(): JsonResponse
    {
        try {
            $news = Cache::remember('featured_news', 900, function () {
                return $this->newsService->getFeaturedNews();
            });

            return response()->json([
                'success' => true,
                'message' => 'Notícias em destaque recuperadas com sucesso',
                'data' => $news,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao recuperar notícias em destaque',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get latest news.
     */
    public function latest(Request $request): JsonResponse
    {
        try {
            $limit = $request->get('limit', 10);
            $category = $request->get('category');

            $cacheKey = "latest_news_{$limit}_{$category}";
            
            $news = Cache::remember($cacheKey, 600, function () use ($limit, $category) {
                return $this->newsService->getLatestNews($limit, $category);
            });

            return response()->json([
                'success' => true,
                'message' => 'Últimas notícias recuperadas com sucesso',
                'data' => $news,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao recuperar últimas notícias',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get news categories.
     */
    public function categories(): JsonResponse
    {
        try {
            $categories = Cache::remember('news_categories', 3600, function () {
                return $this->newsService->getCategories();
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
     * Get related news.
     */
    public function related($id): JsonResponse
    {
        try {
            $related = Cache::remember("related_news_{$id}", 1800, function () use ($id) {
                return $this->newsService->getRelatedNews($id);
            });

            return response()->json([
                'success' => true,
                'message' => 'Notícias relacionadas recuperadas com sucesso',
                'data' => $related,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao recuperar notícias relacionadas',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Search news.
     */
    public function search(Request $request): JsonResponse
    {
        $request->validate([
            'query' => 'required|string|min:2|max:100',
        ]);

        try {
            $query = $request->query;
            $perPage = $request->get('per_page', 15);
            $filters = $request->only(['category', 'date_from', 'date_to']);

            $results = $this->newsService->searchNews($query, $filters, $perPage);

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
     * Like/unlike news.
     */
    public function like($id): JsonResponse
    {
        try {
            $user = Auth::user();
            $result = $this->newsService->toggleLike($user->id, $id);

            if (!$result) {
                return response()->json([
                    'success' => false,
                    'message' => 'Notícia não encontrada',
                ], 404);
            }

            return response()->json([
                'success' => true,
                'message' => $result['action'] === 'liked' ? 'Notícia curtida' : 'Curtida removida',
                'data' => [
                    'is_liked' => $result['is_liked'],
                    'action' => $result['action'],
                    'likes_count' => $result['likes_count'],
                ],
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao curtir notícia',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Share news.
     */
    public function share($id, Request $request): JsonResponse
    {
        $request->validate([
            'platform' => 'required|in:facebook,twitter,whatsapp,email,copy_link',
        ]);

        try {
            $user = Auth::user();
            $result = $this->newsService->shareNews($user->id, $id, $request->platform);

            if (!$result) {
                return response()->json([
                    'success' => false,
                    'message' => 'Notícia não encontrada',
                ], 404);
            }

            return response()->json([
                'success' => true,
                'message' => 'Notícia compartilhada com sucesso',
                'data' => [
                    'share_url' => $result['share_url'],
                    'platform' => $request->platform,
                    'shares_count' => $result['shares_count'],
                ],
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao compartilhar notícia',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get news statistics.
     */
    public function statistics($id): JsonResponse
    {
        try {
            $user = Auth::user();
            
            if (!$user->hasRole('admin') && !$user->hasPermissionTo('view_news_statistics')) {
                return response()->json([
                    'success' => false,
                    'message' => 'Acesso negado',
                ], 403);
            }

            $stats = $this->newsService->getNewsStatistics($id);

            if (!$stats) {
                return response()->json([
                    'success' => false,
                    'message' => 'Notícia não encontrada',
                ], 404);
            }

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
     * Create news (admin/editor only).
     */
    public function store(CreateNewsRequest $request): JsonResponse
    {
        try {
            $user = Auth::user();
            
            if (!$user->hasRole(['admin', 'editor']) && !$user->hasPermissionTo('create_news')) {
                return response()->json([
                    'success' => false,
                    'message' => 'Acesso negado',
                ], 403);
            }

            $data = $request->validated();
            $data['author_id'] = $user->id;
            
            $news = $this->newsService->createNews($data);

            // Clear cache
            Cache::tags(['news'])->flush();

            return response()->json([
                'success' => true,
                'message' => 'Notícia criada com sucesso',
                'data' => $news,
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao criar notícia',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Update news (admin/editor/author only).
     */
    public function update(UpdateNewsRequest $request, $id): JsonResponse
    {
        try {
            $user = Auth::user();
            $news = News::findOrFail($id);
            
            // Check permissions
            if (!$user->hasRole(['admin', 'editor']) && 
                !$user->hasPermissionTo('update_news') && 
                $news->author_id !== $user->id) {
                return response()->json([
                    'success' => false,
                    'message' => 'Acesso negado',
                ], 403);
            }

            $data = $request->validated();
            $news = $this->newsService->updateNews($id, $data);

            if (!$news) {
                return response()->json([
                    'success' => false,
                    'message' => 'Notícia não encontrada',
                ], 404);
            }

            // Clear cache
            Cache::tags(['news'])->flush();
            Cache::forget("news_{$id}");

            return response()->json([
                'success' => true,
                'message' => 'Notícia atualizada com sucesso',
                'data' => $news,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao atualizar notícia',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Delete news (admin/editor/author only).
     */
    public function destroy($id): JsonResponse
    {
        try {
            $user = Auth::user();
            $news = News::findOrFail($id);
            
            // Check permissions
            if (!$user->hasRole(['admin', 'editor']) && 
                !$user->hasPermissionTo('delete_news') && 
                $news->author_id !== $user->id) {
                return response()->json([
                    'success' => false,
                    'message' => 'Acesso negado',
                ], 403);
            }

            $result = $this->newsService->deleteNews($id);

            if (!$result) {
                return response()->json([
                    'success' => false,
                    'message' => 'Notícia não encontrada',
                ], 404);
            }

            // Clear cache
            Cache::tags(['news'])->flush();
            Cache::forget("news_{$id}");

            return response()->json([
                'success' => true,
                'message' => 'Notícia excluída com sucesso',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao excluir notícia',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Publish/unpublish news (admin/editor only).
     */
    public function togglePublish($id): JsonResponse
    {
        try {
            $user = Auth::user();
            
            if (!$user->hasRole(['admin', 'editor']) && !$user->hasPermissionTo('publish_news')) {
                return response()->json([
                    'success' => false,
                    'message' => 'Acesso negado',
                ], 403);
            }

            $result = $this->newsService->togglePublish($id);

            if (!$result) {
                return response()->json([
                    'success' => false,
                    'message' => 'Notícia não encontrada',
                ], 404);
            }

            // Clear cache
            Cache::tags(['news'])->flush();

            return response()->json([
                'success' => true,
                'message' => $result['action'] === 'published' ? 'Notícia publicada' : 'Notícia despublicada',
                'data' => [
                    'status' => $result['status'],
                    'action' => $result['action'],
                    'published_at' => $result['published_at'],
                ],
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao alterar status de publicação',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}