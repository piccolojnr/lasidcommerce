<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateProductOptionValueRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'value' => [
                'required',
                'string',
                'max:255',
                Rule::unique('product_option_values', 'value')
                    ->where('option_type_id', $this->route('value')?->option_type_id)
                    ->ignore($this->route('value')?->getKey()),
            ],
        ];
    }
}
