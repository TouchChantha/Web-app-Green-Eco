<?php

namespace App\Http\Requests\Order;

use Illuminate\Foundation\Http\FormRequest;

class UpdateOrderRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'recipient_name'    => 'sometimes|string|max:255',
            'recipient_phone'   => 'sometimes|string|max:20',
            'pickup_address'    => 'sometimes|string',
            'pickup_lat'        => 'nullable|numeric|between:-90,90',
            'pickup_lng'        => 'nullable|numeric|between:-180,180',
            'delivery_address'  => 'sometimes|string',
            'delivery_lat'      => 'nullable|numeric|between:-90,90',
            'delivery_lng'      => 'nullable|numeric|between:-180,180',
            'priority'          => 'sometimes|in:low,normal,high,urgent',
            'scheduled_at'      => 'nullable|date',
            'notes'             => 'nullable|string',
        ];
    }
}
