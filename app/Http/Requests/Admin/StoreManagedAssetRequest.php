<?php

namespace App\Http\Requests\Admin;

use App\Enums\AssetStatusEnum;
use App\Enums\AssetUnitTypeEnum;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreManagedAssetRequest extends FormRequest
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
            'asset_category_id' => ['required', 'integer', Rule::exists('asset_categories', 'id')],
            'department_id' => ['required', 'integer', Rule::exists('departments', 'id')],
            'name' => ['required', 'string', 'max:255'],
            'asset_code' => ['required', 'string', 'max:100', Rule::unique('assets', 'asset_code')],
            'brand' => ['nullable', 'string', 'max:100'],
            'model' => ['nullable', 'string', 'max:100'],
            'serial_number' => ['nullable', 'string', 'max:150'],
            'description' => ['nullable', 'string'],
            'unit_type' => ['required', Rule::in(array_map(
                static fn (AssetUnitTypeEnum $unitType): string => $unitType->value,
                AssetUnitTypeEnum::cases(),
            ))],
            'quantity_total' => ['required', 'integer', 'min:1'],
            'quantity_available' => ['required', 'integer', 'min:0', 'lte:quantity_total'],
            'reorder_level' => ['required', 'integer', 'min:0', 'lte:quantity_total'],
            'track_serial' => ['nullable', 'boolean'],
            'status' => ['required', Rule::in(array_map(
                static fn (AssetStatusEnum $status): string => $status->value,
                AssetStatusEnum::cases(),
            ))],
            'purchase_date' => ['nullable', 'date'],
            'notes' => ['nullable', 'string'],
        ];
    }

    /**
     * Get the validated asset payload.
     *
     * @return array<string, mixed>
     */
    public function assetData(): array
    {
        /** @var array<string, mixed> $data */
        $data = $this->safe()->only([
            'asset_category_id',
            'department_id',
            'name',
            'asset_code',
            'brand',
            'model',
            'serial_number',
            'description',
            'unit_type',
            'quantity_total',
            'quantity_available',
            'reorder_level',
            'status',
            'purchase_date',
            'notes',
        ]);

        $data['track_serial'] = $this->boolean('track_serial');

        return $data;
    }
}
