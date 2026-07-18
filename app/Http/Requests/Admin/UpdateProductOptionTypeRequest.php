<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateProductOptionTypeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('product_option_types', 'name')
                    ->where('product_id', $this->route('option_type')?->product_id)
                    ->ignore($this->route('option_type')?->getKey()),
            ],
        ];
    }
}
