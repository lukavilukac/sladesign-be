<?php

namespace App\Http\Controllers\Api\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CreateOrderRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'email_address' => 'required|email|min:5|max:50',
            'first_name' => 'required|string|min:2|max:255',
            'last_name' => 'required|string|min:2|max:255',
            'city' => 'required|string|min:2|max:255',
            'country_name' => 'required|string',
            'country_code' => 'required|string',
            'phone_number' => 'required',
            'address' => 'required|string|min:2|max:255',
            'apartment' => 'present|nullable|string|max:255',
            'price' => 'required|numeric',
            'order_lines' => 'required|array',
            'order_lines.*.price' => 'required|numeric',
            'order_lines.*.product_id' => 'required|integer|exists:products,id',
            'order_lines.*.quantity' => 'required|integer',
            'order_lines.*.modifier_ids' => ['nullable', 'array', 'exists:modifiers,id'],
        ];
    }
}
