<?php

namespace App\Http\Requests;

use Carbon\Carbon;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class AnalyticsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return in_array($this->user()?->role, ['cho', 'rhu'], true);
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'from' => $this->input('from') ?: now()->subMonthsNoOverflow(11)->startOfMonth()->toDateString(),
            'to' => $this->input('to') ?: today()->toDateString(),
            'barangay' => $this->input('barangay') ?: null,
            'rhu' => $this->input('rhu') ?: null,
        ]);
    }

    public function rules(): array
    {
        return [
            'from' => ['required', 'date_format:Y-m-d', 'after_or_equal:1900-01-01', 'before_or_equal:to'],
            'to' => ['required', 'date_format:Y-m-d', 'before_or_equal:today'],
            'barangay' => ['nullable', 'string', 'max:150'],
            'rhu' => ['nullable', \Illuminate\Validation\Rule::in(\App\Services\AnalyticsScope::RHUS)],
            'page' => ['sometimes', 'integer', 'min:1', 'max:1000000'],
            'question' => [$this->isMethod('post') ? 'required' : 'sometimes', 'string', 'max:500'],
        ];
    }

    public function after(): array
    {
        return [function (Validator $validator) {
            if (! $validator->errors()->has('from') && ! $validator->errors()->has('to')
                && Carbon::parse($this->input('from'))->addYears(3)->lt(Carbon::parse($this->input('to')))) {
                $validator->errors()->add('to', 'Choose a reporting period of three years or less.');
            }
        }];
    }
}
