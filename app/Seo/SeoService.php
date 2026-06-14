<?php

namespace App\Seo;

use App\Models\Post;
use App\Models\Setting;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\HtmlString;

/**
 * Resolves and renders every SEO <head> tag for the current page (FR-22).
 *
 * Value precedence: explicit override > model SeoMeta record > model default
 * (getSeoTitle/Description/ImageUrl) > global fallback (settings / app config).
 */
class SeoService
{
    protected ?Model $subject = null;

    /** @var array<string, string|null> */
    protected array $overrides = [];

    public function __construct(protected SchemaBuilder $schema) {}

    public function for(Model $subject): static
    {
        $this->subject = $subject;

        return $this;
    }

    public function title(string $title): static
    {
        return $this->set('meta_title', $title);
    }

    public function description(string $description): static
    {
        return $this->set('meta_description', $description);
    }

    public function canonical(string $url): static
    {
        return $this->set('canonical_url', $url);
    }

    public function robots(string $robots): static
    {
        return $this->set('robots', $robots);
    }

    public function image(string $url): static
    {
        return $this->set('og_image', $url);
    }

    public function set(string $key, ?string $value): static
    {
        $this->overrides[$key] = $value;

        return $this;
    }

    /**
     * Resolve the final SEO values for the current subject.
     *
     * @return array<string, string|null>
     */
    public function resolve(): array
    {
        $meta = ($this->subject && method_exists($this->subject, 'seoMeta'))
            ? $this->subject->seoMeta
            : null;

        $siteName = Setting::get('site_name', config('app.name'));

        $title = $this->value('meta_title', $meta?->meta_title) ?? $this->fromSubject('getSeoTitle');
        $description = $this->value('meta_description', $meta?->meta_description)
            ?? $this->fromSubject('getSeoDescription')
            ?? (Setting::get('meta_description_fallback') ?: null);
        $canonical = $this->value('canonical_url', $meta?->canonical_url) ?? url()->current();
        $robots = $this->value('robots', $meta?->robots) ?? 'index, follow';

        $ogTitle = $this->value('og_title', $meta?->og_title) ?? $title;
        $ogDescription = $this->value('og_description', $meta?->og_description) ?? $description;
        $ogImage = $this->value('og_image', $meta?->og_image) ?? $this->fromSubject('getSeoImageUrl');

        return [
            'title' => $this->formatTitle($title, $siteName),
            'raw_title' => $title,
            'description' => $description,
            'canonical' => $canonical,
            'robots' => $robots,
            'og_title' => $ogTitle,
            'og_description' => $ogDescription,
            'og_image' => $ogImage,
            'og_type' => $this->subject instanceof Post ? 'article' : 'website',
            'site_name' => $siteName,
        ];
    }

    /**
     * Render all resolved SEO tags as an HTML string for <x-seo-head />.
     */
    public function render(): HtmlString
    {
        $data = $this->resolve();
        $tags = [];

        if ($data['title']) {
            $tags[] = '<title>'.e($data['title']).'</title>';
        }

        if ($data['description']) {
            $tags[] = $this->meta('name', 'description', $data['description']);
        }

        $tags[] = '<link rel="canonical" href="'.e($data['canonical']).'">';
        $tags[] = $this->meta('name', 'robots', $data['robots']);

        // Open Graph (FR-28, FR-29)
        $tags[] = $this->meta('property', 'og:type', $data['og_type']);
        $tags[] = $this->meta('property', 'og:url', $data['canonical']);
        $tags[] = $this->metaIf('property', 'og:title', $data['og_title']);
        $tags[] = $this->metaIf('property', 'og:description', $data['og_description']);
        $tags[] = $this->metaIf('property', 'og:site_name', $data['site_name']);
        $tags[] = $this->metaIf('property', 'og:image', $data['og_image']);

        // Twitter Card
        $tags[] = $this->meta('name', 'twitter:card', $data['og_image'] ? 'summary_large_image' : 'summary');
        $tags[] = $this->metaIf('name', 'twitter:title', $data['og_title']);
        $tags[] = $this->metaIf('name', 'twitter:description', $data['og_description']);
        $tags[] = $this->metaIf('name', 'twitter:image', $data['og_image']);

        // Google Site Verification (FR-71)
        if ($verification = Setting::get('google_site_verification')) {
            $tags[] = $this->meta('name', 'google-site-verification', $verification);
        }

        // Structured data (FR-23)
        if ($this->subject instanceof Post) {
            $tags[] = $this->schema->toScript($this->schema->article($this->subject))->toHtml();
        }

        return new HtmlString(implode("\n    ", array_filter($tags)));
    }

    protected function value(string $key, ?string $metaValue): ?string
    {
        if (array_key_exists($key, $this->overrides)) {
            return $this->overrides[$key];
        }

        return ($metaValue !== null && $metaValue !== '') ? $metaValue : null;
    }

    protected function fromSubject(string $method): ?string
    {
        return ($this->subject && method_exists($this->subject, $method))
            ? $this->subject->{$method}()
            : null;
    }

    protected function formatTitle(?string $title, ?string $siteName): ?string
    {
        if ($title === null) {
            return $siteName;
        }

        if ($format = Setting::get('meta_title_format')) {
            // Support both {title}/{site} and :title/:site placeholder styles.
            return strtr($format, [
                '{title}' => $title,
                '{site}' => (string) $siteName,
                ':title' => $title,
                ':site' => (string) $siteName,
            ]);
        }

        return $siteName ? "{$title} — {$siteName}" : $title;
    }

    protected function meta(string $attribute, string $name, string $content): string
    {
        return '<meta '.$attribute.'="'.e($name).'" content="'.e($content).'">';
    }

    protected function metaIf(string $attribute, string $name, ?string $content): ?string
    {
        return $content ? $this->meta($attribute, $name, $content) : null;
    }
}
