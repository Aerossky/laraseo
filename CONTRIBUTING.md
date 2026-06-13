# Contributing to laraseo

Thank you for your interest in contributing. This document covers coding conventions, naming standards, and the contribution process.

---

## Getting started

```bash
git clone https://github.com/yourusername/laravel-seo-starter.git
cd laravel-seo-starter
composer install
npm install
cp .env.example .env
php artisan key:generate
php artisan migrate
php artisan db:seed
npm run dev
```

---

## Coding standards

### PHP

- PHP 8.3+ syntax — typed properties, enums, match expressions
- PSR-12 coding standard — enforced via Laravel Pint
- Run Pint before submitting a PR: `./vendor/bin/pint`

### Laravel patterns

- Business logic in Service classes — not in Controllers
- Reusable model behavior in Traits — not in base Model classes
- Controllers must be thin: validate, call service, return response
- No raw SQL — Eloquent ORM only
- No N+1 queries — eager load relationships on list views

### Frontend

- Blade + Tailwind CSS v4 only
- No Vue, no React, no Livewire
- Alpine.js only for small UI interactions where strictly needed
- No inline styles — Tailwind utility classes only
- All images must include `loading="lazy"`

---

## Naming conventions

### PHP classes

| Type       | Convention                   | Example                                     |
| ---------- | ---------------------------- | ------------------------------------------- |
| Model      | Singular PascalCase          | `Post`, `Category`, `Redirect`              |
| Controller | Singular + Controller suffix | `PostController`, `MediaController`         |
| Trait      | Descriptive adjective + noun | `HasSeoMeta`, `HasSlug`                     |
| Service    | Noun + Service suffix        | `SeoService`, `SitemapService`              |
| Middleware | Action + verb                | `HandleRedirects`                           |
| Request    | Action + model + Request     | `StorePostRequest`, `UpdateCategoryRequest` |
| Enum       | PascalCase                   | `PostStatus`, `RedirectType`                |

### Database

| Type        | Convention               | Example                                   |
| ----------- | ------------------------ | ----------------------------------------- |
| Table       | Plural snake_case        | `posts`, `seo_metas`, `redirects`         |
| Column      | Snake_case               | `meta_title`, `published_at`, `is_active` |
| Foreign key | `{model}_id`             | `category_id`, `featured_image_id`        |
| Boolean     | `is_` or `has_` prefix   | `is_active`, `show_toc`                   |
| Pivot table | Alphabetical, snake_case | `category_post`                           |

### Files and views

| Type            | Convention             | Example                                |
| --------------- | ---------------------- | -------------------------------------- |
| Migration       | `create_{table}_table` | `create_posts_table`                   |
| Blade view      | Snake_case             | `admin/posts/index.blade.php`          |
| Blade component | Kebab-case             | `<x-seo-head />`, `<x-post-content />` |
| JS file         | Kebab-case             | `editor.js`, `editor-renderer.js`      |

### Routes

| Type         | Convention          | Example                          |
| ------------ | ------------------- | -------------------------------- |
| Named route  | Dot notation        | `admin.posts.index`, `blog.show` |
| Admin prefix | `/admin/{resource}` | `/admin/posts`, `/admin/media`   |
| Public blog  | `/blog/{slug}`      | `/blog/my-post-title`            |

### Methods

| Pattern       | Convention           | Example                                |
| ------------- | -------------------- | -------------------------------------- |
| Boolean check | `is` or `has` prefix | `isPublished()`, `hasSeoMeta()`        |
| Getter        | `get` prefix         | `getSeoTitle()`, `getExcerpt()`        |
| Action        | Verb                 | `publish()`, `generate()`, `resolve()` |

---

## Git conventions

### Branch naming

```
feat/post-editor
fix/sitemap-not-updating
chore/update-dependencies
refactor/seo-service
```

### Commit messages

Follow [Conventional Commits](https://www.conventionalcommits.org/):

```
<type>(<scope>): <short description>
```

**Types:**
| Type | When to use |
|---|---|
| `feat` | New feature |
| `fix` | Bug fix |
| `chore` | Setup, config, dependencies |
| `refactor` | Restructure without adding features or fixing bugs |
| `docs` | Documentation only |
| `test` | Tests only |
| `perf` | Performance improvement |

**Examples:**

```
feat(posts): add draft/publish/schedule status
fix(sitemap): sitemap not updating on post unpublish
chore: install spatie/laravel-medialibrary
refactor(seo): extract schema generation to SchemaBuilder
docs: update README quick start steps
```

---

## Pull request process

1. Fork the repo and create a branch from `main`
2. Follow all naming and coding conventions above
3. Run `./vendor/bin/pint` before pushing
4. Write or update tests for your changes if applicable
5. Make sure `php artisan migrate:fresh --seed` runs without errors
6. Submit a PR with a clear description of what changed and why

---

## SEO rules (do not bypass these)

These constraints exist for a reason — bypassing them defeats the purpose of this starter kit:

- H1 is always and only the post/page title — do not add H1 to the editor toolbar
- Alt text is required on all images before a post can be published
- Canonical URL must always be present — auto-set if not manually defined
- `<x-seo-head />` must be in every layout — never remove it

---

## Questions

Open a GitHub Discussion or issue. PRs that bypass SEO constraints or deviate from the conventions above will not be merged.
