<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class StoreMediaRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // Route is already protected by the auth middleware.
    }

    /**
     * @return array<string, array<int, string>>
     */
    public function rules(): array
    {
        return [
            // Images only, max 5MB (NFR-05, NFR-06).
            'file' => ['required', 'file', 'image', 'mimes:jpg,jpeg,png,webp,gif', 'max:5120'],
        ];
    }

    /**
     * Return an EditorJS-compatible JSON error for XHR uploads. The global
     * handler only renders JSON for api/* routes, so we handle it explicitly.
     */
    protected function failedValidation(Validator $validator): void
    {
        if ($this->expectsJson()) {
            throw new HttpResponseException(response()->json([
                'success' => 0,
                'message' => $validator->errors()->first('file'),
                'errors' => $validator->errors(),
            ], 422));
        }

        parent::failedValidation($validator);
    }
}
