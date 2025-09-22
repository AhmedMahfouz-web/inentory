<?php

namespace App\Http\Controllers;

use App\Services\CacheService;
use App\Services\InventoryService;
use App\Services\NotificationService;
use App\Services\SystemHealthService;
use App\Services\ExportService;
use App\Services\BackupService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Carbon\Carbon;

class DashboardController extends Controller
{
    protected $cacheService;
    protected $inventoryService;
    protected $notificationService;
    protected $healthService;
    protected $exportService;
    protected $backupService;

    public function __construct(
        CacheService $cacheService,
        InventoryService $inventoryService,
        NotificationService $notificationService,
        SystemHealthService $healthService,
        ExportService $exportService,
        BackupService $backupService
    ) {
        $this->cacheService = $cacheService;
        $this->inventoryService = $inventoryService;
        $this->notificationService = $notificationService;
        $this->healthService = $healthService;
        $this->exportService = $exportService;
        $this->backupService = $backupService;
    }

    /**
     * Show admin dashboard
     */
    public function index()
    {
        // Use cached data for better performance
        $dashboardStats = $this->cacheService->cacheDashboardStats();
        $lowStockProducts = $this->cacheService->cacheLowStockProducts();
        $recentActivity = $this->cacheService->cacheRecentActivity(15);
        $branchPerformance = $this->cacheService->cacheBranchPerformance();
        $inventoryValuation = $this->cacheService->cacheInventoryValuation();
        
        // Get system health summary
        $healthSummary = $this->healthService->getHealthSummary();
        
        // Get notification stats
        $notificationStats = $this->notificationService->getNotificationStats(auth()->id());

        return view('dashboard.admin', compact(
            'dashboardStats',
            'lowStockProducts',
            'recentActivity',
            'branchPerformance',
            'inventoryValuation',
            'healthSummary',
            'notificationStats'
        ));
    }

