<?php

namespace App\Services;

use App\Models\Product;
use App\Models\Branch;
use App\Models\Sell;
use App\Models\ProductAdded;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Font;
use PhpOffice\PhpSpreadsheet\Style\Fill;

class ExportService
{
    /**
     * Export products to Excel
     */
    public function exportProducts($filters = [])
    {
        $query = Product::with(['sub_category.category', 'unit']);

        // Apply filters
        if (isset($filters['category_id'])) {
            $query->where('category_id', $filters['category_id']);
        }

        if (isset($filters['low_stock']) && $filters['low_stock']) {
            $query->lowStock();
        }

        if (isset($filters['active_only']) && $filters['active_only']) {
            $query->active();
        }

        $products = $query->get();

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('المنتجات');

        // Set headers
        $headers = [
            'A1' => 'الكود',
            'B1' => 'اسم المنتج',
            'C1' => 'القسم',
            'D1' => 'التصنيف',
            'E1' => 'الوحدة',
            'F1' => 'الكمية',
            'G1' => 'السعر',
            'H1' => 'الحد الأدنى',
            'I1' => 'الحد الأقصى',
            'J1' => 'القيمة الإجمالية',
            'K1' => 'الحالة'
        ];

        foreach ($headers as $cell => $header) {
            $sheet->setCellValue($cell, $header);
        }

        // Style headers
        $sheet->getStyle('A1:K1')->applyFromArray([
            'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '4472C4']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER]
        ]);

        // Add data
        $row = 2;
        foreach ($products as $product) {
            $sheet->setCellValue('A' . $row, $product->code);
            $sheet->setCellValue('B' . $row, $product->name);
            $sheet->setCellValue('C' . $row, $product->sub_category->category->name ?? '');
            $sheet->setCellValue('D' . $row, $product->sub_category->name ?? '');
            $sheet->setCellValue('E' . $row, $product->unit->name ?? '');
            $sheet->setCellValue('F' . $row, $product->stock);
            $sheet->setCellValue('G' . $row, $product->price);
            $sheet->setCellValue('H' . $row, $product->min_stock);
            $sheet->setCellValue('I' . $row, $product->max_stock);
            $sheet->setCellValue('J' . $row, $product->stock * $product->price);
            $sheet->setCellValue('K' . $row, $product->is_active ? 'نشط' : 'غير نشط');
            $row++;
        }

        // Auto-size columns
        foreach (range('A', 'K') as $column) {
            $sheet->getColumnDimension($column)->setAutoSize(true);
        }

        return $this->saveSpreadsheet($spreadsheet, 'products_export_' . date('Y-m-d_H-i-s'));
    }

    /**
     * Export inventory report
     */
    public function exportInventoryReport($branchId = null)
    {
        $spreadsheet = new Spreadsheet();
        
        if ($branchId) {
            $branch = Branch::findOrFail($branchId);
            $this->exportBranchInventory($spreadsheet, $branch);
        } else {
            $this->exportMainInventory($spreadsheet);
        }

        $filename = $branchId ? "branch_{$branchId}_inventory_" : "main_inventory_";
        return $this->saveSpreadsheet($spreadsheet, $filename . date('Y-m-d_H-i-s'));
    }

    /**
     * Export main inventory
     */
    protected function exportMainInventory($spreadsheet)
    {
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('المخزن الرئيسي');

        $products = Product::with(['sub_category.category', 'unit'])->active()->get();

        // Headers
        $headers = [
            'A1' => 'الكود',
            'B1' => 'اسم المنتج',
            'C1' => 'القسم',
            'D1' => 'الكمية',
            'E1' => 'السعر',
            'F1' => 'القيمة',
            'G1' => 'حالة المخزون'
        ];

        foreach ($headers as $cell => $header) {
            $sheet->setCellValue($cell, $header);
        }

        $this->styleHeaders($sheet, 'A1:G1');

        $row = 2;
        foreach ($products as $product) {
            $sheet->setCellValue('A' . $row, $product->code);
            $sheet->setCellValue('B' . $row, $product->name);
            $sheet->setCellValue('C' . $row, $product->sub_category->category->name ?? '');
            $sheet->setCellValue('D' . $row, $product->stock);
            $sheet->setCellValue('E' . $row, $product->price);
            $sheet->setCellValue('F' . $row, $product->stock * $product->price);
            $sheet->setCellValue('G' . $row, $this->getStockStatusLabel($product->stock_status));
            $row++;
        }

        foreach (range('A', 'G') as $column) {
            $sheet->getColumnDimension($column)->setAutoSize(true);
        }
    }

