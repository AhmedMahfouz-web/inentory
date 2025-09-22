<?php

namespace App\Http\Controllers;

use App\Models\ProductRequest;
use App\Models\Product;
use App\Models\Branch;
use App\Services\ProductRequestService;
use App\Http\Requests\ProductRequestRequest;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;

class ProductRequestController extends Controller
{
    protected $productRequestService;

    public function __construct(ProductRequestService $productRequestService)
    {
        $this->productRequestService = $productRequestService;
    }

    /**
     * Display branch requests page
     */
    public function index(Request $request)
    {
        $branchId = $request->get('branch_id');
        $status = $request->get('status');
        
        $requests = $this->productRequestService->getBranchRequests($branchId, $status);
        $branches = Branch::all();
        $statistics = $this->productRequestService->getRequestStatistics($branchId);

        return view('product-requests.index', compact('requests', 'branches', 'statistics', 'branchId', 'status'));
    }

    /**
     * Display warehouse keeper dashboard
     */
    public function warehouseDashboard()
    {
        $pendingRequests = $this->productRequestService->getPendingRequests();
        $urgentRequests = $this->productRequestService->getUrgentRequests();
        $overdueRequests = $this->productRequestService->getOverdueRequests();
        $statistics = $this->productRequestService->getRequestStatistics();
        $popularProducts = $this->productRequestService->getPopularRequestedProducts();

        return view('product-requests.warehouse-dashboard', compact(
            'pendingRequests',
            'urgentRequests', 
            'overdueRequests',
            'statistics',
            'popularProducts'
        ));
    }

    /**
     * Show create request form
     */
    public function create()
    {
        // Check if user has permission to create product requests
        if (!auth()->user()->can('product-request-create')) {
            return redirect()->route('product-requests.index')
                           ->with('error', 'ليس لديك صلاحية لإنشاء طلبات المنتجات. يرجى التواصل مع المدير.');
        }

        $user = auth()->user();
        
        // Debug: Check if user has any branch assignments
        $userBranchesCount = \App\Models\UserBranch::where('user_id', $user->id)->count();
        $requestableBranchesCount = \App\Models\UserBranch::where('user_id', $user->id)
                                                          ->where('can_request', true)
                                                          ->count();
        
        Log::info("User {$user->id} has {$userBranchesCount} total branch assignments");
        Log::info("User {$user->id} has {$requestableBranchesCount} requestable branch assignments");
        
        // Get branches using direct query first to debug
        $branches = \App\Models\Branch::whereHas('userBranches', function($query) use ($user) {
            $query->where('user_id', $user->id)
                  ->where('can_request', true);
        })->get();
        
        // If no branch assignments, get all branches for admin/manager roles
        if ($branches->isEmpty() && ($user->hasRole('admin') || $user->hasRole('manager'))) {
            $branches = \App\Models\Branch::all();
            Log::info("User is admin/manager, showing all branches");
        }
        
        Log::info("Found {$branches->count()} requestable branches for user {$user->id}");
        
        // If user has no assigned branches, show error
        if ($branches->isEmpty()) {
            return redirect()->route('product-requests.index')
                           ->with('error', 'لا يمكنك إنشاء طلبات منتجات. يرجى التواصل مع المدير لتعيين فروع لك.');
        }
        
        $products = Product::with(['sub_category', 'unit'])->active()->get();

        return view('product-requests.create', compact('branches', 'products'));
    }

    /**
     * Store a new product request
     */
    public function store(Request $request)
    {
        $request->validate([
            'branch_id' => 'required|exists:branches,id',
            'priority' => 'required|in:low,medium,high,urgent',
            'notes' => 'nullable|string|max:1000',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.quantity' => 'required|numeric|min:0.01',
            'items.*.notes' => 'nullable|string|max:500'
        ], [
            'branch_id.required' => 'يجب اختيار الفرع',
            'branch_id.exists' => 'الفرع المحدد غير موجود',
            'priority.required' => 'يجب تحديد الأولوية',
            'items.required' => 'يجب إضافة منتج واحد على الأقل',
            'items.min' => 'يجب إضافة منتج واحد على الأقل',
            'items.*.product_id.required' => 'يجب اختيار المنتج',
            'items.*.product_id.exists' => 'المنتج المحدد غير موجود',
            'items.*.quantity.required' => 'يجب تحديد الكمية',
            'items.*.quantity.min' => 'الكمية يجب أن تكون أكبر من صفر'
        ]);

        // Check if user can request for this branch
        if (!auth()->user()->canRequestForBranch($request->branch_id)) {
            return redirect()->back()
                           ->withInput()
                           ->with('error', 'ليس لديك صلاحية لإنشاء طلبات لهذا الفرع');
        }

        $result = $this->productRequestService->createRequest(
            $request->branch_id,
            $request->items,
            $request->notes,
            $request->priority
        );

        if ($result['success']) {
            return redirect()->route('product-requests.show', $result['request']->id)
                ->with('success', $result['message']);
        } else {
            return back()->withInput()->with('error', $result['message']);
        }
    }

