<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class ImportCatalogCsvRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'catalog_csv' => ['required', 'file', 'mimes:csv,txt', 'max:10240'],
        ];
    }
}