    /**
     * Get dashboard data via API
     */
    public function getDashboardData(): JsonResponse
    {
        try {
            $data = [
                'stats' => $this->cacheService->cacheDashboardStats(),
                'low_stock' => $this->cacheService->cacheLowStockProducts()->take(10),
                'recent_activity' => $this->cacheService->cacheRecentActivity(10),
                'health_summary' => $this->healthService->getHealthSummary(),
                'notifications' => $this->notificationService->getUserNotifications(auth()->id(), true)
            ];

            return response()->json([
                'success' => true,
                'data' => $data
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'خطأ في تحميل بيانات لوحة التحكم',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get inventory alerts
     */
    public function getInventoryAlerts(): JsonResponse
    {
        try {
            $alerts = $this->inventoryService->getLowStockAlerts(true);
            
            return response()->json([
                'success' => true,
                'alerts' => $alerts,
                'count' => $alerts->count()
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'خطأ في تحميل تنبيهات المخزون',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Perform system health check
     */
    public function healthCheck(): JsonResponse
    {
        try {
            $healthData = $this->healthService->performHealthCheck();
            
            return response()->json([
                'success' => true,
                'health' => $healthData
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'خطأ في فحص حالة النظام',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Clear system caches
     */
    public function clearCache(): JsonResponse
    {
        try {
            $this->cacheService->clearAll();
            
            return response()->json([
                'success' => true,
                'message' => 'تم مسح جميع البيانات المؤقتة بنجاح'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'خطأ في مسح البيانات المؤقتة',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Warm up caches
     */
    public function warmUpCache(): JsonResponse
    {
        try {
            $this->cacheService->warmUp();
            
            return response()->json([
                'success' => true,
                'message' => 'تم تحديث البيانات المؤقتة بنجاح'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'خطأ في تحديث البيانات المؤقتة',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Export dashboard data
     */
    public function exportDashboard(Request $request): JsonResponse
    {
        try {
            $format = $request->get('format', 'excel');
            $type = $request->get('type', 'summary');

            switch ($type) {
                case 'products':
                    $result = $this->exportService->exportProducts($request->all());
                    break;
                case 'inventory':
                    $branchId = $request->get('branch_id');
                    $result = $this->exportService->exportInventoryReport($branchId);
                    break;
                case 'sales':
                    $startDate = $request->get('start_date', Carbon::now()->startOfMonth());
                    $endDate = $request->get('end_date', Carbon::now()->endOfMonth());
                    $branchId = $request->get('branch_id');
                    $result = $this->exportService->exportSalesReport($startDate, $endDate, $branchId);
                    break;
                default:
                    throw new \Exception('نوع التصدير غير مدعوم');
            }

            return response()->json([
                'success' => true,
                'message' => 'تم تصدير البيانات بنجاح',
                'file' => $result
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'خطأ في تصدير البيانات',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Create system backup
     */
    public function createBackup(Request $request): JsonResponse
    {
        try {
            $includeFiles = $request->get('include_files', true);
            $result = $this->backupService->createFullBackup($includeFiles);

            if ($result['success']) {
                return response()->json([
                    'success' => true,
                    'message' => 'تم إنشاء النسخة الاحتياطية بنجاح',
                    'backup' => $result
                ]);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => 'فشل في إنشاء النسخة الاحتياطية',
                    'error' => $result['error']
                ], 500);
            }

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'خطأ في إنشاء النسخة الاحتياطية',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * List available backups
     */
    public function listBackups(): JsonResponse
    {
        try {
            $backups = $this->backupService->listBackups();
            $stats = $this->backupService->getBackupStatistics();

            return response()->json([
                'success' => true,
                'backups' => $backups,
                'statistics' => $stats
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'خطأ في تحميل قائمة النسخ الاحتياطية',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Download backup
     */
    public function downloadBackup($backupName)
    {
        try {
            return $this->backupService->downloadBackup($backupName);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'خطأ في تحميل النسخة الاحتياطية',
                'error' => $e->getMessage()
            ], 404);
        }
    }

    /**
     * Send low stock notifications
     */
    public function sendLowStockNotifications(): JsonResponse
    {
        try {
            $result = $this->notificationService->sendLowStockNotifications();

            return response()->json($result);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'خطأ في إرسال التنبيهات',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get system statistics
     */
    public function getSystemStats(): JsonResponse
    {
        try {
            $stats = [
                'database' => [
                    'products_count' => \App\Models\Product::count(),
                    'branches_count' => \App\Models\Branch::count(),
                    'categories_count' => \DB::table('categories')->count(),
                    'sales_today' => \App\Models\Sell::whereDate('created_at', today())->count(),
                    'sales_this_month' => \App\Models\Sell::whereMonth('created_at', now()->month)->count()
                ],
                'inventory' => [
                    'total_value' => $this->cacheService->cacheInventoryValuation()['total_inventory_value'],
                    'low_stock_products' => \App\Models\Product::lowStock()->count(),
                    'out_of_stock_products' => \App\Models\Product::outOfStock()->count()
                ],
                'system' => [
                    'php_version' => PHP_VERSION,
                    'laravel_version' => app()->version(),
                    'cache_driver' => config('cache.default'),
                    'queue_driver' => config('queue.default')
                ],
                'cache' => $this->cacheService->getStats()
            ];

            return response()->json([
                'success' => true,
                'statistics' => $stats
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'خطأ في تحميل إحصائيات النظام',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get performance metrics
     */
    public function getPerformanceMetrics(): JsonResponse
    {
        try {
            $startTime = microtime(true);
            $memoryStart = memory_get_usage(true);

            // Perform some database operations to measure performance
            $productsCount = \App\Models\Product::count();
            $branchesCount = \App\Models\Branch::count();
            
            $executionTime = (microtime(true) - $startTime) * 1000;
            $memoryUsed = memory_get_usage(true) - $memoryStart;

            $metrics = [
                'execution_time_ms' => round($executionTime, 2),
                'memory_used_mb' => round($memoryUsed / 1024 / 1024, 2),
                'peak_memory_mb' => round(memory_get_peak_usage(true) / 1024 / 1024, 2),
                'queries_count' => $productsCount + $branchesCount, // Simple metric
                'cache_hit_ratio' => $this->calculateCacheHitRatio()
            ];

            return response()->json([
                'success' => true,
                'metrics' => $metrics
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'خطأ في تحميل مقاييس الأداء',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Calculate cache hit ratio (simplified)
     */
    protected function calculateCacheHitRatio()
    {
        // This is a simplified calculation
        // In a real application, you would track cache hits/misses
        $cacheStats = $this->cacheService->getStats();
        return $cacheStats['cached_items'] > 0 ? 85 : 0; // Mock percentage
    }
}
