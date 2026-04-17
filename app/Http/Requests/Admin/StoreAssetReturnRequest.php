<?php

namespace App\Http\Requests\Admin;

use App\Enums\ReturnConditionEnum;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreAssetReturnRequest extends FormRequest
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
        $assetIssue = $this->route('assetIssue');
        $outstandingQuantity = $assetIssue?->outstandingQuantity() ?? 0;

        return [
            'quantity_returned' => ['required', 'integer', 'min:1', 'max:'.$outstandingQuantity],
            'condition_on_return' => ['required', Rule::enum(ReturnConditionEnum::class)],
            'remarks' => ['nullable', 'string'],
        ];
    }

    /**
     * Get the validated return payload.
     *
     * @return array{quantity_returned: int, condition_on_return: string, remarks?: string|null}
     */
    public function returnData(): array
    {
        /** @var array{quantity_returned: int, condition_on_return: string, remarks?: string|null} $data */
        $data = $this->safe()->only([
            'quantity_returned',
            'condition_on_return',
            'remarks',
        ]);

        return $data;
    }
}
