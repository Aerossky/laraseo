<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateSettingsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // Route is protected by the auth middleware.
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'site_name' => ['required', 'string', 'max:255'],
            'meta_title_format' => ['nullable', 'string', 'max:255'],
            'meta_description_fallback' => ['nullable', 'string', 'max:500'],
            'google_site_verification' => ['nullable', 'string', 'max:255'],
            'head_scripts' => ['nullable', 'string', 'max:65535'],
            'body_scripts' => ['nullable', 'string', 'max:65535'],
            'robots_txt' => ['nullable', 'string', 'max:65535'],
        ];
    }
}
