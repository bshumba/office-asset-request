<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class RejectAssetRequestRequest extends FormRequest
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
            'rejection_reason' => ['required', 'string'],
            'admin_comment' => ['nullable', 'string'],
        ];
    }

    /**
     * Get the validated rejection payload.
     *
     * @return array{rejection_reason: string, admin_comment?: string|null}
     */
    public function rejectionData(): array
    {
        /** @var array{rejection_reason: string, admin_comment?: string|null} $data */
        $data = $this->safe()->only([
            'rejection_reason',
            'admin_comment',
        ]);

        return $data;
    }
}
