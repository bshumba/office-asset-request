<?php

namespace App\Http\Requests\Admin;

use App\Enums\UserStatusEnum;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

class StoreManagedUserRequest extends FormRequest
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
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', Rule::unique('users', 'email')],
            'role' => ['required', Rule::in(['Department Manager', 'Staff'])],
            'department_id' => ['required', 'integer', Rule::exists('departments', 'id')],
            'status' => ['required', Rule::in(array_map(
                static fn (UserStatusEnum $status): string => $status->value,
                UserStatusEnum::cases(),
            ))],
            'password' => ['required', 'confirmed', Password::min(8)],
            'notes' => ['nullable', 'string'],
        ];
    }

    /**
     * Get the validated user payload.
     *
     * @return array{
     *     name: string,
     *     email: string,
     *     role: string,
     *     department_id: int,
     *     status: string,
     *     password: string,
     *     notes?: string|null
     * }
     */
    public function userData(): array
    {
        /** @var array{
         *     name: string,
         *     email: string,
         *     role: string,
         *     department_id: int,
         *     status: string,
         *     password: string,
         *     notes?: string|null
         * } $data
         */
        $data = $this->safe()->only([
            'name',
            'email',
            'role',
            'department_id',
            'status',
            'password',
            'notes',
        ]);

        return $data;
    }
}
