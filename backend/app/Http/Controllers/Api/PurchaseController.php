<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Purchase;
use App\Events\PurchaseCompleted;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/**
 * PurchaseController
 *
 * Handles purchase creation and management
 * This is primarily for testing the loyalty system
 */
class PurchaseController extends Controller
{
    /**
     * Get user's purchases
     *
     * @group Purchases
     * @authenticated
     *
     * GET /api/v1/purchases
     */
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();

        $purchases = $user->purchases()
            ->orderBy('created_at', 'desc')
            ->paginate(15);

        return response()->json([
            'data' => $purchases->map(function ($purchase) {
                return [
                    'id' => $purchase->id,
                    'order_id' => $purchase->order_id,
                    'amount' => $purchase->amount,
                    'currency' => $purchase->currency,
                    'status' => $purchase->status,
                    'items' => $purchase->items,
                    'processed_for_loyalty' => $purchase->processed_for_loyalty,
                    'created_at' => $purchase->created_at,
                ];
            }),
            'meta' => [
                'current_page' => $purchases->currentPage(),
                'last_page' => $purchases->lastPage(),
                'total' => $purchases->total(),
            ],
        ]);
    }

    /**
     * Create a new purchase
     *
     * @group Purchases
     * @authenticated
     *
     * POST /api/v1/purchases
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'amount' => 'required|numeric|min:1',
            'currency' => 'sometimes|string|size:3',
            'items' => 'required|array|min:1',
            'items.*.name' => 'required|string',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.price' => 'required|numeric|min:0',
        ]);

        try {
            DB::beginTransaction();

            $purchase = Purchase::create([
                'user_id' => $request->user()->id,
                'order_id' => $this->generateOrderId(),
                'amount' => $validated['amount'],
                'currency' => $validated['currency'] ?? 'NGN',
                'status' => Purchase::STATUS_PENDING,
                'items' => $validated['items'],
                'metadata' => [
                    'ip_address' => $request->ip(),
                    'user_agent' => $request->userAgent(),
                ],
            ]);

            // Simulate payment processing (in real app, this would be async)
            $purchase->status = Purchase::STATUS_COMPLETED;
            $purchase->save();

            DB::commit();

            // Fire purchase completed event (triggers loyalty processing)
            event(new PurchaseCompleted($purchase));

            return response()->json([
                'message' => 'Purchase created successfully',
                'data' => [
                    'id' => $purchase->id,
                    'order_id' => $purchase->order_id,
                    'amount' => $purchase->amount,
                    'currency' => $purchase->currency,
                    'status' => $purchase->status,
                    'items' => $purchase->items,
                    'created_at' => $purchase->created_at,
                ],
            ], 201);
        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'message' => 'Failed to create purchase',
                'error' => config('app.debug') ? $e->getMessage() : 'Internal server error',
            ], 500);
        }
    }

    /**
     * Get a specific purchase
     *
     * @group Purchases
     * @authenticated
     *
     * GET /api/v1/purchases/{purchase}
     */
    public function show(Request $request, Purchase $purchase): JsonResponse
    {
        // Ensure user can only view their own purchases
        if ($purchase->user_id !== $request->user()->id && !$request->user()->isAdmin()) {
            return response()->json([
                'message' => 'Unauthorized access',
            ], 403);
        }

        return response()->json([
            'data' => [
                'id' => $purchase->id,
                'order_id' => $purchase->order_id,
                'amount' => $purchase->amount,
                'currency' => $purchase->currency,
                'status' => $purchase->status,
                'items' => $purchase->items,
                'metadata' => $purchase->metadata,
                'processed_for_loyalty' => $purchase->processed_for_loyalty,
                'created_at' => $purchase->created_at,
                'updated_at' => $purchase->updated_at,
            ],
        ]);
    }

    /**
     * Generate unique order ID
     */
    protected function generateOrderId(): string
    {
        return 'ORD-' . strtoupper(Str::random(8)) . '-' . time();
    }

    /**
     * Simulate payment completion (for testing)
     *
     * @group Purchases
     * @authenticated
     *
     * POST /api/v1/purchases/{purchase}/complete
     */
    public function complete(Request $request, Purchase $purchase): JsonResponse
    {
        if ($purchase->user_id !== $request->user()->id && !$request->user()->isAdmin()) {
            return response()->json([
                'message' => 'Unauthorized access',
            ], 403);
        }

        if ($purchase->status !== Purchase::STATUS_PENDING) {
            return response()->json([
                'message' => 'Purchase cannot be completed',
                'current_status' => $purchase->status,
            ], 400);
        }

        try {
            DB::beginTransaction();

            $purchase->status = Purchase::STATUS_COMPLETED;
            $purchase->save();

            DB::commit();

            // Fire purchase completed event
            event(new PurchaseCompleted($purchase));

            return response()->json([
                'message' => 'Purchase completed successfully',
                'data' => [
                    'id' => $purchase->id,
                    'order_id' => $purchase->order_id,
                    'status' => $purchase->status,
                ],
            ]);
        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'message' => 'Failed to complete purchase',
                'error' => config('app.debug') ? $e->getMessage() : 'Internal server error',
            ], 500);
        }
    }

    /**
     * Get purchase statistics for user
     *
     * @group Purchases
     * @authenticated
     *
     * GET /api/v1/purchases/statistics
     */
    public function statistics(Request $request): JsonResponse
    {
        $user = $request->user();

        $stats = [
            'total_purchases' => $user->purchases()->count(),
            'completed_purchases' => $user->purchases()->completed()->count(),
            'total_spent' => $user->purchases()->completed()->sum('amount'),
            'average_purchase' => $user->purchases()->completed()->avg('amount'),
            'pending_purchases' => $user->purchases()->where('status', Purchase::STATUS_PENDING)->count(),
            'recent_purchases' => $user->purchases()
                ->completed()
                ->orderBy('created_at', 'desc')
                ->limit(5)
                ->get()
                ->map(fn($p) => [
                    'order_id' => $p->order_id,
                    'amount' => $p->amount,
                    'created_at' => $p->created_at,
                ]),
        ];

        return response()->json([
            'data' => $stats,
        ]);
    }
}
