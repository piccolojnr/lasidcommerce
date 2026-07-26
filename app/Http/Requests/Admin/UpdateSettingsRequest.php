<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class UpdateSettingsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'settings' => ['required', 'array', 'min:1'],
            'settings.*.key' => [
                'required',
                'string',
                'max:255',
                Rule::in([
                    'catalog.new_arrival_window_days',
                    'storefront.announcement.enabled',
                    'storefront.announcement.message',
                    'storefront.announcement.cta_label',
                    'storefront.announcement.cta_url',
                    'storefront.announcement.variant',
                    'storefront.announcement.starts_at',
                    'storefront.announcement.ends_at',
                ]),
            ],
            'settings.*.value' => ['nullable'],
            'settings.*.type' => ['nullable', 'string', 'max:100'],
            'settings.*.group' => ['nullable', 'string', 'max:100'],
        ];
    }

    protected function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator) {
            $settings = collect($this->input('settings', []))->keyBy('key');
            $values = $settings->map(fn (array $setting) => $setting['value'] ?? null);

            foreach ([
                'storefront.announcement.message' => ['string', 255],
                'storefront.announcement.cta_label' => ['string', 80],
                'storefront.announcement.cta_url' => ['string', 2048],
            ] as $key => [, $max]) {
                $value = $values->get($key);

                if (filled($value) && (! is_string($value) || strlen($value) > $max)) {
                    $validator->errors()->add('settings', "The {$key} setting is invalid.");
                }
            }

            $variant = $values->get('storefront.announcement.variant');
            if (filled($variant) && ! in_array($variant, ['default', 'success', 'sale', 'warning'], true)) {
                $validator->errors()->add('settings', 'The announcement style is invalid.');
            }

            $startsAt = $values->get('storefront.announcement.starts_at');
            $endsAt = $values->get('storefront.announcement.ends_at');
            if (filled($startsAt) && strtotime($startsAt) === false) {
                $validator->errors()->add('settings', 'The announcement start date is invalid.');
            }
            if (filled($endsAt) && strtotime($endsAt) === false) {
                $validator->errors()->add('settings', 'The announcement end date is invalid.');
            }
            if (filled($startsAt) && filled($endsAt) && strtotime($endsAt) < strtotime($startsAt)) {
                $validator->errors()->add('settings', 'The announcement end date must be after the start date.');
            }

            $ctaLabel = $values->get('storefront.announcement.cta_label');
            $ctaUrl = $values->get('storefront.announcement.cta_url');
            if (filled($ctaLabel) xor filled($ctaUrl)) {
                $validator->errors()->add('settings', 'CTA label and URL must be provided together.');
            }
        });
    }
}
