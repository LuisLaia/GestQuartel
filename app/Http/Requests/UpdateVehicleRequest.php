<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateVehicleRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $vehicle = $this->route('vehicle');
        $vehicleId = is_object($vehicle) ? $vehicle->id : $vehicle;

        return [
            'name' => ['required', 'string', 'max:255'],
            'plate' => [
                'nullable',
                'string',
                'max:50',
                Rule::unique('vehicles', 'plate')->ignore($vehicleId),
            ],
            'type' => ['nullable', 'string', 'max:100'],
            'year' => ['nullable', 'integer', 'min:1900', 'max:'.((int) date('Y') + 1)],
            'status' => ['required', 'in:operacional,manutencao,inativo'],
            'notes' => ['nullable', 'string'],
        ];
    }
}
