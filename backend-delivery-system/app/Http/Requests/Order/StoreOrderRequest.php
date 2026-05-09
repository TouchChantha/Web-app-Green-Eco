<?php

namespace App\Http\Requests\Order;

use Illuminate\Foundation\Http\FormRequest;

class StoreOrderRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'recipient_name'    => 'required|string|max:255',
            'recipient_phone'   => 'required|string|max:20',
            'pickup_address'    => 'required|string',
            'pickup_lat'        => 'nullable|numeric|between:-90,90',
            'pickup_lng'        => 'nullable|numeric|between:-180,180',
            'delivery_address'  => 'required|string',
            'delivery_lat'      => 'nullable|numeric|between:-90,90',
            'delivery_lng'      => 'nullable|numeric|between:-180,180',
            'priority'          => 'nullable|in:low,normal,high,urgent',
            'scheduled_at'      => 'nullable|date',
            'notes'             => 'nullable|string',
        ];
    }
}
