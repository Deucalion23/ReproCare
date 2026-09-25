<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreSupplyRequest extends FormRequest
{
    public function authorize(): bool { return $this->user()?->role === 'rhu'; }

    public function rules(): array
    {
        $categories = config('supply_catalog.categories');
        $category = $this->input('supply_category');
        $items = is_string($category) ? ($categories[$category]['items'] ?? []) : [];
        $item = is_string($this->input('supply_name')) ? $this->input('supply_name') : '';
        $units = $item === '__other__' ? config('supply_catalog.units') : ($items[$item] ?? []);
        return [
            'supply_category' => ['required', Rule::in(array_keys($categories))],
            'supply_name' => ['required', 'string', Rule::in([...array_keys($items), '__other__'])],
            'supply_name_other' => ['required_if:supply_name,__other__', 'nullable', 'string', 'max:255'],
            'quantity_requested' => ['required', 'integer', 'min:1', 'max:2147483647'],
            'unit' => ['required', Rule::in($units)],
            'urgency' => ['required', Rule::in(['routine', 'urgent', 'emergency'])],
            'reason' => ['required', 'string', 'max:1000'],
        ];
    }

    public function messages(): array
    {
        return ['supply_name.in' => 'Choose an item from the selected category, or choose Other item.',
            'unit.in' => 'Choose a unit of measure available for this item.',
            'supply_name_other.required_if' => 'Enter the name of the other item.'];
    }
}
