<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Branch;
use App\Models\ProductRequest;
use App\Models\Category;
use App\Models\SubCategory;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class SearchController extends Controller
{
    /**
     * Global search across the system
     */
    public function globalSearch(Request $request): JsonResponse
    {
        try {
            $query = $request->input('query');
            $limit = $request->input('limit', 10);

            if (empty($query) || strlen($query) < 2) {
                return response()->json([
                    'success' => true,
                    'results' => [],
                    'message' => 'يرجى إدخال حرفين على الأقل للبحث'
                ]);
            }

            $results = collect();

            // Search Products
            $products = Product::with(['sub_category.category', 'unit'])
                ->where(function ($q) use ($query) {
                    $q->where('name', 'like', "%{$query}%")
                      ->orWhere('code', 'like', "%{$query}%");
                })
                ->active()
                ->limit($limit)
                ->get();

            foreach ($products as $product) {
                $results->push([
                    'type' => 'product',
                    'id' => $product->id,
                    'title' => $product->name,
                    'subtitle' => "كود: {$product->code} | مخزون: {$product->stock} " . ($product->unit ? $product->unit->name : ''),
                    'description' => optional($product->sub_category->category)->name ?? '',
                    'url' => route('show products') . '?search=' . urlencode($product->name),
                    'icon' => 'ti-box',
                    'badge' => $product->stock_status === 'low_stock' ? 'مخزون منخفض' : null,
                    'badge_color' => $product->stock_status === 'low_stock' ? 'warning' : null
                ]);
            }

            // Search Branches
            $branches = Branch::where('name', 'like', "%{$query}%")
                ->limit(5)
                ->get();

            foreach ($branches as $branch) {
                $results->push([
                    'type' => 'branch',
                    'id' => $branch->id,
                    'title' => $branch->name,
                    'subtitle' => 'فرع',
                    'description' => 'عرض مخزون الفرع',
                    'url' => route('inventory', $branch->id),
                    'icon' => 'ti-building-warehouse',
                    'badge' => null,
                    'badge_color' => null
                ]);
            }

            // Search Categories
            $categories = Category::where('name', 'like', "%{$query}%")
                ->limit(5)
                ->get();

            foreach ($categories as $category) {
                $results->push([
                    'type' => 'category',
                    'id' => $category->id,
                    'title' => $category->name,
                    'subtitle' => 'قسم',
                    'description' => 'عرض منتجات القسم',
                    'url' => route('show products') . '?category=' . $category->id,
                    'icon' => 'ti-category',
                    'badge' => null,
                    'badge_color' => null
                ]);
            }

            // Search Product Requests
            $requests = ProductRequest::with(['branch'])
                ->where('request_number', 'like', "%{$query}%")
                ->orWhereHas('branch', function ($q) use ($query) {
                    $q->where('name', 'like', "%{$query}%");
                })
                ->limit(5)
                ->get();

            foreach ($requests as $request) {
                $results->push([
                    'type' => 'request',
                    'id' => $request->id,
                    'title' => "طلب رقم {$request->request_number}",
                    'subtitle' => "فرع {$request->branch->name}",
                    'description' => $request->status_label,
                    'url' => route('product-requests.show', $request),
                    'icon' => 'ti-file-text',
                    'badge' => $request->status_label,
                    'badge_color' => $request->status_color
                ]);
            }

            // Sort results by relevance (products first, then by name)
            $sortedResults = $results->sortBy(function ($item) {
                $typeOrder = ['product' => 1, 'branch' => 2, 'category' => 3, 'request' => 4];
                return $typeOrder[$item['type']] . $item['title'];
            })->take($limit)->values();

            return response()->json([
                'success' => true,
                'results' => $sortedResults,
                'total_count' => $sortedResults->count(),
                'query' => $query
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'خطأ في البحث',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Quick search for products (for autocomplete)
     */
    public function quickProductSearch(Request $request): JsonResponse
    {
        try {
            $query = $request->input('query');
            $limit = $request->input('limit', 5);

            if (empty($query) || strlen($query) < 2) {
                return response()->json([
                    'success' => true,
                    'products' => []
                ]);
            }

            $products = Product::with(['unit'])
                ->where(function ($q) use ($query) {
                    $q->where('name', 'like', "%{$query}%")
                      ->orWhere('code', 'like', "%{$query}%");
                })
                ->active()
                ->limit($limit)
                ->get()
                ->map(function ($product) {
                    return [
                        'id' => $product->id,
                        'name' => $product->name,
                        'code' => $product->code,
                        'stock' => $product->stock,
                        'price' => $product->price,
                        'unit' => $product->unit->name ?? '',
                        'stock_status' => $product->stock_status,
                        'is_low_stock' => $product->is_low_stock
                    ];
                });

            return response()->json([
                'success' => true,
                'products' => $products
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'خطأ في البحث السريع',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get search suggestions
     */
    public function getSearchSuggestions(Request $request): JsonResponse
    {
        try {
            $query = $request->input('query');
            $limit = $request->input('limit', 5);

            if (empty($query) || strlen($query) < 2) {
                return response()->json([
                    'success' => true,
                    'suggestions' => []
                ]);
            }

            $suggestions = collect();

            // Product name suggestions
            $productNames = Product::where('name', 'like', "%{$query}%")
                ->active()
                ->pluck('name')
                ->unique()
                ->take($limit);

            foreach ($productNames as $name) {
                $suggestions->push([
                    'text' => $name,
                    'type' => 'product',
                    'icon' => 'ti-box'
                ]);
            }

            // Product code suggestions
            $productCodes = Product::where('code', 'like', "%{$query}%")
                ->active()
                ->pluck('code')
                ->unique()
                ->take(3);

            foreach ($productCodes as $code) {
                $suggestions->push([
                    'text' => $code,
                    'type' => 'code',
                    'icon' => 'ti-barcode'
                ]);
            }

            // Branch name suggestions
            $branchNames = Branch::where('name', 'like', "%{$query}%")
                ->pluck('name')
                ->unique()
                ->take(3);

            foreach ($branchNames as $name) {
                $suggestions->push([
                    'text' => $name,
                    'type' => 'branch',
                    'icon' => 'ti-building-warehouse'
                ]);
            }

            return response()->json([
                'success' => true,
                'suggestions' => $suggestions->take($limit)->values()
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'خطأ في تحميل الاقتراحات',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
