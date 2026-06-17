<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreTripRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'external_id' => ['nullable', 'string', 'max:255', 'unique:trips,external_id'],
            'vehicle_id' => ['required', 'exists:vehicles,id'],
            'driver_name' => ['nullable', 'string', 'max:255'],
            'distance_km' => ['required', 'numeric', 'min:0.01'],
            'fuel_used_liters' => ['required', 'numeric', 'min:0.01'],
            'started_at' => ['nullable', 'date'],
            'ended_at' => ['nullable', 'date', 'after_or_equal:started_at'],
        ];
    }
}
