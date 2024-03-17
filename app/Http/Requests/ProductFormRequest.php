<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ProductFormRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'product_reference' => 'required|max:30',
            'list_description' => 'required|max:255',
            'product_description' => 'required|max:255',
            'purchase_price' => 'required|numeric|between:0,999999.999',
            'sale_price' => 'required|numeric|between:0,999999.999',
            'weight' => 'required|numeric|between:0,999999.999',
            'length' => 'required|numeric|between:0,999999.999',
            'width' => 'required|numeric|between:0,999999.999',
            'height' => 'required|numeric|between:0,999999.999',
            'cubic_meter' => 'numeric|between:0,999999.9999',
            'quantity' => 'required|numeric|min:0',
            'stock' => 'numeric|min:0'
        ];
    }
}
