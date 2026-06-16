# Changelog

All notable changes to this project are documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.1.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [1.1.1] - 2026-06-16

### Changed

- Reorganized Blade layouts for clarity: the public layout moved to
  `layouts/app.blade.php` and is now used as `<x-app-layout>` (the standard
  Laravel convention), and the admin navigation moved to
  `admin/partials/navigation.blade.php` so `layouts/` holds only layouts.

### Fixed

- The `AppLayout` view component pointed at a missing `layouts/app.blade.php`;
  it now resolves correctly.

### Note

- For anyone tracking upstream: the public layout tag changed from
  `<x-public-layout>` to `<x-app-layout>`.

## [1.1.0] - 2026-06-16

### Added

- **Role-based access control** — `admin`, `editor`, and `author` roles, with
  authors limited to their own posts and content managers seeing all.
- **Moderated comments** — visitors can comment on posts; comments are held for
  moderation before they appear publicly.
- **Featured image library picker** — pick an existing image from the media
  library (modal) instead of only uploading a new file.
- **"Library image" editor block** — insert an image that already lives in the
  media library directly into post content via EditorJS.

### Changed

- Featured image uploads are now funneled through the media library, so it stays
  the single catalog of every image in the project.
- Editing an image's alt text in the media library now propagates to the
  featured images and post content blocks that use it, while preserving any
  alt text that was overridden per post.

## [1.0.0] - 2026-06-15

### Added

- Initial release — a Laravel 13, SEO-first blog starter kit.
- Post and category management with EditorJS content and server-side rendering.
- Media library with enforced alt text.
- SEO layer: meta/Open Graph/Twitter tags, canonical URLs, JSON-LD schema, and a
  generated XML sitemap.
- Redirect management and configurable site settings, with seeded demo content.

[1.1.1]: https://github.com/Aerossky/laraseo/compare/v1.1.0...v1.1.1
[1.1.0]: https://github.com/Aerossky/laraseo/compare/v1.0.0...v1.1.0
[1.0.0]: https://github.com/Aerossky/laraseo/releases/tag/v1.0.0
