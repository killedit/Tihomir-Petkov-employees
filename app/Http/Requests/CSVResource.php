<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CSVResource extends FormRequest
{
    public function rules(): array
    {
        return [
            'file' => 'required|file|mimes:csv,txt|max:10240',
        ];
    }

    public function messages(): array
    {
        return [
            'file.required' => 'Please select a CSV file.',
            'file.file' => 'The uploaded item must be a file.',
            'file.mimes' => 'The file must be a CSV file (.csv or .txt).',
            'file.max' => 'The file size must not exceed 10MB.',
        ];
    }
}
