<?php

namespace App\Http\Requests;

use App\Enums\RedirectType;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreRedirectRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // Route is protected by the auth middleware.
    }

    protected function prepareForValidation(): void
    {
        $from = trim((string) $this->input('from_url'));

        // The source is always a local path; force a single leading slash.
        if ($from !== '') {
            $from = '/'.ltrim($from, '/');
        }

        $this->merge([
            'from_url' => $from,
            'to_url' => trim((string) $this->input('to_url')),
            'type' => (int) $this->input('type', RedirectType::Permanent->value),
            'is_active' => $this->boolean('is_active'),
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'from_url' => ['required', 'string', 'max:2048', 'regex:#^/#', Rule::unique('redirects', 'from_url')->ignore($this->route('redirect'))],
            'to_url' => ['required', 'string', 'max:2048'],
            'type' => ['required', Rule::enum(RedirectType::class)],
            'is_active' => ['boolean'],
        ];
    }

    public function messages(): array
    {
        return [
            'from_url.regex' => 'The source path must start with a slash (e.g. /old-page).',
        ];
    }

    /**
     * Guard against self-referencing loops and redirecting the admin panel.
     */
    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator) {
            $from = $this->input('from_url');

            if ($from !== '' && $from === $this->input('to_url')) {
                $validator->errors()->add('to_url', 'The destination must differ from the source.');
            }

            if (str_starts_with((string) $from, '/admin')) {
                $validator->errors()->add('from_url', 'You cannot redirect the admin panel.');
            }
        });
    }
}