    /**
     * Show specific request
     */
    public function show(ProductRequest $productRequest)
    {
        $productRequest->load(['items.product.unit', 'branch', 'requestedBy', 'approvedBy', 'fulfilledBy']);
        
        return view('product-requests.show', compact('productRequest'));
    }

    /**
     * Show approve request form
     */
    public function showApprove(ProductRequest $productRequest)
    {
        if (!$productRequest->canBeApproved()) {
            return redirect()->route('product-requests.warehouse-dashboard')
                ->with('error', 'لا يمكن الموافقة على هذا الطلب');
        }

        $productRequest->load(['items.product.unit', 'branch', 'requestedBy']);
        
        return view('product-requests.approve', compact('productRequest'));
    }

    /**
     * Process request approval
     */
    public function approve(Request $request, ProductRequest $productRequest)
    {
        $request->validate([
            'warehouse_notes' => 'nullable|string|max:1000',
            'items' => 'required|array',
            'items.*.action' => 'required|in:approve,reject',
            'items.*.quantity' => 'required_if:items.*.action,approve|numeric|min:0',
            'items.*.notes' => 'nullable|string|max:500'
        ]);

        $result = $this->productRequestService->approveRequest(
            $productRequest->id,
            $request->items,
            $request->warehouse_notes
        );

        if ($result['success']) {
            return redirect()->route('product-requests.warehouse-dashboard')
                ->with('success', $result['message']);
        } else {
            return back()->withInput()->with('error', $result['message']);
        }
    }

    /**
     * Show fulfill request form
     */
    public function showFulfill(ProductRequest $productRequest)
    {
        if (!$productRequest->canBeFulfilled()) {
            return redirect()->route('product-requests.warehouse-dashboard')
                ->with('error', 'لا يمكن تنفيذ هذا الطلب');
        }

        $productRequest->load(['items.product.unit', 'branch', 'requestedBy']);
        
        return view('product-requests.fulfill', compact('productRequest'));
    }

    /**
     * Process request fulfillment
     */
    public function fulfill(Request $request, ProductRequest $productRequest)
    {
        $request->validate([
            'items' => 'required|array',
            'items.*.quantity' => 'required|numeric|min:0'
        ]);

        $result = $this->productRequestService->fulfillRequest(
            $productRequest->id,
            $request->items
        );

        if ($result['success']) {
            return redirect()->route('product-requests.warehouse-dashboard')
                ->with('success', $result['message']);
        } else {
            return back()->withInput()->with('error', $result['message']);
        }
    }

    /**
     * Cancel a request
     */
    public function cancel(Request $request, ProductRequest $productRequest)
    {
        $request->validate([
            'reason' => 'nullable|string|max:500'
        ]);

        $result = $this->productRequestService->cancelRequest(
            $productRequest->id,
            $request->reason
        );

        if ($result['success']) {
            return back()->with('success', $result['message']);
        } else {
            return back()->with('error', $result['message']);
        }
    }

    /**
     * Get request data via API
     */
    public function getRequestData(ProductRequest $productRequest): JsonResponse
    {
        $productRequest->load(['items.product.unit', 'branch', 'requestedBy', 'approvedBy', 'fulfilledBy']);
        
        return response()->json([
            'success' => true,
            'request' => $productRequest
        ]);
    }

    /**
     * Get pending requests count
     */
    public function getPendingCount(): JsonResponse
    {
        $count = ProductRequest::pending()->count();
        $urgentCount = ProductRequest::urgent()->whereIn('status', ['pending', 'approved'])->count();
        
        return response()->json([
            'success' => true,
            'pending_count' => $count,
            'urgent_count' => $urgentCount
        ]);
    }

    /**
     * Get request statistics
     */
    public function getStatistics(Request $request): JsonResponse
    {
        $branchId = $request->get('branch_id');
        $period = $request->get('period', 'month');
        
        $statistics = $this->productRequestService->getRequestStatistics($branchId, $period);
        
        return response()->json([
            'success' => true,
            'statistics' => $statistics
        ]);
    }

    /**
     * Search products for request
     */
    public function searchProducts(Request $request): JsonResponse
    {
        $search = $request->get('search');
        $limit = $request->get('limit', 20);
        
        $products = Product::with(['sub_category', 'unit'])
            ->active()
            ->where(function ($query) use ($search) {
                $query->where('name', 'like', "%{$search}%")
                      ->orWhere('code', 'like', "%{$search}%");
            })
            ->limit($limit)
            ->get();
        
        return response()->json([
            'success' => true,
            'products' => $products
        ]);
    }

    /**
     * Get product stock info
     */
    public function getProductStock(Product $product): JsonResponse
    {
        return response()->json([
            'success' => true,
            'product' => [
                'id' => $product->id,
                'name' => $product->name,
                'code' => $product->code,
                'stock' => $product->stock,
                'price' => $product->price,
                'unit' => $product->unit->name ?? '',
                'stock_status' => $product->stock_status,
                'is_low_stock' => $product->is_low_stock
            ]
        ]);
    }
}
