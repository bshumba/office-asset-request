<?php

namespace App\Http\Requests\Manager;

use Illuminate\Foundation\Http\FormRequest;

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
            'manager_comment' => ['nullable', 'string'],
        ];
    }

    /**
     * Get the validated review payload.
     *
     * @return array{quantity_approved: int, manager_comment?: string|null}
     */
    public function reviewData(): array
    {
        /** @var array{quantity_approved: int, manager_comment?: string|null} $data */
        $data = $this->safe()->only([
            'quantity_approved',
            'manager_comment',
        ]);

        return $data;
    }
}
