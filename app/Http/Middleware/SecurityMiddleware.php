<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\RateLimiter;
use Symfony\Component\HttpFoundation\Response;

class SecurityMiddleware
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Log suspicious activity
        $this->logSuspiciousActivity($request);
        
        // Check for common attack patterns
        if ($this->detectSuspiciousPatterns($request)) {
            Log::warning('Suspicious request detected', [
                'ip' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'url' => $request->fullUrl(),
                'method' => $request->method(),
                'headers' => $request->headers->all()
            ]);
        }

        // Rate limiting for sensitive operations
        if ($this->isSensitiveOperation($request)) {
            $key = 'sensitive_ops:' . $request->ip();
            
            if (RateLimiter::tooManyAttempts($key, 10)) {
                Log::warning('Rate limit exceeded for sensitive operation', [
                    'ip' => $request->ip(),
                    'url' => $request->fullUrl()
                ]);
                
                return response()->json([
                    'error' => 'تم تجاوز الحد المسموح من المحاولات. يرجى المحاولة لاحقاً.'
                ], 429);
            }
            
            RateLimiter::hit($key, 300); // 5 minutes
        }

        return $next($request);
    }

    /**
     * Log suspicious activity
     */
    private function logSuspiciousActivity(Request $request)
    {
        $suspiciousPatterns = [
            'sql injection' => ['union', 'select', 'drop', 'delete', 'insert', 'update', '--', ';'],
            'xss' => ['<script', 'javascript:', 'onload=', 'onerror='],
            'path traversal' => ['../', '..\\', '/etc/passwd', '/windows/system32'],
            'command injection' => ['|', '&&', '||', ';', '`']
        ];

        $requestData = json_encode([
            'query' => $request->query(),
            'body' => $request->all(),
            'headers' => $request->headers->all()
        ]);

        foreach ($suspiciousPatterns as $type => $patterns) {
            foreach ($patterns as $pattern) {
                if (stripos($requestData, $pattern) !== false) {
                    Log::warning("Potential {$type} attack detected", [
                        'ip' => $request->ip(),
                        'user_agent' => $request->userAgent(),
                        'url' => $request->fullUrl(),
                        'pattern' => $pattern,
                        'type' => $type
                    ]);
                    break 2;
                }
            }
        }
    }

    /**
     * Detect suspicious patterns
     */
    private function detectSuspiciousPatterns(Request $request): bool
    {
        // Check for unusual user agents
        $userAgent = $request->userAgent();
        $suspiciousAgents = ['sqlmap', 'nikto', 'nmap', 'masscan', 'curl', 'wget'];
        
        foreach ($suspiciousAgents as $agent) {
            if (stripos($userAgent, $agent) !== false) {
                return true;
            }
        }

        // Check for unusual request patterns
        if ($request->hasHeader('X-Forwarded-For') && 
            count(explode(',', $request->header('X-Forwarded-For'))) > 5) {
            return true; // Too many proxy hops
        }

        // Check for missing common headers
        if (!$request->hasHeader('Accept') && !$request->hasHeader('User-Agent')) {
            return true;
        }

        return false;
    }

    /**
     * Check if this is a sensitive operation
     */
    private function isSensitiveOperation(Request $request): bool
    {
        $sensitiveRoutes = [
            'login',
            'register',
            'password',
            'admin',
            'delete',
            'destroy',
            'export',
            'backup'
        ];

        $currentRoute = $request->route() ? $request->route()->getName() : '';
        $currentPath = $request->path();

        foreach ($sensitiveRoutes as $route) {
            if (stripos($currentRoute, $route) !== false || 
                stripos($currentPath, $route) !== false) {
                return true;
            }
        }

        return false;
    }
}
