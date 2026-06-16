<?php

namespace App\Http\Requests;

use App\Enums\PostStatus;
use App\Models\MediaLibrary;
use App\Services\PostService;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StorePostRequest extends FormRequest
{
    /** Robots directives offered in the SEO panel. */
    public const ROBOTS = [
        'index, follow',
        'index, nofollow',
        'noindex, follow',
        'noindex, nofollow',
    ];

    public function authorize(): bool
    {
        return true; // Route is protected by the auth middleware.
    }

    protected function prepareForValidation(): void
    {
        $content = $this->input('content');

        $this->merge([
            'content' => is_string($content) ? (json_decode($content, true) ?: []) : ($content ?? []),
            'show_toc' => $this->boolean('show_toc'),
        ]);

        // A post published now needs a timestamp so it appears immediately.
        if ($this->input('status') === PostStatus::Published->value && ! $this->filled('published_at')) {
            $this->merge(['published_at' => now()->toDateTimeString()]);
        }
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:255'],
            'slug' => ['nullable', 'string', 'max:255', 'alpha_dash', Rule::unique('posts', 'slug')->ignore($this->route('post'))],
            'category_id' => ['nullable', 'exists:categories,id'],
            'excerpt' => ['nullable', 'string', 'max:500'],
            'content' => ['nullable', 'array'],
            'status' => ['required', Rule::enum(PostStatus::class)],
            'published_at' => ['nullable', 'date', Rule::requiredIf($this->input('status') === PostStatus::Scheduled->value)],
            'show_toc' => ['boolean'],
            'featured_image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp,gif', 'max:5120'],
            'featured_media_id' => ['nullable', 'integer', Rule::exists('media', 'id')->where('collection_name', MediaLibrary::COLLECTION)],
            'featured_alt' => ['nullable', 'string', 'max:255'],
            'seo' => ['array'],
            'seo.meta_title' => ['nullable', 'string', 'max:255'],
            'seo.meta_description' => ['nullable', 'string', 'max:255'],
            'seo.canonical_url' => ['nullable', 'url', 'max:2048'],
            'seo.robots' => ['nullable', Rule::in(self::ROBOTS)],
            'seo.og_title' => ['nullable', 'string', 'max:255'],
            'seo.og_description' => ['nullable', 'string', 'max:255'],
            'seo.og_image' => ['nullable', 'url', 'max:2048'],
        ];
    }

    /**
     * Enforce SEO publish gates: a category and alt text on every image
     * are required before a post can go live (FR-11, FR-15).
     */
    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator) {
            $goingLive = in_array($this->input('status'), [
                PostStatus::Published->value,
                PostStatus::Scheduled->value,
            ], true);

            if (! $goingLive) {
                return;
            }

            if (! $this->input('category_id')) {
                $validator->errors()->add('category_id', 'A category is required before a post can be published.');
            }

            if (($missing = PostService::imageBlocksMissingAlt($this->input('content'))) > 0) {
                $validator->errors()->add('content', "Every image needs alt text before publishing ({$missing} missing).");
            }
        });
    }
}
