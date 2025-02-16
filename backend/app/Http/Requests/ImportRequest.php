<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ImportRequest extends FormRequest
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
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'file' => [
                'required',
                'file',
                'mimes:csv,txt',
                'max:10240', // 10MB max
            ],
            'client_id' => 'sometimes|uuid'
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'client_id' => auth()->user()->client_id
        ]);
    }

    public function messages(): array
    {
        return [
            'file.required' => 'Please provide a CSV file',
            'file.mimes' => 'The file must be a CSV file',
            'file.max' => 'The file size must not exceed 10MB'
        ];
    }
}
