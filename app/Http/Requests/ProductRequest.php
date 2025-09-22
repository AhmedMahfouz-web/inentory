<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ProductRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true; // Handle authorization in controllers or middleware
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        $productId = $this->route('product') ? $this->route('product')->id : null;
        
        return [
            'name' => [
                'required',
                'string',
                'max:255',
                'min:2',
                Rule::unique('products')->ignore($productId)->whereNull('deleted_at')
            ],
            'code' => [
                'required',
                'string',
                'max:50',
                Rule::unique('products')->ignore($productId)->whereNull('deleted_at')
            ],
            'description' => 'nullable|string|max:1000',
            'category_id' => 'required|exists:sub_categories,id',
            'unit_id' => 'required|exists:units,id',
            'price' => 'required|numeric|min:0|max:999999.99',
            'stock' => 'required|numeric|min:0|max:999999.99',
            'min_stock' => 'nullable|numeric|min:0|max:999999.99|lt:max_stock',
            'max_stock' => 'nullable|numeric|min:0|max:999999.99|gt:min_stock',
            'is_active' => 'boolean'
        ];
    }

    /**
     * Get custom validation messages.
     */
    public function messages(): array
    {
        return [
            'name.required' => 'اسم المنتج مطلوب',
            'name.unique' => 'اسم المنتج موجود بالفعل',
            'name.min' => 'اسم المنتج يجب أن يكون على الأقل حرفين',
            'name.max' => 'اسم المنتج لا يمكن أن يتجاوز 255 حرف',
            
            'code.required' => 'كود المنتج مطلوب',
            'code.unique' => 'كود المنتج موجود بالفعل',
            'code.max' => 'كود المنتج لا يمكن أن يتجاوز 50 حرف',
            
            'description.max' => 'الوصف لا يمكن أن يتجاوز 1000 حرف',
            
            'category_id.required' => 'التصنيف مطلوب',
            'category_id.exists' => 'التصنيف المحدد غير موجود',
            
            'unit_id.required' => 'الوحدة مطلوبة',
            'unit_id.exists' => 'الوحدة المحددة غير موجودة',
            
            'price.required' => 'السعر مطلوب',
            'price.numeric' => 'السعر يجب أن يكون رقم',
            'price.min' => 'السعر يجب أن يكون أكبر من أو يساوي صفر',
            'price.max' => 'السعر لا يمكن أن يتجاوز 999999.99',
            
            'stock.required' => 'الكمية مطلوبة',
            'stock.numeric' => 'الكمية يجب أن تكون رقم',
            'stock.min' => 'الكمية يجب أن تكون أكبر من أو تساوي صفر',
            'stock.max' => 'الكمية لا يمكن أن تتجاوز 999999.99',
            
            'min_stock.numeric' => 'الحد الأدنى للمخزون يجب أن يكون رقم',
            'min_stock.min' => 'الحد الأدنى للمخزون يجب أن يكون أكبر من أو يساوي صفر',
            'min_stock.max' => 'الحد الأدنى للمخزون لا يمكن أن يتجاوز 999999.99',
            'min_stock.lt' => 'الحد الأدنى للمخزون يجب أن يكون أقل من الحد الأقصى',
            
            'max_stock.numeric' => 'الحد الأقصى للمخزون يجب أن يكون رقم',
            'max_stock.min' => 'الحد الأقصى للمخزون يجب أن يكون أكبر من أو يساوي صفر',
            'max_stock.max' => 'الحد الأقصى للمخزون لا يمكن أن يتجاوز 999999.99',
            'max_stock.gt' => 'الحد الأقصى للمخزون يجب أن يكون أكبر من الحد الأدنى',
        ];
    }

    /**
     * Get custom attributes for validator errors.
     */
    public function attributes(): array
    {
        return [
            'name' => 'اسم المنتج',
            'code' => 'كود المنتج',
            'description' => 'الوصف',
            'category_id' => 'التصنيف',
            'unit_id' => 'الوحدة',
            'price' => 'السعر',
            'stock' => 'الكمية',
            'min_stock' => 'الحد الأدنى للمخزون',
            'max_stock' => 'الحد الأقصى للمخزون',
            'is_active' => 'الحالة'
        ];
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
        // Clean and prepare data
        if ($this->has('price')) {
            $this->merge([
                'price' => (float) str_replace(',', '', $this->price)
            ]);
        }

        if ($this->has('stock')) {
            $this->merge([
                'stock' => (float) str_replace(',', '', $this->stock)
            ]);
        }

        if ($this->has('min_stock') && $this->min_stock !== null) {
            $this->merge([
                'min_stock' => (float) str_replace(',', '', $this->min_stock)
            ]);
        }

        if ($this->has('max_stock') && $this->max_stock !== null) {
            $this->merge([
                'max_stock' => (float) str_replace(',', '', $this->max_stock)
            ]);
        }

        // Set default values
        if (!$this->has('is_active')) {
            $this->merge(['is_active' => true]);
        }
    }
}
