<?php

namespace App\Http\Requests\Staff;

use App\Enums\AssetStatusEnum;
use App\Enums\RequestPriorityEnum;
use App\Models\AssetRequest;
use Illuminate\Database\Query\Builder;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreAssetRequestRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()?->can('create', AssetRequest::class) ?? false;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, array<int, mixed>>
     */
    public function rules(): array
    {
        $user = $this->user();

        return [
            'asset_id' => [
                'required',
                'integer',
                Rule::exists('assets', 'id')->where(function (Builder $query) use ($user): void {
                    $query->where('status', AssetStatusEnum::ACTIVE->value)
                        ->where(function (Builder $departmentQuery) use ($user): void {
                            if ($user?->department_id !== null) {
                                $departmentQuery->where('department_id', $user->department_id)
                                    ->orWhereNull('department_id');

                                return;
                            }

                            $departmentQuery->whereNull('department_id');
                        });
                }),
            ],
            'quantity_requested' => ['required', 'integer', 'min:1'],
            'needed_by_date' => ['nullable', 'date', 'after_or_equal:today'],
            'priority' => ['required', Rule::enum(RequestPriorityEnum::class)],
            'reason' => ['required', 'string'],
        ];
    }

    /**
     * Get the validated request payload used by the staff request workflow.
     *
     * @return array{asset_id: int, quantity_requested: int, needed_by_date?: string|null, priority: string, reason: string}
     */
    public function workflowData(): array
    {
        /** @var array{asset_id: int, quantity_requested: int, needed_by_date?: string|null, priority: string, reason: string} $data */
        $data = $this->safe()->only([
            'asset_id',
            'quantity_requested',
            'needed_by_date',
            'priority',
            'reason',
        ]);

        return $data;
    }
}
