<?php

namespace App\Http\Requests\Admin;

use App\Enums\StockAdjustmentReasonEnum;
use App\Enums\StockAdjustmentTypeEnum;
use App\Models\Asset;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class StoreAssetAdjustmentRequest extends FormRequest
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
            'asset_id' => ['required', 'integer', Rule::exists('assets', 'id')],
            'type' => ['required', Rule::enum(StockAdjustmentTypeEnum::class)],
            'quantity' => ['required', 'integer', 'min:1'],
            'reason' => ['required', Rule::enum(StockAdjustmentReasonEnum::class)],
            'reference' => ['nullable', 'string'],
            'note' => ['nullable', 'string'],
        ];
    }

    /**
     * Configure the validator instance.
     */
    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator): void {
            $asset = Asset::query()->find($this->integer('asset_id'));

            if (! $asset instanceof Asset) {
                return;
            }

            if (
                $this->input('type') === StockAdjustmentTypeEnum::DECREASE->value
                && $this->integer('quantity') > $asset->quantity_available
            ) {
                $validator->errors()->add(
                    'quantity',
                    'The decrease quantity may not exceed the current available stock.',
                );
            }
        });
    }

    /**
     * Get the validated stock adjustment payload.
     *
     * @return array{asset_id: int, type: string, quantity: int, reason: string, reference?: string|null, note?: string|null}
     */
    public function adjustmentData(): array
    {
        /** @var array{asset_id: int, type: string, quantity: int, reason: string, reference?: string|null, note?: string|null} $data */
        $data = $this->safe()->only([
            'asset_id',
            'type',
            'quantity',
            'reason',
            'reference',
            'note',
        ]);

        return $data;
    }
}
