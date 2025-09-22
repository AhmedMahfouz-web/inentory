<?php

namespace App\Services;

use App\Models\Product;
use App\Models\User;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Cache;

class NotificationService
{
    /**
     * Send low stock notifications
     */
    public function sendLowStockNotifications()
    {
        try {
            $lowStockProducts = Product::lowStock()
                ->with(['sub_category.category', 'unit'])
                ->get();

            if ($lowStockProducts->isEmpty()) {
                return ['success' => true, 'message' => 'لا توجد منتجات بمخزون منخفض'];
            }

            // Get users who should receive notifications
            $notificationUsers = User::whereHas('roles', function ($query) {
                $query->whereIn('name', ['admin', 'manager', 'inventory_manager']);
            })->get();

            foreach ($notificationUsers as $user) {
                $this->sendLowStockEmail($user, $lowStockProducts);
            }

            // Cache the notification to avoid spam
            Cache::put('last_low_stock_notification', now(), now()->addHours(6));

            Log::info('Low stock notifications sent', [
                'products_count' => $lowStockProducts->count(),
                'users_notified' => $notificationUsers->count()
            ]);

            return [
                'success' => true,
                'message' => 'تم إرسال تنبيهات المخزون المنخفض بنجاح',
                'products_count' => $lowStockProducts->count(),
                'users_notified' => $notificationUsers->count()
            ];

        } catch (\Exception $e) {
            Log::error('Error sending low stock notifications', [
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }

    /**
     * Send low stock email to user
     */
    private function sendLowStockEmail($user, $products)
    {
        try {
            // In a real application, you would use Laravel's Mail facade
            // For now, we'll just log the notification
            Log::info('Low stock email notification', [
                'user_id' => $user->id,
                'user_email' => $user->email,
                'products' => $products->pluck('name')->toArray()
            ]);

            // Example of how you would send an actual email:
            /*
            Mail::to($user->email)->send(new LowStockNotification($products));
            */

        } catch (\Exception $e) {
            Log::error('Error sending low stock email', [
                'user_id' => $user->id,
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * Create system notification
     */
    public function createSystemNotification($type, $title, $message, $data = [], $userId = null)
    {
        try {
            $notification = [
                'id' => uniqid(),
                'type' => $type,
                'title' => $title,
                'message' => $message,
                'data' => $data,
                'user_id' => $userId,
                'read' => false,
                'created_at' => now()->toISOString()
            ];

            // Store in cache (in a real app, you'd use a database table)
            $cacheKey = $userId ? "user_notifications_{$userId}" : 'system_notifications';
            $notifications = Cache::get($cacheKey, []);
            array_unshift($notifications, $notification);
            
            // Keep only last 50 notifications
            $notifications = array_slice($notifications, 0, 50);
            
            Cache::put($cacheKey, $notifications, now()->addDays(30));

            return $notification;

        } catch (\Exception $e) {
            Log::error('Error creating system notification', [
                'type' => $type,
                'title' => $title,
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }

    /**
     * Get user notifications
     */
    public function getUserNotifications($userId, $unreadOnly = false)
    {
        $cacheKey = "user_notifications_{$userId}";
        $notifications = Cache::get($cacheKey, []);

        if ($unreadOnly) {
            $notifications = array_filter($notifications, function ($notification) {
                return !$notification['read'];
            });
        }

        return array_values($notifications);
    }

    /**
     * Mark notification as read
     */
    public function markAsRead($notificationId, $userId)
    {
        $cacheKey = "user_notifications_{$userId}";
        $notifications = Cache::get($cacheKey, []);

        foreach ($notifications as &$notification) {
            if ($notification['id'] === $notificationId) {
                $notification['read'] = true;
                break;
            }
        }

        Cache::put($cacheKey, $notifications, now()->addDays(30));

        return true;
    }

    /**
     * Mark all notifications as read
     */
    public function markAllAsRead($userId)
    {
        $cacheKey = "user_notifications_{$userId}";
        $notifications = Cache::get($cacheKey, []);

        foreach ($notifications as &$notification) {
            $notification['read'] = true;
        }

        Cache::put($cacheKey, $notifications, now()->addDays(30));

        return true;
    }

    /**
     * Send monthly report notification
     */
    public function sendMonthlyReportNotification($month)
    {
        try {
            $inventoryService = app(InventoryService::class);
            $monthlyStartService = app(MonthlyStartService::class);

            // Get monthly statistics
            $valuation = $inventoryService->getInventoryValuation();
            $monthlyReport = $monthlyStartService->getMonthlyStartReport($month);

            $message = "تقرير شهر {$month}:\n";
            $message .= "- إجمالي قيمة المخزون: " . number_format($valuation['total_inventory_value'], 2) . " ج\n";
            $message .= "- منتجات المخزن الرئيسي: " . $monthlyReport['summary']['main_products_count'] . "\n";
            $message .= "- منتجات الفروع: " . $monthlyReport['summary']['branch_products_count'];

            $this->createSystemNotification(
                'monthly_report',
                'تقرير شهري جديد',
                $message,
                [
                    'month' => $month,
                    'valuation' => $valuation,
                    'report' => $monthlyReport['summary']
                ]
            );

            return ['success' => true, 'message' => 'تم إرسال تنبيه التقرير الشهري'];

        } catch (\Exception $e) {
            Log::error('Error sending monthly report notification', [
                'month' => $month,
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }

    /**
     * Send stock movement notification
     */
    public function sendStockMovementNotification($type, $productName, $quantity, $branchName = null, $userId = null)
    {
        try {
            $typeLabels = [
                'sale' => 'بيع',
                'transfer' => 'تحويل',
                'increase' => 'زيادة مخزون',
                'decrease' => 'تقليل مخزون'
            ];

            $title = $typeLabels[$type] ?? 'حركة مخزون';
            $message = "{$title}: {$productName}";
            
            if ($branchName) {
                $message .= " - {$branchName}";
            }
            
            $message .= " - الكمية: " . number_format($quantity);

            $this->createSystemNotification(
                'stock_movement',
                $title,
                $message,
                [
                    'type' => $type,
                    'product_name' => $productName,
                    'quantity' => $quantity,
                    'branch_name' => $branchName
                ],
                $userId
            );

            return ['success' => true];

        } catch (\Exception $e) {
            Log::error('Error sending stock movement notification', [
                'type' => $type,
                'product_name' => $productName,
                'error' => $e->getMessage()
            ]);
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    /**
     * Check if notifications should be sent (to avoid spam)
     */
    public function shouldSendNotification($type, $cooldownHours = 6)
    {
        $cacheKey = "last_{$type}_notification";
        $lastSent = Cache::get($cacheKey);

        if (!$lastSent) {
            return true;
        }

        return now()->diffInHours($lastSent) >= $cooldownHours;
    }

    /**
     * Get notification statistics
     */
    public function getNotificationStats($userId = null)
    {
        try {
            if ($userId) {
                $notifications = $this->getUserNotifications($userId);
                $unreadCount = count($this->getUserNotifications($userId, true));
            } else {
                $notifications = Cache::get('system_notifications', []);
                $unreadCount = count(array_filter($notifications, function ($n) {
                    return !$n['read'];
                }));
            }

            return [
                'total' => count($notifications),
                'unread' => $unreadCount,
                'read' => count($notifications) - $unreadCount,
                'types' => $this->getNotificationTypeBreakdown($notifications)
            ];

        } catch (\Exception $e) {
            Log::error('Error getting notification stats', [
                'user_id' => $userId,
                'error' => $e->getMessage()
            ]);
            return [
                'total' => 0,
                'unread' => 0,
                'read' => 0,
                'types' => []
            ];
        }
    }

    /**
     * Get notification type breakdown
     */
    private function getNotificationTypeBreakdown($notifications)
    {
        $breakdown = [];
        
        foreach ($notifications as $notification) {
            $type = $notification['type'];
            if (!isset($breakdown[$type])) {
                $breakdown[$type] = 0;
            }
            $breakdown[$type]++;
        }

        return $breakdown;
    }
}