    /**
     * Export branch inventory
     */
    protected function exportBranchInventory($spreadsheet, $branch)
    {
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle($branch->name);

        $branchProducts = DB::table('product_branches')
            ->join('products', 'product_branches.product_id', '=', 'products.id')
            ->join('sub_categories', 'products.category_id', '=', 'sub_categories.id')
            ->join('categories', 'sub_categories.category_id', '=', 'categories.id')
            ->leftJoin('units', 'products.unit_id', '=', 'units.id')
            ->where('product_branches.branch_id', $branch->id)
            ->select(
                'products.code',
                'products.name as product_name',
                'categories.name as category_name',
                'product_branches.qty',
                'product_branches.price',
                'units.name as unit_name'
            )
            ->get();

        // Headers
        $headers = [
            'A1' => 'الكود',
            'B1' => 'اسم المنتج',
            'C1' => 'القسم',
            'D1' => 'الكمية',
            'E1' => 'السعر',
            'F1' => 'الوحدة',
            'G1' => 'القيمة الإجمالية'
        ];

        foreach ($headers as $cell => $header) {
            $sheet->setCellValue($cell, $header);
        }

        $this->styleHeaders($sheet, 'A1:G1');

        $row = 2;
        foreach ($branchProducts as $product) {
            $sheet->setCellValue('A' . $row, $product->code);
            $sheet->setCellValue('B' . $row, $product->product_name);
            $sheet->setCellValue('C' . $row, $product->category_name);
            $sheet->setCellValue('D' . $row, $product->qty);
            $sheet->setCellValue('E' . $row, $product->price);
            $sheet->setCellValue('F' . $row, $product->unit_name);
            $sheet->setCellValue('G' . $row, $product->qty * $product->price);
            $row++;
        }

        foreach (range('A', 'G') as $column) {
            $sheet->getColumnDimension($column)->setAutoSize(true);
        }
    }

