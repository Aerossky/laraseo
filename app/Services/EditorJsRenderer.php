<?php

namespace App\Services;

use Illuminate\Support\HtmlString;
use Illuminate\Support\Str;

/**
 * Renders EditorJS block JSON to HTML on the server (FR-67).
 *
 * Server-side rendering is required: the whole point of this starter is SEO,
 * and client-side rendering would hide content from crawlers and AI engines.
 * Admin-authored content is trusted; inline HTML is limited to a safe allowlist.
 */
class EditorJsRenderer
{
    /** Inline tags allowed inside text blocks (EditorJS inline tools). */
    protected const ALLOWED_INLINE = '<b><strong><i><em><u><s><a><mark><code><br>';

    /** Block types that carry a single image and share the image renderer/alt rules. */
    public const IMAGE_BLOCKS = ['image', 'libraryImage'];

    /**
     * @param  array<string, mixed>|string|null  $content
     */
    public function render(array|string|null $content): HtmlString
    {
        $html = collect($this->blocks($content))
            ->map(fn (array $block) => $this->renderBlock($block))
            ->filter()
            ->implode("\n");

        return new HtmlString($html);
    }

    /**
     * @param  array<string, mixed>|string|null  $content
     * @return array<int, array<string, mixed>>
     */
    protected function blocks(array|string|null $content): array
    {
        if (is_string($content)) {
            $content = json_decode($content, true) ?: [];
        }

        return $content['blocks'] ?? [];
    }

    /**
     * @param  array<string, mixed>  $block
     */
    protected function renderBlock(array $block): string
    {
        $type = $block['type'] ?? '';
        $data = $block['data'] ?? [];

        if (in_array($type, self::IMAGE_BLOCKS, true)) {
            return $this->image($data);
        }

        return match ($type) {
            'paragraph' => $this->paragraph($data),
            'header' => $this->header($data),
            'gallery' => $this->gallery($data),
            'quote' => $this->quote($data),
            'list' => $this->list($data),
            'code' => $this->code($data),
            'table' => $this->table($data),
            'delimiter' => '<hr>',
            default => '',
        };
    }

    /** @param array<string, mixed> $data */
    protected function paragraph(array $data): string
    {
        $text = $this->inline($data['text'] ?? '');

        return $text === '' ? '' : "<p>{$text}</p>";
    }

    /** @param array<string, mixed> $data */
    protected function header(array $data): string
    {
        // SEO: never emit H1 — clamp to H2/H3 (FR-48).
        $level = max(2, min(3, (int) ($data['level'] ?? 2)));
        $text = $this->inline($data['text'] ?? '');
        $slug = Str::slug(strip_tags($data['text'] ?? ''));
        $id = $slug !== '' ? ' id="'.e($slug).'"' : '';

        return "<h{$level}{$id}>{$text}</h{$level}>";
    }

    /** @param array<string, mixed> $data */
    protected function image(array $data): string
    {
        $url = $data['file']['url'] ?? null;

        if (! $url) {
            return '';
        }

        // The caption doubles as the required alt text (FR-50); fall back to the
        // media library alt. All images are lazy-loaded (FR-65).
        $caption = trim(strip_tags($data['caption'] ?? ''));
        $alt = $caption !== '' ? $caption : (string) ($data['file']['alt'] ?? '');

        $img = '<img src="'.e($url).'" alt="'.e($alt).'" loading="lazy">';
        $figcaption = $caption !== '' ? '<figcaption>'.e($caption).'</figcaption>' : '';

        return "<figure>{$img}{$figcaption}</figure>";
    }

    /** @param array<string, mixed> $data */
    protected function gallery(array $data): string
    {
        $images = collect($data['files'] ?? [])
            ->map(function (array $file): string {
                $url = $file['url'] ?? null;

                return $url
                    ? '<img src="'.e($url).'" alt="'.e((string) ($file['alt'] ?? '')).'" loading="lazy">'
                    : '';
            })
            ->filter()
            ->implode("\n");

        if ($images === '') {
            return '';
        }

        $caption = trim(strip_tags($data['caption'] ?? ''));
        $figcaption = $caption !== '' ? '<figcaption>'.e($caption).'</figcaption>' : '';

        return '<figure class="editorjs-gallery">'
            .'<div class="grid grid-cols-2 gap-4 md:grid-cols-3">'.$images.'</div>'
            .$figcaption.'</figure>';
    }

    /** @param array<string, mixed> $data */
    protected function quote(array $data): string
    {
        $text = $this->inline($data['text'] ?? '');

        if ($text === '') {
            return '';
        }

        $caption = trim($data['caption'] ?? '');
        $cite = $caption !== '' ? '<figcaption>'.$this->inline($caption).'</figcaption>' : '';

        return "<figure><blockquote>{$text}</blockquote>{$cite}</figure>";
    }

    /** @param array<string, mixed> $data */
    protected function list(array $data): string
    {
        return $this->listItems($data['items'] ?? [], $data['style'] ?? 'unordered');
    }

    /**
     * Render list items, supporting both v1 (string items) and v2 (nested
     * objects with content/items/meta) shapes from @editorjs/list.
     *
     * @param  array<int, mixed>  $items
     */
    protected function listItems(array $items, string $style): string
    {
        if (! $items) {
            return '';
        }

        $tag = $style === 'ordered' ? 'ol' : 'ul';

        $lis = collect($items)->map(function ($item) use ($style): string {
            if (is_array($item)) {
                $content = $this->inline($item['content'] ?? '');
                $nested = $this->listItems($item['items'] ?? [], $style);
                $checkbox = $style === 'checklist'
                    ? '<input type="checkbox" disabled'.(($item['meta']['checked'] ?? false) ? ' checked' : '').'> '
                    : '';

                return "<li>{$checkbox}{$content}{$nested}</li>";
            }

            return '<li>'.$this->inline((string) $item).'</li>';
        })->implode("\n");

        return "<{$tag}>{$lis}</{$tag}>";
    }

    /** @param array<string, mixed> $data */
    protected function code(array $data): string
    {
        $code = e($data['code'] ?? '');

        return $code === '' ? '' : "<pre><code>{$code}</code></pre>";
    }

    /** @param array<string, mixed> $data */
    protected function table(array $data): string
    {
        $rows = $data['content'] ?? [];

        if (! $rows) {
            return '';
        }

        $withHeadings = $data['withHeadings'] ?? false;

        $out = '<table>';

        foreach ($rows as $i => $row) {
            $cellTag = ($withHeadings && $i === 0) ? 'th' : 'td';
            $cells = collect($row)
                ->map(fn ($cell) => "<{$cellTag}>".$this->inline((string) $cell)."</{$cellTag}>")
                ->implode('');
            $out .= "<tr>{$cells}</tr>";
        }

        return $out.'</table>';
    }

    protected function inline(string $html): string
    {
        return trim(strip_tags($html, self::ALLOWED_INLINE));
    }
}
