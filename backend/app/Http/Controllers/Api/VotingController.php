<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Voting\CreateVotingRequest;
use App\Http\Requests\Voting\UpdateVotingRequest;
use App\Http\Requests\Voting\CastVoteRequest;
use App\Http\Resources\VotingResource;
use App\Http\Resources\VotingCollection;
use App\Http\Resources\VoteResource;
use App\Services\VotingService;
use App\Services\BiometricService;
use App\Models\Voting;
use App\Models\Vote;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Spatie\QueryBuilder\QueryBuilder;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\AllowedSort;

class VotingController extends Controller
{
    protected $votingService;
    protected $biometricService;

    public function __construct(VotingService $votingService, BiometricService $biometricService)
    {
        $this->votingService = $votingService;
        $this->biometricService = $biometricService;
        $this->middleware('auth:api');
        $this->middleware('role:admin|manager', ['only' => ['store', 'update', 'destroy', 'start', 'end']]);
    }

    /**
     * Get paginated list of votings.
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $votings = QueryBuilder::for(Voting::class)
                ->allowedFilters([
                    AllowedFilter::exact('status'),
                    AllowedFilter::exact('type'),
                    AllowedFilter::exact('category'),
                    AllowedFilter::partial('title'),
                    AllowedFilter::partial('description'),
                    AllowedFilter::scope('active'),
                    AllowedFilter::scope('upcoming'),
                    AllowedFilter::scope('completed'),
                    AllowedFilter::scope('public'),
                ])
                ->allowedSorts([
                    AllowedSort::field('title'),
                    AllowedSort::field('created_at'),
                    AllowedSort::field('starts_at'),
                    AllowedSort::field('ends_at'),
                    AllowedSort::field('priority'),
                ])
                ->with(['creator', 'options', 'results'])
                ->paginate($request->get('per_page', 15));

            return response()->json([
                'success' => true,
                'data' => new VotingCollection($votings),
                'meta' => [
                    'total' => $votings->total(),
                    'per_page' => $votings->perPage(),
                    'current_page' => $votings->currentPage(),
                    'last_page' => $votings->lastPage(),
                ],
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao buscar votações',
                'error' => config('app.debug') ? $e->getMessage() : null,
            ], 500);
        }
    }

    /**
     * Create new voting.
     */
    public function store(CreateVotingRequest $request): JsonResponse
    {
        try {
            $votingData = $request->validated();
            $votingData['created_by'] = auth('api')->id();
            
            $result = $this->votingService->createVoting($votingData);

            if (!$result['success']) {
                return response()->json([
                    'success' => false,
                    'message' => $result['message'],
                    'errors' => $result['errors'] ?? null,
                ], 422);
            }

            return response()->json([
                'success' => true,
                'message' => 'Votação criada com sucesso',
                'data' => new VotingResource($result['voting']),
            ], 201);
        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Dados inválidos',
                'errors' => $e->errors(),
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao criar votação',
                'error' => config('app.debug') ? $e->getMessage() : null,
            ], 500);
        }
    }

    /**
     * Get specific voting details.
     */
    public function show(Voting $voting): JsonResponse
    {
        try {
            $voting->load([
                'creator',
                'approver',
                'options.votes',
                'results',
                'participants',
                'votes' => function ($query) {
                    $query->where('user_id', auth('api')->id());
                },
            ]);

            // Check if user can view this voting
            if (!$this->canViewVoting($voting)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Não autorizado a visualizar esta votação',
                ], 403);
            }

            return response()->json([
                'success' => true,
                'data' => new VotingResource($voting),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao buscar votação',
                'error' => config('app.debug') ? $e->getMessage() : null,
            ], 500);
        }
    }

    /**
     * Update voting.
     */
    public function update(UpdateVotingRequest $request, Voting $voting): JsonResponse
    {
        try {
            // Check if voting can be updated
            if (!$this->canUpdateVoting($voting)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Esta votação não pode ser editada',
                ], 422);
            }

            $votingData = $request->validated();
            $result = $this->votingService->updateVoting($voting, $votingData);

            if (!$result['success']) {
                return response()->json([
                    'success' => false,
                    'message' => $result['message'],
                    'errors' => $result['errors'] ?? null,
                ], 422);
            }

            return response()->json([
                'success' => true,
                'message' => 'Votação atualizada com sucesso',
                'data' => new VotingResource($result['voting']),
            ]);
        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Dados inválidos',
                'errors' => $e->errors(),
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao atualizar votação',
                'error' => config('app.debug') ? $e->getMessage() : null,
            ], 500);
        }
    }

    /**
     * Start voting.
     */
    public function start(Voting $voting): JsonResponse
    {
        try {
            $result = $this->votingService->startVoting($voting);

            if (!$result['success']) {
                return response()->json([
                    'success' => false,
                    'message' => $result['message'],
                ], 422);
            }

            return response()->json([
                'success' => true,
                'message' => 'Votação iniciada com sucesso',
                'data' => new VotingResource($result['voting']),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao iniciar votação',
                'error' => config('app.debug') ? $e->getMessage() : null,
            ], 500);
        }
    }

    /**
     * End voting.
     */
    public function end(Voting $voting): JsonResponse
    {
        try {
            $result = $this->votingService->endVoting($voting);

            if (!$result['success']) {
                return response()->json([
                    'success' => false,
                    'message' => $result['message'],
                ], 422);
            }

            return response()->json([
                'success' => true,
                'message' => 'Votação encerrada com sucesso',
                'data' => new VotingResource($result['voting']),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao encerrar votação',
                'error' => config('app.debug') ? $e->getMessage() : null,
            ], 500);
        }
    }

    /**
     * Cast vote in voting.
     */
    public function vote(CastVoteRequest $request, Voting $voting): JsonResponse
    {
        try {
            $voteData = $request->validated();
            $voteData['user_id'] = auth('api')->id();
            $voteData['voting_id'] = $voting->id;
            
            // Verify biometric if required
            if ($voting->requires_biometric && isset($voteData['biometric_data'])) {
                $biometricResult = $this->biometricService->verifyForVoting(
                    auth('api')->user(),
                    $voteData['biometric_data'],
                    $voteData['biometric_type'] ?? 'fingerprint'
                );
                
                if (!$biometricResult['success']) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Verificação biométrica falhou',
                        'error' => $biometricResult['message'],
                    ], 422);
                }
                
                $voteData['biometric_verified'] = true;
                $voteData['biometric_hash'] = $biometricResult['hash'];
            }

            $result = $this->votingService->castVote($voting, $voteData);

            if (!$result['success']) {
                return response()->json([
                    'success' => false,
                    'message' => $result['message'],
                    'errors' => $result['errors'] ?? null,
                ], 422);
            }

            return response()->json([
                'success' => true,
                'message' => 'Voto registrado com sucesso',
                'data' => new VoteResource($result['vote']),
            ]);
        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Dados inválidos',
                'errors' => $e->errors(),
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao registrar voto',
                'error' => config('app.debug') ? $e->getMessage() : null,
            ], 500);
        }
    }

    /**
     * Get voting results.
     */
    public function results(Voting $voting): JsonResponse
    {
        try {
            // Check if user can view results
            if (!$this->canViewResults($voting)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Resultados não disponíveis ainda',
                ], 403);
            }

            $results = $this->votingService->getVotingResults($voting);

            return response()->json([
                'success' => true,
                'data' => $results,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao buscar resultados',
                'error' => config('app.debug') ? $e->getMessage() : null,
            ], 500);
        }
    }

    /**
     * Get voting statistics.
     */
    public function statistics(Voting $voting): JsonResponse
    {
        try {
            $stats = $this->votingService->getVotingStatistics($voting);

            return response()->json([
                'success' => true,
                'data' => $stats,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao buscar estatísticas',
                'error' => config('app.debug') ? $e->getMessage() : null,
            ], 500);
        }
    }

    /**
     * Get user's vote in specific voting.
     */
    public function myVote(Voting $voting): JsonResponse
    {
        try {
            $vote = $this->votingService->getUserVote($voting, auth('api')->user());

            if (!$vote) {
                return response()->json([
                    'success' => true,
                    'data' => null,
                    'message' => 'Usuário ainda não votou nesta votação',
                ]);
            }

            return response()->json([
                'success' => true,
                'data' => new VoteResource($vote),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao buscar voto',
                'error' => config('app.debug') ? $e->getMessage() : null,
            ], 500);
        }
    }

    /**
     * Check if user is eligible to vote.
     */
    public function checkEligibility(Voting $voting): JsonResponse
    {
        try {
            $user = auth('api')->user();
            $eligibility = $this->votingService->checkVotingEligibility($voting, $user);

            return response()->json([
                'success' => true,
                'data' => $eligibility,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao verificar elegibilidade',
                'error' => config('app.debug') ? $e->getMessage() : null,
            ], 500);
        }
    }

    /**
     * Get live voting updates (for WebSocket).
     */
    public function liveUpdates(Voting $voting): JsonResponse
    {
        try {
            $updates = $this->votingService->getLiveUpdates($voting);

            return response()->json([
                'success' => true,
                'data' => $updates,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao buscar atualizações',
                'error' => config('app.debug') ? $e->getMessage() : null,
            ], 500);
        }
    }

    /**
     * Delete voting.
     */
    public function destroy(Voting $voting): JsonResponse
    {
        try {
            // Check if voting can be deleted
            if (!$this->canDeleteVoting($voting)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Esta votação não pode ser excluída',
                ], 422);
            }

            $result = $this->votingService->deleteVoting($voting);

            if (!$result['success']) {
                return response()->json([
                    'success' => false,
                    'message' => $result['message'],
                ], 422);
            }

            return response()->json([
                'success' => true,
                'message' => 'Votação excluída com sucesso',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao excluir votação',
                'error' => config('app.debug') ? $e->getMessage() : null,
            ], 500);
        }
    }

    /**
     * Check if user can view voting.
     */
    private function canViewVoting(Voting $voting): bool
    {
        $user = auth('api')->user();
        
        // Public votings can be viewed by anyone
        if ($voting->is_public) {
            return true;
        }
        
        // Admins and managers can view all votings
        if ($user->hasAnyRole(['admin', 'manager'])) {
            return true;
        }
        
        // Check if user is in eligible participants
        return $voting->participants()->where('user_id', $user->id)->exists();
    }

    /**
     * Check if voting can be updated.
     */
    private function canUpdateVoting(Voting $voting): bool
    {
        // Cannot update if voting has started
        if (in_array($voting->status, ['active', 'completed', 'cancelled'])) {
            return false;
        }
        
        return true;
    }

    /**
     * Check if user can view results.
     */
    private function canViewResults(Voting $voting): bool
    {
        $user = auth('api')->user();
        
        // Admins and managers can always view results
        if ($user->hasAnyRole(['admin', 'manager'])) {
            return true;
        }
        
        // Check voting settings for result visibility
        if ($voting->show_results_after === 'immediately') {
            return true;
        }
        
        if ($voting->show_results_after === 'after_voting' && $voting->status === 'completed') {
            return true;
        }
        
        if ($voting->show_results_after === 'never') {
            return false;
        }
        
        return false;
    }

    /**
     * Check if voting can be deleted.
     */
    private function canDeleteVoting(Voting $voting): bool
    {
        // Cannot delete if voting has votes
        if ($voting->votes()->count() > 0) {
            return false;
        }
        
        // Cannot delete if voting is active
        if ($voting->status === 'active') {
            return false;
        }
        
        return true;
    }
}