<?php

namespace App\Http\Requests\Driver;

use Illuminate\Foundation\Http\FormRequest;

class StoreDriverRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name'           => 'required|string|max:255',
            'email'          => 'required|email|unique:users,email',
            'password'       => 'required|string|min:8|confirmed',
            'phone'          => 'nullable|string|max:20',
            'license_number' => 'required|string|max:50',
            'vehicle_type'   => 'required|in:motorcycle,car,van,truck',
            'vehicle_plate'  => 'required|string|max:20',
        ];
    }
}