    /**
     * Export sales report
     */
    public function exportSalesReport($startDate, $endDate, $branchId = null)
    {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('تقرير المبيعات');

        $query = Sell::with(['product_branch.product', 'product_branch.branch'])
            ->whereBetween('created_at', [$startDate, $endDate]);

        if ($branchId) {
            $query->whereHas('product_branch', function ($q) use ($branchId) {
                $q->where('branch_id', $branchId);
            });
        }

        $sales = $query->orderBy('created_at', 'desc')->get();

        // Headers
        $headers = [
            'A1' => 'التاريخ',
            'B1' => 'المنتج',
            'C1' => 'الفرع',
            'D1' => 'الكمية',
            'E1' => 'السعر',
            'F1' => 'القيمة الإجمالية'
        ];

        foreach ($headers as $cell => $header) {
            $sheet->setCellValue($cell, $header);
        }

        $this->styleHeaders($sheet, 'A1:F1');

        $row = 2;
        $totalValue = 0;
        foreach ($sales as $sale) {
            $value = $sale->qty * $sale->product_branch->price;
            $totalValue += $value;

            $sheet->setCellValue('A' . $row, $sale->created_at->format('Y-m-d H:i'));
            $sheet->setCellValue('B' . $row, $sale->product_branch->product->name ?? '');
            $sheet->setCellValue('C' . $row, $sale->product_branch->branch->name ?? '');
            $sheet->setCellValue('D' . $row, $sale->qty);
            $sheet->setCellValue('E' . $row, $sale->product_branch->price);
            $sheet->setCellValue('F' . $row, $value);
            $row++;
        }

        // Add total row
        $sheet->setCellValue('E' . $row, 'الإجمالي:');
        $sheet->setCellValue('F' . $row, $totalValue);
        $sheet->getStyle('E' . $row . ':F' . $row)->applyFromArray([
            'font' => ['bold' => true],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'E2EFDA']]
        ]);

        foreach (range('A', 'F') as $column) {
            $sheet->getColumnDimension($column)->setAutoSize(true);
        }

        return $this->saveSpreadsheet($spreadsheet, 'sales_report_' . date('Y-m-d_H-i-s'));
    }

    /**
     * Export monthly starts report
     */
    public function exportMonthlyStartsReport($month)
    {
        $monthlyStartService = app(MonthlyStartService::class);
        $report = $monthlyStartService->getMonthlyStartReport($month);

        $spreadsheet = new Spreadsheet();
        
        // Main inventory sheet
        $this->createMainInventorySheet($spreadsheet, $report, $month);
        
        // Branch inventory sheet
        $this->createBranchInventorySheet($spreadsheet, $report, $month);
        
        // Summary sheet
        $this->createSummarySheet($spreadsheet, $report, $month);

        return $this->saveSpreadsheet($spreadsheet, "monthly_starts_{$month}");
    }

    /**
     * Create main inventory sheet
     */
    protected function createMainInventorySheet($spreadsheet, $report, $month)
    {
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('المخزن الرئيسي');

        $headers = [
            'A1' => 'كود المنتج',
            'B1' => 'اسم المنتج',
            'C1' => 'القسم',
            'D1' => 'بداية الشهر',
            'E1' => 'الإضافات',
            'F1' => 'المبيعات',
            'G1' => 'الرصيد الحالي'
        ];

        foreach ($headers as $cell => $header) {
            $sheet->setCellValue($cell, $header);
        }

        $this->styleHeaders($sheet, 'A1:G1');

        // Add data (you would need to implement this based on your report structure)
        // This is a placeholder - implement based on your actual report data structure

        foreach (range('A', 'G') as $column) {
            $sheet->getColumnDimension($column)->setAutoSize(true);
        }
    }

    /**
     * Create branch inventory sheet
     */
    protected function createBranchInventorySheet($spreadsheet, $report, $month)
    {
        $sheet = $spreadsheet->createSheet();
        $sheet->setTitle('الفروع');

        $headers = [
            'A1' => 'الفرع',
            'B1' => 'المنتج',
            'C1' => 'القسم',
            'D1' => 'بداية الشهر',
            'E1' => 'الإضافات',
            'F1' => 'المبيعات',
            'G1' => 'الرصيد الحالي'
        ];

        foreach ($headers as $cell => $header) {
            $sheet->setCellValue($cell, $header);
        }

        $this->styleHeaders($sheet, 'A1:G1');

        foreach (range('A', 'G') as $column) {
            $sheet->getColumnDimension($column)->setAutoSize(true);
        }
    }

    /**
     * Create summary sheet
     */
    protected function createSummarySheet($spreadsheet, $report, $month)
    {
        $sheet = $spreadsheet->createSheet();
        $sheet->setTitle('الملخص');

        $sheet->setCellValue('A1', 'ملخص بداية الشهر - ' . $month);
        $sheet->getStyle('A1')->applyFromArray([
            'font' => ['bold' => true, 'size' => 16]
        ]);

        $summaryData = [
            ['البيان', 'القيمة'],
            ['عدد منتجات المخزن الرئيسي', $report['summary']['main_products_count'] ?? 0],
            ['عدد منتجات الفروع', $report['summary']['branch_products_count'] ?? 0],
            ['إجمالي كمية المخزن الرئيسي', $report['summary']['total_main_qty'] ?? 0],
            ['إجمالي كمية الفروع', $report['summary']['total_branch_qty'] ?? 0]
        ];

        $row = 3;
        foreach ($summaryData as $data) {
            $sheet->setCellValue('A' . $row, $data[0]);
            $sheet->setCellValue('B' . $row, $data[1]);
            $row++;
        }

        $this->styleHeaders($sheet, 'A3:B3');
        $sheet->getColumnDimension('A')->setAutoSize(true);
        $sheet->getColumnDimension('B')->setAutoSize(true);
    }

    /**
     * Style headers
     */
    protected function styleHeaders($sheet, $range)
    {
        $sheet->getStyle($range)->applyFromArray([
            'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '4472C4']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER]
        ]);
    }

    /**
     * Get stock status label
     */
    protected function getStockStatusLabel($status)
    {
        $labels = [
            'normal' => 'طبيعي',
            'low_stock' => 'مخزون منخفض',
            'out_of_stock' => 'نفد المخزون',
            'overstock' => 'مخزون زائد'
        ];

        return $labels[$status] ?? 'غير محدد';
    }

    /**
     * Save spreadsheet and return file path
     */
    protected function saveSpreadsheet($spreadsheet, $filename)
    {
        $writer = new Xlsx($spreadsheet);
        $filepath = storage_path("app/exports/{$filename}.xlsx");
        
        // Create exports directory if it doesn't exist
        if (!file_exists(dirname($filepath))) {
            mkdir(dirname($filepath), 0755, true);
        }

        $writer->save($filepath);

        return [
            'success' => true,
            'filename' => $filename . '.xlsx',
            'filepath' => $filepath,
            'size' => filesize($filepath)
        ];
    }

    /**
     * Export to CSV format
     */
    public function exportToCsv($data, $headers, $filename)
    {
        $filepath = storage_path("app/exports/{$filename}.csv");
        
        if (!file_exists(dirname($filepath))) {
            mkdir(dirname($filepath), 0755, true);
        }

        $file = fopen($filepath, 'w');
        
        // Add BOM for UTF-8
        fwrite($file, "\xEF\xBB\xBF");
        
        // Add headers
        fputcsv($file, $headers);
        
        // Add data
        foreach ($data as $row) {
            fputcsv($file, $row);
        }
        
        fclose($file);

        return [
            'success' => true,
            'filename' => $filename . '.csv',
            'filepath' => $filepath,
            'size' => filesize($filepath)
        ];
    }

    /**
     * Clean old export files
     */
    public function cleanOldExports($daysToKeep = 7)
    {
        $exportPath = storage_path('app/exports');
        
        if (!is_dir($exportPath)) {
            return 0;
        }

        $files = glob($exportPath . '/*');
        $deletedCount = 0;
        $cutoffTime = time() - ($daysToKeep * 24 * 60 * 60);

        foreach ($files as $file) {
            if (is_file($file) && filemtime($file) < $cutoffTime) {
                unlink($file);
                $deletedCount++;
            }
        }

        return $deletedCount;
    }
}
