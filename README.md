# laraseo

> A Laravel 13 starter kit with SEO built-in from day one.

Most Laravel developers skip SEO because setting it up from scratch is tedious. WordPress developers rely on Rank Math. **laraseo** fills that gap — clone it, customize it, ship it.

---

## What's included

- **Blog system** — posts, categories, draft/publish/schedule
- **SEO per post** — meta title, meta description, Open Graph, canonical, robots, schema markup (JSON-LD)
- **Sitemap** — auto-generated, auto-updated on publish/unpublish
- **Redirect manager** — 301 and 302 redirects managed from admin, no code change needed
- **Media library** — upload once, reuse anywhere, alt text stored per file
- **Block editor** — EditorJS with paragraph, heading, image, gallery, quote, list, code, TOC blocks
- **Table of Contents** — place freely inside content, auto-generated from headings
- **Admin panel** — simple Blade + Tailwind, no Filament, no heavy dependencies
- **Global SEO settings** — site name, default meta format, Google verification tag, head/body scripts, robots.txt editor

---

## Stack

| Layer | Technology |
|---|---|
| Backend | Laravel 13, PHP 8.3+ |
| Frontend | Blade, Tailwind CSS v4, Alpine.js |
| Auth | Laravel Breeze (Blade stack) |
| Editor | EditorJS |
| Database | MySQL 8+ (SQLite for local dev) |

---

## Requirements

- PHP 8.3+
- Composer
- Node.js 18+
- MySQL 8+ or SQLite

---

## Quick start

```bash
# 1. Clone the repo
git clone https://github.com/yourusername/laravel-seo-starter.git my-project
cd my-project

# 2. Install PHP dependencies
composer install

# 3. Install JS dependencies
npm install

# 4. Setup environment
cp .env.example .env
php artisan key:generate

# 5. Configure your database in .env, then run migrations
php artisan migrate

# 6. Seed the database (creates default admin account)
php artisan db:seed

# 7. Build assets
npm run build

# 8. Start local server
php artisan serve
```

Admin panel available at `/admin`. Default credentials are in `DatabaseSeeder.php` — change them before deploying to production.

---

## Features in detail

### SEO — enforced at system level

SEO is not an afterthought here. It is built into the system:

- H1 is always locked to the post title — one H1 per page, always
- Meta title capped at 60 characters with a live counter in admin
- Meta description capped at 160 characters with a live counter in admin
- Canonical URL auto-set to current URL unless manually overridden
- Schema markup (`Article`, `BreadcrumbList`) auto-generated per content type
- All images require alt text before a post can be published
- Sitemap updates automatically — no manual trigger needed
- `<x-seo-head />` component handles all head tags in one line

To use SEO in a new model:

```php
use App\Seo\HasSeoMeta;

class Product extends Model
{
    use HasSeoMeta;
}
```

```php
// In your controller
public function show(Product $product)
{
    seo()->for($product);
    return view('products.show', compact('product'));
}
```

### Block editor (EditorJS)

Content is built with blocks — no raw HTML needed:

- Type `/` to open block picker
- Drag blocks to reorder
- Insert images directly from media library
- Available blocks: paragraph, heading (H2/H3), image, gallery, quote, list, code, table of contents

### Table of Contents

Add a TOC block anywhere inside the post content. It auto-generates from all H2 and H3 headings in the post with smooth scroll anchors.

### Redirect manager

Add and manage redirects from `/admin/redirects` — no code changes, no deployment needed. Middleware handles it automatically on every request.

### Media library

Powered by `spatie/laravel-medialibrary`. Upload images once, insert them anywhere across posts. Alt text is stored per file and auto-filled when inserting into content.

---

## Google Search Console setup

1. Go to **Admin → SEO Settings**
2. Paste your Google Site Verification meta tag
3. Submit `https://yourdomain.com/sitemap.xml` to GSC

That's it — sitemap stays up to date automatically.

For Google Analytics, Clarity, or any other tracking script: paste the code in **Admin → SEO Settings → Head Scripts**.

---

## Customization

This is a starting point, not a finished product. Everything is meant to be customized:

- Edit Blade templates in `resources/views/`
- Override styles in `resources/css/`
- Add new models with `HasSeoMeta` trait
- Extend EditorJS with additional blocks via npm

The frontend is intentionally minimal — add your own design on top.

---

## What this is not

- Not a SaaS product
- Not a page builder
- Not a multi-tenant CMS
- Not a drop-in replacement for WordPress

If you need a full CMS out of the box, consider October CMS or Statamic. laraseo is for Laravel developers who want to own their stack.

---

## Roadmap

**v1 (current)**
- Blog, categories, SEO fields, sitemap, redirects, media library, EditorJS, admin panel

**v2 (planned)**
- Static pages (About, Contact)
- User roles and permissions
- Tag pages
- Comments system
- Newsletter integration

---

## Contributing

See [CONTRIBUTING.md](CONTRIBUTING.md) for coding conventions and contribution guidelines.

---

## License

MIT — free to use for personal and commercial projects.

---

Built by [Aero](https://github.com/Aerossky)
