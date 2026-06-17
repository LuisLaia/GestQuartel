<?php

namespace App\Http\Requests;

use App\Models\UniformItemSize;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreUniformAssignmentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'person_id' => ['required', Rule::exists('people', 'id')->where('status', 'ativo')],
            'uniform_item_id' => ['required', 'exists:uniform_items,id'],
            'size' => ['required', 'string', 'max:50'],
            'quantity' => ['required', 'integer', 'min:1'],
            'suggested_size' => ['nullable', 'string', 'max:20'],
            'chosen_size' => ['nullable', 'string', 'max:20'],
            'assigned_at' => ['required', 'date'],
            'notes' => ['nullable', 'string'],
        ];
    }

    protected function prepareForValidation(): void
    {
        if ($this->filled('size') && ! $this->filled('chosen_size')) {
            $this->merge(['chosen_size' => $this->input('size')]);
        }
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator): void {
            $itemId = $this->input('uniform_item_id');
            $size = $this->input('size');
            $quantity = (int) $this->input('quantity');

            if (! $itemId || ! $size || $quantity <= 0) {
                return;
            }

            $itemSize = UniformItemSize::where('uniform_item_id', $itemId)
                ->where('size', $size)
                ->first();

            if (! $itemSize) {
                $validator->errors()->add('size', 'Tamanho indisponivel para esta peca.');

                return;
            }

            if ($itemSize->stock < $quantity) {
                $validator->errors()->add('quantity', 'Stock insuficiente.');
            }
        });
    }
}
