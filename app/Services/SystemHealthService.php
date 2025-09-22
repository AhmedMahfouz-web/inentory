<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class SystemHealthService
{
    /**
     * Perform comprehensive system health check
     */
    public function performHealthCheck()
    {
        try {
            $healthData = [
                'timestamp' => Carbon::now(),
                'overall_status' => 'healthy',
                'checks' => [
                    'database' => $this->checkDatabase(),
                    'storage' => $this->checkStorage(),
                    'cache' => $this->checkCache(),
                    'queue' => $this->checkQueue(),
                    'logs' => $this->checkLogs(),
                    'performance' => $this->checkPerformance(),
                    'security' => $this->checkSecurity(),
                    'inventory' => $this->checkInventoryHealth()
                ]
            ];

            // Determine overall status
            $failedChecks = collect($healthData['checks'])->filter(function ($check) {
                return $check['status'] !== 'healthy';
            });

            if ($failedChecks->count() > 0) {
                $healthData['overall_status'] = $failedChecks->contains('status', 'critical') ? 'critical' : 'warning';
            }

            // Cache the health data
            Cache::put('system_health', $healthData, now()->addMinutes(5));

            return $healthData;

        } catch (\Exception $e) {
            Log::error('Health check failed', ['error' => $e->getMessage()]);
            
            return [
                'timestamp' => Carbon::now(),
                'overall_status' => 'critical',
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Check database health
     */
    protected function checkDatabase()
    {
        try {
            $startTime = microtime(true);
            
            // Test basic connectivity
            DB::connection()->getPdo();
            
            // Test query performance
            $result = DB::select('SELECT COUNT(*) as count FROM products');
            $queryTime = (microtime(true) - $startTime) * 1000;

            // Check database size
            $dbSize = DB::select("
                SELECT ROUND(SUM(data_length + index_length) / 1024 / 1024, 2) AS size_mb 
                FROM information_schema.tables 
                WHERE table_schema = DATABASE()
            ")[0]->size_mb ?? 0;

            // Check for long-running queries
            $longQueries = DB::select("
                SELECT COUNT(*) as count 
                FROM information_schema.processlist 
                WHERE command != 'Sleep' AND time > 30
            ")[0]->count ?? 0;

            $status = 'healthy';
            $issues = [];

            if ($queryTime > 1000) {
                $status = 'warning';
                $issues[] = 'Slow query response time';
            }

            if ($longQueries > 0) {
                $status = 'warning';
                $issues[] = 'Long-running queries detected';
            }

            if ($dbSize > 1000) { // 1GB
                $status = 'warning';
                $issues[] = 'Large database size';
            }

            return [
                'status' => $status,
                'response_time_ms' => round($queryTime, 2),
                'database_size_mb' => $dbSize,
                'long_running_queries' => $longQueries,
                'issues' => $issues
            ];

        } catch (\Exception $e) {
            return [
                'status' => 'critical',
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Check storage health
     */
    protected function checkStorage()
    {
        try {
            $disks = ['local', 'public'];
            $diskInfo = [];
            $overallStatus = 'healthy';
            $issues = [];

            foreach ($disks as $disk) {
                try {
                    // Test write/read
                    $testFile = 'health_check_' . time() . '.txt';
                    Storage::disk($disk)->put($testFile, 'health check');
                    $content = Storage::disk($disk)->get($testFile);
                    Storage::disk($disk)->delete($testFile);

                    // Get disk usage (if possible)
                    $path = Storage::disk($disk)->path('');
                    $freeBytes = disk_free_space($path);
                    $totalBytes = disk_total_space($path);
                    $usedPercent = (($totalBytes - $freeBytes) / $totalBytes) * 100;

                    $diskStatus = 'healthy';
                    if ($usedPercent > 90) {
                        $diskStatus = 'critical';
                        $issues[] = "Disk {$disk} is {$usedPercent}% full";
                    } elseif ($usedPercent > 80) {
                        $diskStatus = 'warning';
                        $issues[] = "Disk {$disk} is {$usedPercent}% full";
                    }

                    if ($diskStatus !== 'healthy' && $overallStatus === 'healthy') {
                        $overallStatus = $diskStatus;
                    }

                    $diskInfo[$disk] = [
                        'status' => $diskStatus,
                        'free_space_gb' => round($freeBytes / 1024 / 1024 / 1024, 2),
                        'total_space_gb' => round($totalBytes / 1024 / 1024 / 1024, 2),
                        'used_percent' => round($usedPercent, 2)
                    ];

                } catch (\Exception $e) {
                    $overallStatus = 'critical';
                    $diskInfo[$disk] = [
                        'status' => 'critical',
                        'error' => $e->getMessage()
                    ];
                }
            }

            return [
                'status' => $overallStatus,
                'disks' => $diskInfo,
                'issues' => $issues
            ];

        } catch (\Exception $e) {
            return [
                'status' => 'critical',
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Check cache health
     */
    protected function checkCache()
    {
        try {
            $startTime = microtime(true);
            
            // Test cache write/read
            $testKey = 'health_check_' . time();
            $testValue = 'cache_test_' . rand(1000, 9999);
            
            Cache::put($testKey, $testValue, 60);
            $retrieved = Cache::get($testKey);
            Cache::forget($testKey);
            
            $responseTime = (microtime(true) - $startTime) * 1000;

            $status = 'healthy';
            $issues = [];

            if ($retrieved !== $testValue) {
                $status = 'critical';
                $issues[] = 'Cache read/write test failed';
            }

            if ($responseTime > 100) {
                $status = 'warning';
                $issues[] = 'Slow cache response time';
            }

            return [
                'status' => $status,
                'response_time_ms' => round($responseTime, 2),
                'driver' => config('cache.default'),
                'issues' => $issues
            ];

        } catch (\Exception $e) {
            return [
                'status' => 'critical',
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Check queue health
     */
    protected function checkQueue()
    {
        try {
            // For file-based queues, check if jobs are piling up
            $queueDriver = config('queue.default');
            
            if ($queueDriver === 'database') {
                $pendingJobs = DB::table('jobs')->count();
                $failedJobs = DB::table('failed_jobs')->count();
                
                $status = 'healthy';
                $issues = [];
                
                if ($failedJobs > 10) {
                    $status = 'warning';
                    $issues[] = "High number of failed jobs: {$failedJobs}";
                }
                
                if ($pendingJobs > 100) {
                    $status = 'warning';
                    $issues[] = "High number of pending jobs: {$pendingJobs}";
                }
                
                return [
                    'status' => $status,
                    'driver' => $queueDriver,
                    'pending_jobs' => $pendingJobs,
                    'failed_jobs' => $failedJobs,
                    'issues' => $issues
                ];
            }

            return [
                'status' => 'healthy',
                'driver' => $queueDriver,
                'note' => 'Queue health check not implemented for this driver'
            ];

        } catch (\Exception $e) {
            return [
                'status' => 'warning',
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Check logs health
     */
    protected function checkLogs()
    {
        try {
            $logPath = storage_path('logs/laravel.log');
            $status = 'healthy';
            $issues = [];

            if (!file_exists($logPath)) {
                return [
                    'status' => 'warning',
                    'issues' => ['Log file does not exist']
                ];
            }

            $logSize = filesize($logPath);
            $logSizeMB = round($logSize / 1024 / 1024, 2);

            // Check log file size
            if ($logSizeMB > 100) {
                $status = 'warning';
                $issues[] = "Large log file size: {$logSizeMB}MB";
            }

            // Check for recent errors
            $recentErrors = 0;
            if ($logSizeMB < 50) { // Only check if file is not too large
                $logContent = file_get_contents($logPath);
                $lines = explode("\n", $logContent);
                $recentLines = array_slice($lines, -1000); // Last 1000 lines
                
                foreach ($recentLines as $line) {
                    if (strpos($line, '.ERROR:') !== false && 
                        strpos($line, date('Y-m-d')) !== false) {
                        $recentErrors++;
                    }
                }
            }

            if ($recentErrors > 10) {
                $status = 'warning';
                $issues[] = "High number of recent errors: {$recentErrors}";
            }

            return [
                'status' => $status,
                'log_size_mb' => $logSizeMB,
                'recent_errors' => $recentErrors,
                'issues' => $issues
            ];

        } catch (\Exception $e) {
            return [
                'status' => 'warning',
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Check system performance
     */
    protected function checkPerformance()
    {
        try {
            $startTime = microtime(true);
            $memoryStart = memory_get_usage(true);

            // Perform some operations to test performance
            $products = DB::table('products')->limit(100)->get();
            $branches = DB::table('branches')->get();
            
            $executionTime = (microtime(true) - $startTime) * 1000;
            $memoryUsed = memory_get_usage(true) - $memoryStart;
            $memoryUsedMB = round($memoryUsed / 1024 / 1024, 2);

            $status = 'healthy';
            $issues = [];

            if ($executionTime > 500) {
                $status = 'warning';
                $issues[] = 'Slow query execution time';
            }

            if ($memoryUsedMB > 50) {
                $status = 'warning';
                $issues[] = 'High memory usage';
            }

            return [
                'status' => $status,
                'execution_time_ms' => round($executionTime, 2),
                'memory_used_mb' => $memoryUsedMB,
                'peak_memory_mb' => round(memory_get_peak_usage(true) / 1024 / 1024, 2),
                'issues' => $issues
            ];

        } catch (\Exception $e) {
            return [
                'status' => 'warning',
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Check security health
     */
    protected function checkSecurity()
    {
        try {
            $status = 'healthy';
            $issues = [];
            $recommendations = [];

            // Check if debug mode is enabled in production
            if (config('app.debug') && config('app.env') === 'production') {
                $status = 'critical';
                $issues[] = 'Debug mode enabled in production';
            }

            // Check if APP_KEY is set
            if (empty(config('app.key'))) {
                $status = 'critical';
                $issues[] = 'Application key not set';
            }

            // Check HTTPS configuration
            if (!request()->isSecure() && config('app.env') === 'production') {
                $status = 'warning';
                $recommendations[] = 'Consider enabling HTTPS';
            }

            // Check for default passwords (basic check)
            $defaultPasswords = ['password', '123456', 'admin'];
            foreach ($defaultPasswords as $password) {
                if (config('database.connections.mysql.password') === $password) {
                    $status = 'critical';
                    $issues[] = 'Default database password detected';
                    break;
                }
            }

            return [
                'status' => $status,
                'issues' => $issues,
                'recommendations' => $recommendations,
                'https_enabled' => request()->isSecure(),
                'debug_mode' => config('app.debug'),
                'environment' => config('app.env')
            ];

        } catch (\Exception $e) {
            return [
                'status' => 'warning',
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Check inventory-specific health
     */
    protected function checkInventoryHealth()
    {
        try {
            $status = 'healthy';
            $issues = [];
            $warnings = [];

            // Check for products with negative stock
            $negativeStock = DB::table('products')->where('stock', '<', 0)->count();
            if ($negativeStock > 0) {
                $status = 'warning';
                $warnings[] = "{$negativeStock} products with negative stock";
            }

            // Check for products without categories
            $uncategorized = DB::table('products')->whereNull('category_id')->count();
            if ($uncategorized > 0) {
                $warnings[] = "{$uncategorized} products without categories";
            }

            // Check for products without units
            $noUnits = DB::table('products')->whereNull('unit_id')->count();
            if ($noUnits > 0) {
                $warnings[] = "{$noUnits} products without units";
            }

            // Check for low stock products
            $lowStock = DB::table('products')
                ->whereColumn('stock', '<=', 'min_stock')
                ->whereNotNull('min_stock')
                ->count();

            // Check for orphaned records
            $orphanedProductBranches = DB::table('product_branches')
                ->leftJoin('products', 'product_branches.product_id', '=', 'products.id')
                ->whereNull('products.id')
                ->count();

            if ($orphanedProductBranches > 0) {
                $status = 'warning';
                $issues[] = "{$orphanedProductBranches} orphaned product-branch records";
            }

            // Check monthly starts status
            $currentMonth = Carbon::now()->format('Y-m-01');
            $mainStartsExist = DB::table('start__inventories')->where('month', $currentMonth)->exists();
            $branchStartsExist = DB::table('starts')->where('month', $currentMonth)->exists();

            if (!$mainStartsExist || !$branchStartsExist) {
                $warnings[] = 'Monthly starts not generated for current month';
            }

            return [
                'status' => $status,
                'issues' => $issues,
                'warnings' => $warnings,
                'statistics' => [
                    'total_products' => DB::table('products')->count(),
                    'active_products' => DB::table('products')->where('is_active', true)->count(),
                    'low_stock_products' => $lowStock,
                    'negative_stock_products' => $negativeStock,
                    'uncategorized_products' => $uncategorized,
                    'products_without_units' => $noUnits,
                    'orphaned_records' => $orphanedProductBranches,
                    'monthly_starts_current' => [
                        'main_inventory' => $mainStartsExist,
                        'branch_inventory' => $branchStartsExist
                    ]
                ]
            ];

        } catch (\Exception $e) {
            return [
                'status' => 'critical',
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Get cached health data
     */
    public function getCachedHealthData()
    {
        return Cache::get('system_health', [
            'timestamp' => Carbon::now(),
            'overall_status' => 'unknown',
            'message' => 'Health check not performed yet'
        ]);
    }

    /**
     * Get health summary
     */
    public function getHealthSummary()
    {
        $healthData = $this->getCachedHealthData();
        
        if (!isset($healthData['checks'])) {
            return [
                'status' => 'unknown',
                'last_check' => null,
                'issues_count' => 0
            ];
        }

        $issuesCount = 0;
        $warningsCount = 0;
        $criticalCount = 0;

        foreach ($healthData['checks'] as $check) {
            if (isset($check['issues'])) {
                $issuesCount += count($check['issues']);
            }
            if (isset($check['warnings'])) {
                $warningsCount += count($check['warnings']);
            }
            if ($check['status'] === 'critical') {
                $criticalCount++;
            }
        }

        return [
            'status' => $healthData['overall_status'],
            'last_check' => $healthData['timestamp'],
            'issues_count' => $issuesCount,
            'warnings_count' => $warningsCount,
            'critical_count' => $criticalCount,
            'checks_passed' => count(array_filter($healthData['checks'], function($check) {
                return $check['status'] === 'healthy';
            })),
            'total_checks' => count($healthData['checks'])
        ];
    }
}
