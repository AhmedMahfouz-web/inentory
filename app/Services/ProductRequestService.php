<?php

namespace App\Services;

use App\Models\ProductRequest;
use App\Models\ProductRequestItem;
use App\Models\Product;
use App\Models\Product_branch;
use App\Models\ProductAdded;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class ProductRequestService
{
    protected $notificationService;
    protected $inventoryService;

    public function __construct(NotificationService $notificationService, InventoryService $inventoryService)
    {
        $this->notificationService = $notificationService;
        $this->inventoryService = $inventoryService;
    }

    /**
     * Create a new product request
     */
    public function createRequest($branchId, $items, $notes = null, $priority = 'medium')
    {
        DB::beginTransaction();
        
        try {
            // Create the main request
            $request = ProductRequest::create([
                'branch_id' => $branchId,
                'requested_by' => Auth::id(),
                'status' => 'pending',
                'priority' => $priority,
                'notes' => $notes,
                'requested_at' => now()
            ]);

            // Create request items
            foreach ($items as $item) {
                $product = Product::findOrFail($item['product_id']);
                
                ProductRequestItem::create([
                    'product_request_id' => $request->id,
                    'product_id' => $item['product_id'],
                    'requested_qty' => $item['quantity'],
                    'unit_price' => $product->price,
                    'notes' => $item['notes'] ?? null
                ]);
            }

            // Send notification to warehouse keepers
            $this->notificationService->createSystemNotification(
                'product_request',
                'طلب منتجات جديد',
                "طلب جديد رقم {$request->request_number} من فرع {$request->branch->name}",
                [
                    'request_id' => $request->id,
                    'request_number' => $request->request_number,
                    'branch_name' => $request->branch->name,
                    'items_count' => count($items),
                    'priority' => $priority
                ]
            );

            DB::commit();
            
            return [
                'success' => true,
                'request' => $request->load(['items.product', 'branch']),
                'message' => 'تم إنشاء الطلب بنجاح'
            ];

        } catch (\Exception $e) {
            DB::rollBack();
            
            return [
                'success' => false,
                'message' => 'خطأ في إنشاء الطلب: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Get requests for a specific branch
     */
    public function getBranchRequests($branchId, $status = null, $limit = 50)
    {
        $query = ProductRequest::with(['items.product.unit', 'requestedBy', 'approvedBy', 'fulfilledBy'])
            ->byBranch($branchId)
            ->orderBy('requested_at', 'desc');

        if ($status) {
            $query->where('status', $status);
        }

        return $query->limit($limit)->get();
    }

    /**
     * Get all pending requests for warehouse keeper
     */
    public function getPendingRequests($priority = null)
    {
        $query = ProductRequest::with(['items.product.unit', 'branch', 'requestedBy'])
            ->pending()
            ->orderBy('priority', 'desc')
            ->orderBy('requested_at', 'asc');

        if ($priority) {
            $query->byPriority($priority);
        }

        return $query->get();
    }

    /**
     * Get urgent requests
     */
    public function getUrgentRequests()
    {
        return ProductRequest::with(['items.product.unit', 'branch', 'requestedBy'])
            ->urgent()
            ->whereIn('status', ['pending', 'approved'])
            ->orderBy('requested_at', 'asc')
            ->get();
    }

    /**
     * Approve a product request
     */
    public function approveRequest($requestId, $itemApprovals, $warehouseNotes = null)
    {
        DB::beginTransaction();
        
        try {
            $request = ProductRequest::with('items.product')->findOrFail($requestId);
            
            if (!$request->canBeApproved()) {
                throw new \Exception('لا يمكن الموافقة على هذا الطلب');
            }

            $allApproved = true;
            $anyApproved = false;

            // Process each item approval
            foreach ($itemApprovals as $itemId => $approval) {
                $item = $request->items()->findOrFail($itemId);
                
                if ($approval['action'] === 'approve') {
                    $approvedQty = min($approval['quantity'], $item->requested_qty);
                    
                    // Check stock availability
                    if ($item->product->stock < $approvedQty) {
                        $approvedQty = $item->product->stock;
                    }
                    
                    if ($approvedQty > 0) {
                        $item->approve($approvedQty, $approval['notes'] ?? null);
                        $anyApproved = true;
                    } else {
                        $item->reject('المخزون غير متوفر');
                        $allApproved = false;
                    }
                    
                    if ($approvedQty < $item->requested_qty) {
                        $allApproved = false;
                    }
                } else {
                    $item->reject($approval['notes'] ?? 'مرفوض من قبل أمين المخزن');
                    $allApproved = false;
                }
            }

            // Update request status
            $status = $allApproved ? 'approved' : ($anyApproved ? 'partially_approved' : 'rejected');
            
            $request->update([
                'status' => $status,
                'approved_by' => Auth::id(),
                'approved_at' => now(),
                'warehouse_notes' => $warehouseNotes
            ]);

            // Send notification to branch
            $this->notificationService->createSystemNotification(
                'request_approved',
                'تم الرد على طلب المنتجات',
                "تم {$request->status_label} الطلب رقم {$request->request_number}",
                [
                    'request_id' => $request->id,
                    'request_number' => $request->request_number,
                    'status' => $status
                ],
                $request->requested_by
            );

            DB::commit();
            
            return [
                'success' => true,
                'request' => $request->fresh(['items.product', 'branch']),
                'message' => 'تم معالجة الطلب بنجاح'
            ];

        } catch (\Exception $e) {
            DB::rollBack();
            
            return [
                'success' => false,
                'message' => 'خطأ في معالجة الطلب: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Fulfill a product request (send products to branch)
     */
    public function fulfillRequest($requestId, $fulfillmentData)
    {
        DB::beginTransaction();
        
        try {
            $request = ProductRequest::with(['items.product', 'branch'])->findOrFail($requestId);
            
            if (!$request->canBeFulfilled()) {
                throw new \Exception('لا يمكن تنفيذ هذا الطلب');
            }

            $allFulfilled = true;

            foreach ($fulfillmentData as $itemId => $fulfillment) {
                $item = $request->items()->findOrFail($itemId);
                $fulfilledQty = $fulfillment['quantity'];
                
                if ($fulfilledQty > 0) {
                    // Check stock availability
                    if ($item->product->stock < $fulfilledQty) {
                        throw new \Exception("المخزون غير كافي للمنتج: {$item->product->name}");
                    }

                    // Transfer product from main inventory to branch
                    $transferResult = $this->inventoryService->transferProduct(
                        $item->product_id,
                        null, // from main inventory
                        $request->branch_id,
                        $fulfilledQty,
                        Auth::id()
                    );

                    if (!$transferResult['success']) {
                        throw new \Exception($transferResult['message']);
                    }

                    // Update item fulfillment
                    $item->fulfill($fulfilledQty);
                    
                    if ($fulfilledQty < $item->approved_qty) {
                        $allFulfilled = false;
                    }
                } else {
                    $allFulfilled = false;
                }
            }

            // Update request status
            $request->update([
                'status' => 'fulfilled',
                'fulfilled_by' => Auth::id(),
                'fulfilled_at' => now()
            ]);

            // Send notification to branch
            $this->notificationService->createSystemNotification(
                'request_fulfilled',
                'تم تنفيذ طلب المنتجات',
                "تم تنفيذ الطلب رقم {$request->request_number} وإرسال المنتجات",
                [
                    'request_id' => $request->id,
                    'request_number' => $request->request_number
                ],
                $request->requested_by
            );

            DB::commit();
            
            return [
                'success' => true,
                'request' => $request->fresh(['items.product', 'branch']),
                'message' => 'تم تنفيذ الطلب وإرسال المنتجات بنجاح'
            ];

        } catch (\Exception $e) {
            DB::rollBack();
            
            return [
                'success' => false,
                'message' => 'خطأ في تنفيذ الطلب: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Cancel a product request
     */
    public function cancelRequest($requestId, $reason = null)
    {
        try {
            $request = ProductRequest::findOrFail($requestId);
            
            if (!$request->canBeCancelled()) {
                throw new \Exception('لا يمكن إلغاء هذا الطلب');
            }

            $request->cancel();

            // Send notification
            $this->notificationService->createSystemNotification(
                'request_cancelled',
                'تم إلغاء طلب المنتجات',
                "تم إلغاء الطلب رقم {$request->request_number}" . ($reason ? " - السبب: {$reason}" : ''),
                [
                    'request_id' => $request->id,
                    'request_number' => $request->request_number,
                    'reason' => $reason
                ]
            );

            return [
                'success' => true,
                'message' => 'تم إلغاء الطلب بنجاح'
            ];

        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => 'خطأ في إلغاء الطلب: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Get request statistics
     */
    public function getRequestStatistics($branchId = null, $period = 'month')
    {
        $startDate = match($period) {
            'day' => now()->startOfDay(),
            'week' => now()->startOfWeek(),
            'month' => now()->startOfMonth(),
            'year' => now()->startOfYear(),
            default => now()->startOfMonth()
        };

        $query = ProductRequest::where('requested_at', '>=', $startDate);
        
        if ($branchId) {
            $query->byBranch($branchId);
        }

        $requests = $query->get();

        return [
            'total_requests' => $requests->count(),
            'pending_requests' => $requests->where('status', 'pending')->count(),
            'approved_requests' => $requests->where('status', 'approved')->count(),
            'fulfilled_requests' => $requests->where('status', 'fulfilled')->count(),
            'rejected_requests' => $requests->where('status', 'rejected')->count(),
            'urgent_requests' => $requests->where('priority', 'urgent')->count(),
            'overdue_requests' => $requests->filter->is_overdue->count(),
            'total_items_requested' => $requests->sum('total_requested_qty'),
            'total_items_fulfilled' => $requests->sum('total_fulfilled_qty'),
            'estimated_value' => $requests->sum('estimated_value'),
            'by_priority' => $requests->groupBy('priority')->map->count(),
            'by_status' => $requests->groupBy('status')->map->count()
        ];
    }

    /**
     * Get overdue requests
     */
    public function getOverdueRequests()
    {
        return ProductRequest::with(['items.product', 'branch', 'requestedBy'])
            ->whereIn('status', ['pending', 'approved'])
            ->get()
            ->filter->is_overdue
            ->values();
    }

    /**
     * Get popular requested products
     */
    public function getPopularRequestedProducts($limit = 10, $period = 'month')
    {
        $startDate = match($period) {
            'week' => now()->startOfWeek(),
            'month' => now()->startOfMonth(),
            'year' => now()->startOfYear(),
            default => now()->startOfMonth()
        };

        return ProductRequestItem::join('product_requests', 'product_request_items.product_request_id', '=', 'product_requests.id')
            ->join('products', 'product_request_items.product_id', '=', 'products.id')
            ->where('product_requests.requested_at', '>=', $startDate)
            ->select(
                'products.id',
                'products.name',
                'products.code',
                DB::raw('COUNT(*) as request_count'),
                DB::raw('SUM(product_request_items.requested_qty) as total_requested'),
                DB::raw('SUM(product_request_items.fulfilled_qty) as total_fulfilled')
            )
            ->groupBy('products.id', 'products.name', 'products.code')
            ->orderBy('request_count', 'desc')
            ->limit($limit)
            ->get();
    }
}
