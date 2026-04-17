<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class ApproveAssetRequestRequest extends FormRequest
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
        $requestedQuantity = $assetRequest?->quantity_requested ?? 0;

        return [
            'quantity_approved' => ['required', 'integer', 'min:1', 'max:'.$requestedQuantity],
            'admin_comment' => ['nullable', 'string'],
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

            if ($this->integer('quantity_approved') > $availableQuantity) {
                $validator->errors()->add(
                    'quantity_approved',
                    'The approved quantity may not exceed the current available stock.',
                );
            }
        });
    }

    /**
     * Get the validated approval payload.
     *
     * @return array{quantity_approved: int, admin_comment?: string|null}
     */
    public function approvalData(): array
    {
        /** @var array{quantity_approved: int, admin_comment?: string|null} $data */
        $data = $this->safe()->only([
            'quantity_approved',
            'admin_comment',
        ]);

        return $data;
    }
}
