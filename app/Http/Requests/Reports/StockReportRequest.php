<?php

namespace App\Http\Requests\Reports;

use App\Enums\AssetStatusEnum;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StockReportRequest extends FormRequest
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
            'search' => ['nullable', 'string', 'max:100'],
            'department_id' => ['nullable', 'integer', Rule::exists('departments', 'id')],
            'status' => ['nullable', Rule::in(array_map(
                static fn (AssetStatusEnum $status): string => $status->value,
                AssetStatusEnum::cases(),
            ))],
        ];
    }

    /**
     * Get the validated stock report filters.
     *
     * @return array{search?: string, department_id?: int, status?: string}
     */
    public function filters(): array
    {
        /** @var array{search?: string, department_id?: int, status?: string} $filters */
        $filters = $this->safe()->only([
            'search',
            'department_id',
            'status',
        ]);

        return array_filter(
            $filters,
            static fn (mixed $value): bool => $value !== null && $value !== '',
        );
    }
}
