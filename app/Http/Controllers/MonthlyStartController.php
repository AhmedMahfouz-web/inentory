<?php

namespace App\Http\Controllers;

use App\Services\MonthlyStartService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class MonthlyStartController extends Controller
{
    protected $monthlyStartService;

    public function __construct(MonthlyStartService $monthlyStartService)
    {
        $this->monthlyStartService = $monthlyStartService;
    }

    /**
     * Display monthly start management page
     */
    public function index()
    {
        $currentMonth = Carbon::now()->format('Y-m');
        $previousMonth = Carbon::now()->subMonth()->format('Y-m');
        
        // Check if starts exist for current and previous months
        $currentMonthExists = $this->monthlyStartService->monthlyStartsExist($currentMonth);
        $previousMonthExists = $this->monthlyStartService->monthlyStartsExist($previousMonth);
        
        // Get report for current month if exists
        $currentReport = null;
        if ($currentMonthExists['any_exists']) {
            $currentReport = $this->monthlyStartService->getMonthlyStartReport($currentMonth);
        }
        
        return view('pages.monthly_starts.index', compact(
            'currentMonth',
            'previousMonth',
            'currentMonthExists',
            'previousMonthExists',
            'currentReport'
        ));
    }

    /**
     * Generate monthly starts for current month
     */
    public function generateCurrent()
    {
        try {
            $results = $this->monthlyStartService->autoGenerateCurrentMonth();
            
            return redirect()->back()->with('success', 
                "تم إنشاء بداية الشهر بنجاح! " .
                "المخزن الرئيسي: {$results['main_inventory']} منتج، " .
                "الفروع: {$results['branch_inventory']} منتج"
            );
            
        } catch (\Exception $e) {
            Log::error('Error generating current month starts', ['error' => $e->getMessage()]);
            
            return redirect()->back()->with('error', 
                'حدث خطأ أثناء إنشاء بداية الشهر: ' . $e->getMessage()
            );
        }
    }

    /**
     * Generate monthly starts for specific month
     */
    public function generateForMonth(Request $request)
    {
        $request->validate([
            'month' => 'required|date_format:Y-m',
            'type' => 'required|in:main,branch,both'
        ]);

        try {
            $results = $this->monthlyStartService->generateMonthlyStarts(
                $request->month,
                $request->type
            );
            
            $message = "تم إنشاء بداية الشهر {$request->month} بنجاح! ";
            
            if ($request->type === 'main' || $request->type === 'both') {
                $message .= "المخزن الرئيسي: {$results['main_inventory']} منتج ";
            }
            
            if ($request->type === 'branch' || $request->type === 'both') {
                $message .= "الفروع: {$results['branch_inventory']} منتج";
            }
            
            return redirect()->back()->with('success', $message);
            
        } catch (\Exception $e) {
            Log::error('Error generating monthly starts', [
                'month' => $request->month,
                'type' => $request->type,
                'error' => $e->getMessage()
            ]);
            
            return redirect()->back()->with('error', 
                'حدث خطأ أثناء إنشاء بداية الشهر: ' . $e->getMessage()
            );
        }
    }

    /**
     * Show monthly start report
     */
    public function report(Request $request)
    {
        $month = $request->get('month', Carbon::now()->format('Y-m'));
        
        try {
            $report = $this->monthlyStartService->getMonthlyStartReport($month);
            
            return view('pages.monthly_starts.report', compact('report'));
            
        } catch (\Exception $e) {
            Log::error('Error generating monthly start report', [
                'month' => $month,
                'error' => $e->getMessage()
            ]);
            
            return redirect()->back()->with('error', 
                'حدث خطأ أثناء إنشاء التقرير: ' . $e->getMessage()
            );
        }
    }

    /**
     * API endpoint to check if monthly starts exist
     */
    public function checkExists(Request $request)
    {
        $month = $request->get('month', Carbon::now()->format('Y-m'));
        
        try {
            $exists = $this->monthlyStartService->monthlyStartsExist($month);
            
            return response()->json([
                'success' => true,
                'month' => $month,
                'exists' => $exists
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Show detailed category report
     */
    public function categoryReport(Request $request)
    {
        $month = $request->get('month', Carbon::now()->format('Y-m'));
        
        try {
            $categoryAnalysis = $this->monthlyStartService->getCategoryAnalysis($month);
            $branchAnalysis = $this->monthlyStartService->getBranchAnalysis($month);
            
            return view('pages.monthly_starts.category_report', compact(
                'categoryAnalysis',
                'branchAnalysis',
                'month'
            ));
            
        } catch (\Exception $e) {
            Log::error('Error generating category report', [
                'month' => $month,
                'error' => $e->getMessage()
            ]);
            
            return redirect()->back()->with('error', 
                'حدث خطأ أثناء إنشاء تقرير الأقسام: ' . $e->getMessage()
            );
        }
    }

    /**
     * API endpoint to get monthly start summary
     */
    public function getSummary(Request $request)
    {
        $month = $request->get('month', Carbon::now()->format('Y-m'));
        
        try {
            $report = $this->monthlyStartService->getMonthlyStartReport($month);
            
            return response()->json([
                'success' => true,
                'month' => $month,
                'summary' => $report['summary']
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
