# SEO Guide

laraseo is built so that good SEO is the *default*, not a checklist you have to
remember. This guide explains what the system does for you, what you configure once,
and what to pay attention to per page and before launch.

---

## What is automatic

You do not write any code for these — they ship on every public page rendered through
`<x-public-layout>` / `<x-seo-head />`:

| Element | Behaviour |
|---|---|
| `<title>` | Built from the page title + your title format (e.g. `Post Title · Site`). |
| `<meta name="description">` | From the resolved description (override → SeoMeta → excerpt → global fallback). |
| `<link rel="canonical">` | Auto-set to the current URL unless you override it. **Always present.** |
| `<meta name="robots">` | Defaults to `index, follow`. |
| Open Graph | `og:title`, `og:description`, `og:url`, `og:image`, `og:site_name`, `og:type` (`article` for posts, else `website`). |
| Twitter Card | `summary_large_image` when an image exists, otherwise `summary`. |
| JSON-LD | `Article` + `BreadcrumbList` structured data generated per post. |
| Sitemap | `/sitemap.xml`, regenerated automatically on publish/unpublish. |
| `robots.txt` | `/robots.txt`, served from settings with the sitemap URL appended automatically. |
| Single H1 | The post title is the only H1; the editor has no H1 block. |
| Image alt text | Required before a post can be published. |

---

## Global settings

Set once under **Admin → Settings**. These are the site-wide fallbacks.

| Key | Purpose |
|---|---|
| `site_name` | Brand name used in titles and `og:site_name`. |
| `meta_title_format` | Title template. Supports `{title}`/`{site}` or `:title`/`:site`. Default: `{title} · {site}`. |
| `meta_description_fallback` | Description used when a page has none of its own. |
| `robots_txt` | Raw `robots.txt` body. The `Sitemap:` line is appended for you. |
| `google_site_verification` | Google Search Console verification token (rendered as a meta tag). |
| `head_scripts` | Raw markup injected at the end of `<head>` (analytics, etc.). |
| `body_scripts` | Raw markup injected at the start of `<body>`. |

---

## Per-page SEO

### From the admin (posts & categories)

Posts and categories have editable SEO fields: meta title, meta description, OG title,
OG description, canonical URL, robots, and OG image. Leave any blank to fall back to
the model default. The admin shows live character counters and warns past **60 chars**
(title) and **160 chars** (description).

### From code (any page)

Inject `SeoService` and use the fluent API before returning the view:

| Method | Sets |
|---|---|
| `->title('…')` | `<title>`, `og:title`, `twitter:title` |
| `->description('…')` | description + OG/Twitter description |
| `->canonical('…')` | `<link rel="canonical">` and `og:url` |
| `->robots('…')` | robots meta (e.g. `noindex, nofollow`) |
| `->image('…')` | `og:image` + `twitter:image` |
| `->set('og_title', '…')` | any individual key |
| `->for($model)` | pull everything from a model's `seoMeta` + defaults |

```php
$seo->title('Blog')
    ->description('Latest articles on SEO and Laravel.')
    ->image(asset('images/og-blog.png'));
```

---

## How meta is resolved

Highest priority wins:

```
1. Explicit override   → $seo->title(), ->set(), …      (controller)
2. Model SeoMeta       → the fields edited in admin
3. Model default       → getSeoTitle() / getSeoDescription() / getSeoImageUrl()
4. Global fallback     → Settings / config
```

This is why an empty SEO field in the admin is safe — it simply defers to the next
level down, never to nothing.

---

## Per-post checklist

Before publishing a post, aim for:

- [ ] **Title** ≤ 60 characters, includes the primary keyword near the front.
- [ ] **Meta description** ≤ 160 characters, compelling, includes the keyword.
- [ ] **Slug** is short and readable (auto-generated from the title; editable).
- [ ] **Category** assigned.
- [ ] **Featured image** set, with descriptive **alt text** (enforced).
- [ ] **Headings** use H2/H3 in a logical order — never skip levels for styling.
- [ ] In-content images have meaningful captions (the caption doubles as alt text).
- [ ] At least one **internal link** to a related post or category.

---

## Go-live checklist

Do these once when deploying to production:

- [ ] Set **`APP_URL`** to your real domain. Canonical URLs, `og:url`, sitemap, and
      media URLs are all absolute and derived from it — a wrong value breaks them all.
- [ ] Change the default admin credentials in `database/seeders/DatabaseSeeder.php`
      (or create a real account and remove the default).
- [ ] Run `php artisan storage:link` so uploaded media is publicly served.
- [ ] Set `site_name`, `meta_title_format`, and `meta_description_fallback` in Settings.
- [ ] Add your **Google site verification** token, then submit
      `https://yourdomain.com/sitemap.xml` in Google Search Console.
- [ ] Confirm `/robots.txt` disallows `/admin` and advertises your sitemap.
- [ ] Paste analytics into **Settings → Head Scripts** if needed.
- [ ] Spot-check a published post's `<head>` via *View Source* — title, canonical,
      OG tags, and the JSON-LD `<script type="application/ld+json">` should be present.

---

## Things to watch

- **Meta lives in `<head>`, not on the page.** If you "don't see" the meta title, look
  at the browser tab or *View Source* / DevTools — it is there.
- **Never add an H1 block** to the editor config. The locked H1 = post title is a core
  guarantee; a second H1 hurts SEO.
- **Don't remove the alt-text requirement.** It is enforced at publish time on purpose.
- **`<x-seo-head />` must stay in every layout's `<head>`.** It is the single point that
  emits all SEO tags; bypassing it drops canonical, OG, and structured data.
