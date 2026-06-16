<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreCommentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // Comments are open to guests and authenticated users alike.
    }

    /**
     * Guest identity is only required when nobody is signed in.
     *
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        $guest = ! $this->user();

        return [
            'author_name' => [$guest ? 'required' : 'nullable', 'string', 'max:80'],
            'author_email' => [$guest ? 'required' : 'nullable', 'email', 'max:255'],
            'body' => ['required', 'string', 'min:2', 'max:2000'],
            // Honeypot: real users never see or fill this field.
            'website' => ['prohibited'],
        ];
    }

    public function messages(): array
    {
        return [
            'website.prohibited' => 'Spam detected.',
        ];
    }

    /**
     * Trim input so whitespace-only submissions fail the min/required rules.
     */
    protected function prepareForValidation(): void
    {
        $this->merge([
            'body' => trim((string) $this->input('body')),
            'author_name' => trim((string) $this->input('author_name')),
            'author_email' => trim((string) $this->input('author_email')),
        ]);
    }
}
