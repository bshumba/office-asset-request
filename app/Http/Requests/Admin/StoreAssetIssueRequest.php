<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class StoreAssetIssueRequest extends FormRequest
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
        $assetRequest = $this->route('assetRequest');
        $approvedQuantity = $assetRequest?->quantity_approved ?? 0;

        return [
            'quantity_issued' => ['required', 'integer', 'min:1', 'max:'.$approvedQuantity],
            'expected_return_date' => ['nullable', 'date', 'after_or_equal:today'],
            'notes' => ['nullable', 'string'],
        ];
    }

    /**
     * Configure the validator instance.
     */
    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator): void {
            $assetRequest = $this->route('assetRequest');
            $availableQuantity = $assetRequest?->asset?->quantity_available ?? 0;

            if ($this->integer('quantity_issued') > $availableQuantity) {
                $validator->errors()->add(
                    'quantity_issued',
                    'The issued quantity may not exceed the current available stock.',
                );
            }
        });
    }

    /**
     * Get the validated issue payload.
     *
     * @return array{quantity_issued: int, expected_return_date?: string|null, notes?: string|null}
     */
    public function issueData(): array
    {
        /** @var array{quantity_issued: int, expected_return_date?: string|null, notes?: string|null} $data */
        $data = $this->safe()->only([
            'quantity_issued',
            'expected_return_date',
            'notes',
        ]);

        return $data;
    }
}
