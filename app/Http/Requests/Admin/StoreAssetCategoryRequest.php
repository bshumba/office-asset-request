<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreAssetCategoryRequest extends FormRequest
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
            'name' => ['required', 'string', 'max:255', Rule::unique('asset_categories', 'name')],
            'description' => ['nullable', 'string'],
            'is_active' => ['nullable', 'boolean'],
        ];
    }

    /**
     * Get the validated asset category payload.
     *
     * @return array{name: string, description?: string|null, is_active: bool}
     */
    public function categoryData(): array
    {
        /** @var array{name: string, description?: string|null} $data */
        $data = $this->safe()->only([
            'name',
            'description',
        ]);

        $data['is_active'] = $this->boolean('is_active', true);

        return $data;
    }
}
