<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreOrderRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return false;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'customer_email'     => 'required|email',
            'shipping_address.street'  => 'required|string',
            'shipping_address.city'    => 'required|string',
            'shipping_address.zip'     => 'required|string',
            'shipping_address.country' => 'required|string',
            'items.*.product_id'       => 'required|integer',
            'items.*.quantity'         => 'required|integer|min:1',
            'items.*.price'            => 'required|numeric',
            'coupon_code'              => 'string|nullable',
            'notes'                    => 'string|nullable',
        ];
    }
}
