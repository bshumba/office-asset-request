<?php

namespace App\Http\Requests\Reports;

use App\Enums\AssetRequestStatusEnum;
use App\Enums\RequestPriorityEnum;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class RequestReportRequest extends FormRequest
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
                static fn (AssetRequestStatusEnum $status): string => $status->value,
                AssetRequestStatusEnum::cases(),
            ))],
            'priority' => ['nullable', Rule::in(array_map(
                static fn (RequestPriorityEnum $priority): string => $priority->value,
                RequestPriorityEnum::cases(),
            ))],
            'from' => ['nullable', 'date'],
            'to' => ['nullable', 'date', 'after_or_equal:from'],
        ];
    }

    /**
     * Get the validated request report filters.
     *
     * @return array{
     *     search?: string,
     *     department_id?: int,
     *     status?: string,
     *     priority?: string,
     *     from?: string,
     *     to?: string
     * }
     */
    public function filters(): array
    {
        /** @var array{
         *     search?: string,
         *     department_id?: int,
         *     status?: string,
         *     priority?: string,
         *     from?: string,
         *     to?: string
         * } $filters
         */
        $filters = $this->safe()->only([
            'search',
            'department_id',
            'status',
            'priority',
            'from',
            'to',
        ]);

        return array_filter(
            $filters,
            static fn (mixed $value): bool => $value !== null && $value !== '',
        );
    }
}
