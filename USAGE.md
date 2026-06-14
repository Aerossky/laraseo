# Usage Guide

How to run, manage, and extend laraseo. This guide assumes you have completed the
[Quick start](README.md#quick-start) and can reach the admin panel at `/admin`.

For everything SEO-specific, see the [SEO Guide](SEO.md).

---

## 1. The admin panel

After seeding, log in at `/admin` with the default account
(`admin@example.com` / `password` — change this in `database/seeders/DatabaseSeeder.php`).

The admin navigation is grouped to stay uncluttered:

```
Dashboard   Blog ▾   Redirects   Settings
            ├─ Posts
            ├─ Categories
            └─ Media
```

- **Dashboard** — overview and quick links
- **Blog → Posts / Categories / Media** — your content
- **Redirects** — 301/302 rules managed without code
- **Settings** — global SEO settings (see [SEO Guide](SEO.md#global-settings))

---

## 2. Writing and publishing a post

1. **Blog → Posts → New post**.
2. Enter the **title**. This is locked as the page's single `<h1>` — the editor
   intentionally has no H1 block, so you can never accidentally create a second one.
3. Write the body with the **EditorJS** block editor (type `/` for the block picker).
   Available blocks: paragraph, heading (H2/H3), image, quote, list, code, table.
4. Pick a **category** and optionally write an **excerpt** (used as the meta
   description fallback and on cards). If left blank, it is auto-derived from the
   first paragraph.
5. **Featured image** — upload one. Alt text is required.
6. Fill the **SEO fields** if you want to override the defaults — otherwise sensible
   values are derived automatically (see [SEO Guide](SEO.md)).
7. Set the **status**:
   - `Draft` — not public.
   - `Published` — live immediately (with a past `published_at`).
   - `Scheduled` — goes live automatically once `published_at` is reached.
8. **Table of contents** — toggle `show_toc` to render a TOC from your H2/H3 headings.

> **Alt text is enforced.** A post with any image missing alt text cannot be
> published. This is deliberate — accessibility and image SEO are not optional.

---

## 3. Categories

Manage under **Blog → Categories**. Each category has its own public archive at
`/blog/category/{slug}` with its own editable SEO meta. Slugs are generated from the
name and stay stable even if you rename the category later.

---

## 4. Media library

Under **Blog → Media** (`spatie/laravel-medialibrary`). Upload an image once, store
its alt text, and reuse it across posts. Featured images are downloaded and
self-hosted — never hotlinked.

> When seeding or importing images from a URL, use a **direct image URL** that the
> server can download (e.g. Unsplash/Pexels/Pixabay), not a Google Images search link.

---

## 5. Redirects

Add 301 (permanent) or 302 (temporary) redirects under **Redirects** — no code or
deploy needed. The `HandleRedirects` middleware applies active rules on every request.
Toggle a rule on/off without deleting it.

---

## 6. Add your own admin page

The admin nav is a single source of truth in
[`resources/views/layouts/navigation.blade.php`](resources/views/layouts/navigation.blade.php).

**Step 1 — register a route** under the `admin.` prefix in `routes/web.php`:

```php
Route::middleware('auth')->prefix('admin')->name('admin.')->group(function () {
    // ...
    Route::get('pages', [PageController::class, 'index'])->name('pages.index');
});
```

**Step 2 — add it to the nav** by editing the `$nav` array. A single link:

```php
['label' => 'Pages', 'route' => 'admin.pages.index'],
```

Or a dropdown group (same shape as "Blog"):

```php
['label' => 'Shop', 'children' => [
    ['label' => 'Products', 'route' => 'admin.products.index'],
    ['label' => 'Orders',   'route' => 'admin.orders.index'],
]],
```

**Step 3 — wrap your view** in the admin layout so it inherits the nav and chrome:

```blade
<x-admin-layout>
    <x-slot name="header">
        <h2 class="text-xl font-semibold text-gray-800">Pages</h2>
    </x-slot>

    {{-- your content --}}
</x-admin-layout>
```

Index routes use the `.index` suffix so the active-state highlighter also matches
nested pages (`create`, `edit`, …).

---

## 7. Add your own public page

Wrap the view in `<x-public-layout>` and set its SEO meta from the controller:

```php
public function about(\App\Seo\SeoService $seo): \Illuminate\Contracts\View\View
{
    $seo->title('About Us')
        ->description('The story behind our project.');

    return view('pages.about'); // view uses <x-public-layout>
}
```

`<x-public-layout>` already includes `<x-seo-head />`, so the title, canonical URL,
Open Graph, Twitter Card, and global head/body scripts are emitted automatically. If
you set nothing, the global fallbacks apply — the page is still valid.

---

## 8. Add SEO to a new model

Any Eloquent model can opt into the SEO system:

```php
use App\Seo\HasSeoMeta;

class Product extends Model
{
    use HasSeoMeta;

    public function getSeoTitle(): ?string       { return $this->name; }
    public function getSeoDescription(): ?string { return $this->summary; }
    public function getSeoImageUrl(): ?string    { return $this->image_url; }
}
```

Then in the controller:

```php
public function show(Product $product, \App\Seo\SeoService $seo)
{
    $seo->for($product);

    return view('products.show', compact('product'));
}
```

The model now has an editable `seoMeta` relation and resolves meta with the same
precedence as posts. See the [SEO Guide](SEO.md#how-meta-is-resolved) for details.
