<?php

namespace App\Http\Requests\Dashboard;

use Illuminate\Foundation\Http\FormRequest;

class DashboardFilterRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, array<int, mixed>>
     */
    public function rules(): array
    {
        return [
            'from' => ['nullable', 'date'],
            'to' => ['nullable', 'date', 'after_or_equal:from'],
        ];
    }

    /**
     * Get the validated dashboard filters.
     *
     * @return array{from?: string, to?: string}
     */
    public function filters(): array
    {
        /** @var array{from?: string, to?: string} $filters */
        $filters = $this->safe()->only([
            'from',
            'to',
        ]);

        return array_filter(
            $filters,
            static fn (mixed $value): bool => $value !== null && $value !== '',
        );
    }
}
