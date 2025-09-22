<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\ProductRequest;
use App\Models\Sell;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Carbon\Carbon;

class NotificationController extends Controller
{
    /**
     * Get user notifications
     */
    public function getNotifications(): JsonResponse
    {
        try {
            $notifications = collect();
            
            // Get pending product requests (for warehouse keepers)
            $pendingRequests = ProductRequest::with(['branch', 'requestedBy'])
                ->where('status', 'pending')
                ->orderBy('requested_at', 'desc')
                ->limit(5)
                ->get();

            foreach ($pendingRequests as $request) {
                $notifications->push([
                    'id' => 'request_' . $request->id,
                    'title' => 'طلب منتجات جديد',
                    'message' => "طلب رقم {$request->request_number} من فرع {$request->branch->name}",
                    'time' => $request->requested_at->diffForHumans(),
                    'type' => 'request',
                    'priority' => $request->priority,
                    'unread' => true,
                    'url' => route('product-requests.show-approve', $request),
                    'created_at' => $request->requested_at
                ]);
            }

            // Get urgent requests
            $urgentRequests = ProductRequest::with(['branch'])
                ->where('priority', 'urgent')
                ->whereIn('status', ['pending', 'approved'])
                ->orderBy('requested_at', 'desc')
                ->limit(3)
                ->get();

            foreach ($urgentRequests as $request) {
                $notifications->push([
                    'id' => 'urgent_' . $request->id,
                    'title' => 'طلب عاجل',
                    'message' => "طلب عاجل رقم {$request->request_number} من فرع {$request->branch->name}",
                    'time' => $request->requested_at->diffForHumans(),
                    'type' => 'urgent',
                    'priority' => 'urgent',
                    'unread' => true,
                    'url' => route('product-requests.show', $request),
                    'created_at' => $request->requested_at
                ]);
            }

            // Get low stock products
            $lowStockProducts = Product::with(['unit'])
                ->lowStock()
                ->active()
                ->orderBy('stock', 'asc')
                ->limit(5)
                ->get();

            foreach ($lowStockProducts as $product) {
                $notifications->push([
                    'id' => 'stock_' . $product->id,
                    'title' => 'مخزون منخفض',
                    'message' => "منتج \"{$product->name}\" متبقي {$product->stock} " . ($product->unit ? $product->unit->name : 'وحدة'),
                    'time' => 'الآن',
                    'type' => 'warning',
                    'priority' => 'medium',
                    'unread' => true,
                    'url' => route('show products') . '?search=' . urlencode($product->name),
                    'created_at' => now()
                ]);
            }

            // Get out of stock products
            $outOfStockProducts = Product::with(['unit'])
                ->outOfStock()
                ->active()
                ->limit(3)
                ->get();

            foreach ($outOfStockProducts as $product) {
                $notifications->push([
                    'id' => 'outstock_' . $product->id,
                    'title' => 'نفد المخزون',
                    'message' => "منتج \"{$product->name}\" نفد من المخزون",
                    'time' => 'الآن',
                    'type' => 'danger',
                    'priority' => 'high',
                    'unread' => true,
                    'url' => route('show products') . '?search=' . urlencode($product->name),
                    'created_at' => now()
                ]);
            }

            // Get recent high-value sales (today)
            $highValueSales = Sell::with(['product_branch.product', 'product_branch.branch'])
                ->whereDate('created_at', today())
                ->get()
                ->filter(function ($sell) {
                    return ($sell->qty * $sell->product_branch->price) > 1000; // High value threshold
                })
                ->sortByDesc('created_at')
                ->take(3);

            foreach ($highValueSales as $sell) {
                $value = $sell->qty * $sell->product_branch->price;
                $notifications->push([
                    'id' => 'sale_' . $sell->id,
                    'title' => 'مبيعات عالية القيمة',
                    'message' => "بيع {$sell->product_branch->product->name} بقيمة " . number_format($value, 2) . " ريال",
                    'time' => $sell->created_at->diffForHumans(),
                    'type' => 'success',
                    'priority' => 'low',
                    'unread' => true,
                    'url' => '#',
                    'created_at' => $sell->created_at
                ]);
            }

            // Sort by priority and time
            $sortedNotifications = $notifications->sortByDesc(function ($notification) {
                $priorityWeight = [
                    'urgent' => 4,
                    'high' => 3,
                    'medium' => 2,
                    'low' => 1
                ];
                return $priorityWeight[$notification['priority']] * 1000 + $notification['created_at']->timestamp;
            })->take(10)->values();

            $unreadCount = $sortedNotifications->where('unread', true)->count();

            return response()->json([
                'success' => true,
                'notifications' => $sortedNotifications,
                'unread_count' => $unreadCount,
                'total_count' => $sortedNotifications->count()
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'خطأ في تحميل التنبيهات',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get notification counts
     */
    public function getNotificationCounts(): JsonResponse
    {
        try {
            $pendingRequests = ProductRequest::where('status', 'pending')->count();
            $urgentRequests = ProductRequest::where('priority', 'urgent')
                ->whereIn('status', ['pending', 'approved'])->count();
            $lowStockProducts = Product::lowStock()->active()->count();
            $outOfStockProducts = Product::outOfStock()->active()->count();

            $totalNotifications = $pendingRequests + $urgentRequests + $lowStockProducts + $outOfStockProducts;

            return response()->json([
                'success' => true,
                'counts' => [
                    'pending_requests' => $pendingRequests,
                    'urgent_requests' => $urgentRequests,
                    'low_stock' => $lowStockProducts,
                    'out_of_stock' => $outOfStockProducts,
                    'total' => $totalNotifications
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'خطأ في تحميل عدد التنبيهات',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Mark notification as read
     */
    public function markAsRead(Request $request): JsonResponse
    {
        try {
            $notificationId = $request->input('notification_id');
            
            // Here you would typically update a notifications table
            // For now, we'll just return success since we're using dynamic notifications
            
            return response()->json([
                'success' => true,
                'message' => 'تم تحديد التنبيه كمقروء'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'خطأ في تحديث التنبيه',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Mark all notifications as read
     */
    public function markAllAsRead(): JsonResponse
    {
        try {
            // Here you would typically update all notifications for the user
            // For now, we'll just return success
            
            return response()->json([
                'success' => true,
                'message' => 'تم تحديد جميع التنبيهات كمقروءة'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'خطأ في تحديث التنبيهات',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
