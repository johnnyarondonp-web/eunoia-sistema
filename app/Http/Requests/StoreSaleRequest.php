<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class StoreSaleRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'items' => ['required', 'array', 'min:1'],
            'items.*.product_id' => ['required', 'exists:products,id'],
            'items.*.quantity' => ['required', 'integer', 'min:1'],
            'bcv_rate' => ['nullable', 'numeric', 'min:1', 'max:9999']
        ];
    }

    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            $ids = collect($this->input('items', []))->pluck('product_id')->filter();
            if ($ids->count() !== $ids->unique()->count()) {
                $validator->errors()->add('items', 'No se puede registrar el mismo producto dos veces en una venta.');
            }
        });
    }
}
