<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CSVRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'file' => 'required|file|mimes:csv|mimetypes:text/csv,application/csv|max:10240',
        ];
    }

    public function messages(): array
    {
        return [
            'file.required' => 'Please select a CSV file.',
            'file.file'     => 'The uploaded item must be a file.',
            'file.mimes'    => 'The file must be a CSV file (.csv).',
            'file.max'      => 'The file size must not exceed 10MB.',
        ];
    }
}
