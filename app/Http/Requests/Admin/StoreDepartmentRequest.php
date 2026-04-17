<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreDepartmentRequest extends FormRequest
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
            'name' => ['required', 'string', 'max:255', Rule::unique('departments', 'name')],
            'code' => ['required', 'string', 'max:20', Rule::unique('departments', 'code')],
            'description' => ['nullable', 'string'],
            'is_active' => ['nullable', 'boolean'],
        ];
    }

    /**
     * Get the validated department payload.
     *
     * @return array{name: string, code: string, description?: string|null, is_active: bool}
     */
    public function departmentData(): array
    {
        /** @var array{name: string, code: string, description?: string|null} $data */
        $data = $this->safe()->only([
            'name',
            'code',
            'description',
        ]);

        $data['is_active'] = $this->boolean('is_active', true);

        return $data;
    }
}
