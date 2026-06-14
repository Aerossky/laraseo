<?php

namespace Database\Seeders;

use App\Enums\PostStatus;
use App\Models\Category;
use App\Models\Post;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

/**
 * Seeds three published example blog posts across two categories, each with a
 * structured EditorJS body and SEO meta. Idempotent — safe to run repeatedly.
 */
class BlogSeeder extends Seeder
{
    use WithoutModelEvents;

    public function run(): void
    {
        $seo = Category::firstOrCreate(
            ['slug' => 'seo'],
            [
                'name' => 'SEO',
                'description' => 'Practical, on-page SEO techniques you can apply today.',
            ],
        );

        $laravel = Category::firstOrCreate(
            ['slug' => 'laravel'],
            [
                'name' => 'Laravel',
                'description' => 'Tips and patterns for building with the Laravel framework.',
            ],
        );

        foreach ($this->posts($seo->id, $laravel->id) as $data) {
            $seoMeta = $data['seo'];
            $image = $data['image'];
            unset($data['seo'], $data['image']);

            $post = Post::firstOrCreate(['slug' => $data['slug']], $data);

            $post->seoMeta()->updateOrCreate([], $seoMeta);

            // Download the featured image into the media library so it is
            // self-hosted rather than hotlinked. Skipped if it already exists
            // (idempotent) or if the download fails (e.g. seeding offline).
            if (! $post->hasMedia('featured')) {
                try {
                    $post->addMediaFromUrl($image['url'])
                        ->withCustomProperties(['alt' => $image['alt']])
                        ->toMediaCollection('featured');
                } catch (\Throwable $e) {
                    $this->command?->warn("Could not download featured image for \"{$post->slug}\": {$e->getMessage()}");
                }
            }
        }
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    protected function posts(int $seoCategoryId, int $laravelCategoryId): array
    {
        return [
            [
                'category_id' => $seoCategoryId,
                'title' => 'A Beginner\'s Guide to On-Page SEO',
                'slug' => 'beginners-guide-to-on-page-seo',
                'excerpt' => 'Learn the on-page SEO fundamentals — titles, meta descriptions, headings, and internal links — that help search engines understand your content.',
                'content' => [
                    'time' => now()->timestamp,
                    'version' => '2.31.0',
                    'blocks' => [
                        ['type' => 'paragraph', 'data' => ['text' => 'On-page SEO is everything you control directly on a page to help it rank: the title, the content structure, and the signals you send to search engines. Get the basics right and the rest of your SEO work compounds.']],
                        ['type' => 'header', 'data' => ['text' => 'Write a strong title tag', 'level' => 2]],
                        ['type' => 'paragraph', 'data' => ['text' => 'Your <b>title tag</b> is the single most important on-page element. Keep it under 60 characters, lead with your primary keyword, and make it compelling enough to earn the click.']],
                        ['type' => 'header', 'data' => ['text' => 'Nail the meta description', 'level' => 2]],
                        ['type' => 'paragraph', 'data' => ['text' => 'A meta description does not directly affect rankings, but it heavily influences click-through rate. Aim for 150-160 characters that summarise the page and include a clear reason to read on.']],
                        ['type' => 'header', 'data' => ['text' => 'Structure content with headings', 'level' => 2]],
                        ['type' => 'list', 'data' => ['style' => 'unordered', 'items' => [
                            'Use one H1 — it should be the page title.',
                            'Break sections with H2s and sub-sections with H3s.',
                            'Keep the hierarchy logical; never skip levels for styling.',
                        ]]],
                        ['type' => 'quote', 'data' => ['text' => 'Good structure helps both readers and crawlers find what matters.', 'caption' => 'On-page SEO in one sentence']],
                        ['type' => 'header', 'data' => ['text' => 'Link internally', 'level' => 2]],
                        ['type' => 'paragraph', 'data' => ['text' => 'Internal links spread authority across your site and help search engines discover new pages. Link to related posts using descriptive anchor text rather than "click here".']],
                    ],
                ],
                'status' => PostStatus::Published,
                'show_toc' => true,
                'published_at' => now()->subDays(5),
                'image' => [
                    'url' => 'https://images.unsplash.com/photo-1432888622747-4eb9a8efeb07?w=1200&q=80',
                    'alt' => 'A magnifying glass over an analytics report, illustrating search optimisation',
                ],
                'seo' => [
                    'meta_title' => 'On-Page SEO: A Beginner\'s Guide',
                    'meta_description' => 'Master the on-page SEO basics — title tags, meta descriptions, headings, and internal links — with this practical beginner\'s guide.',
                    'og_title' => 'A Beginner\'s Guide to On-Page SEO',
                    'og_description' => 'The on-page SEO fundamentals every site owner should get right.',
                    'robots' => 'index, follow',
                ],
            ],
            [
                'category_id' => $laravelCategoryId,
                'title' => 'Eager Loading: How to Avoid N+1 Queries in Laravel',
                'slug' => 'avoid-n-plus-1-queries-in-laravel',
                'excerpt' => 'N+1 queries quietly slow down Laravel apps. Learn how to spot them and fix them with eager loading.',
                'content' => [
                    'time' => now()->timestamp,
                    'version' => '2.31.0',
                    'blocks' => [
                        ['type' => 'paragraph', 'data' => ['text' => 'The N+1 query problem is one of the most common performance issues in Laravel. It happens when you load a list of records and then query a relationship for each one — turning one page load into hundreds of database calls.']],
                        ['type' => 'header', 'data' => ['text' => 'Spotting the problem', 'level' => 2]],
                        ['type' => 'paragraph', 'data' => ['text' => 'Loading posts and then accessing each post\'s author inside a loop triggers a separate query per post. With 50 posts, that is 51 queries instead of two.']],
                        ['type' => 'code', 'data' => ['code' => "// Triggers N+1\n\$posts = Post::all();\nforeach (\$posts as \$post) {\n    echo \$post->category->name;\n}"]],
                        ['type' => 'header', 'data' => ['text' => 'The fix: eager loading', 'level' => 2]],
                        ['type' => 'paragraph', 'data' => ['text' => 'Use <code>with()</code> to load the relationship up front in a single extra query:']],
                        ['type' => 'code', 'data' => ['code' => "// Two queries total\n\$posts = Post::with('category')->get();\nforeach (\$posts as \$post) {\n    echo \$post->category->name;\n}"]],
                        ['type' => 'header', 'data' => ['text' => 'Prevent it in development', 'level' => 2]],
                        ['type' => 'paragraph', 'data' => ['text' => 'Call <code>Model::preventLazyLoading()</code> in a non-production environment so Laravel throws an exception whenever you accidentally lazy load a relationship.']],
                    ],
                ],
                'status' => PostStatus::Published,
                'show_toc' => true,
                'published_at' => now()->subDays(3),
                'image' => [
                    'url' => 'https://images.unsplash.com/photo-1555066931-4365d14bab8c?w=1200&q=80',
                    'alt' => 'Source code displayed on a screen, representing Laravel database queries',
                ],
                'seo' => [
                    'meta_title' => 'Avoid N+1 Queries in Laravel with Eager Loading',
                    'meta_description' => 'Learn how to detect and fix the N+1 query problem in Laravel using eager loading and lazy-loading prevention.',
                    'og_title' => 'How to Avoid N+1 Queries in Laravel',
                    'og_description' => 'Spot and fix the N+1 query problem with eager loading.',
                    'robots' => 'index, follow',
                ],
            ],
            [
                'category_id' => $seoCategoryId,
                'title' => 'Why Structured Data Matters for Your Blog',
                'slug' => 'why-structured-data-matters-for-your-blog',
                'excerpt' => 'Structured data tells search engines exactly what your content is about — and can earn you rich results in the SERPs.',
                'content' => [
                    'time' => now()->timestamp,
                    'version' => '2.31.0',
                    'blocks' => [
                        ['type' => 'paragraph', 'data' => ['text' => 'Structured data is a standardised format for describing your content to search engines. Add it correctly and you become eligible for rich results — the stars, images, and breadcrumbs that make a listing stand out.']],
                        ['type' => 'header', 'data' => ['text' => 'What is JSON-LD?', 'level' => 2]],
                        ['type' => 'paragraph', 'data' => ['text' => 'JSON-LD is Google\'s recommended structured-data format. It lives in a script tag in your page head and describes your content using the Schema.org vocabulary — without touching your visible markup.']],
                        ['type' => 'header', 'data' => ['text' => 'Useful schema types for blogs', 'level' => 2]],
                        ['type' => 'list', 'data' => ['style' => 'unordered', 'items' => [
                            'Article — for blog posts and news content.',
                            'BreadcrumbList — to show your site hierarchy in results.',
                            'Organization — to define your brand identity.',
                        ]]],
                        ['type' => 'quote', 'data' => ['text' => 'Structured data does not improve rankings directly, but it makes your results far more clickable.', 'caption' => 'A note on rich results']],
                        ['type' => 'header', 'data' => ['text' => 'It should be automatic', 'level' => 2]],
                        ['type' => 'paragraph', 'data' => ['text' => 'The best structured data is the kind you never have to think about. laraseo builds Article and BreadcrumbList JSON-LD for every post automatically, so your markup stays valid as you write.']],
                    ],
                ],
                'status' => PostStatus::Published,
                'show_toc' => true,
                'published_at' => now()->subDay(),
                'image' => [
                    'url' => 'https://images.unsplash.com/photo-1496171367470-9ed9a91ea931?w=1200&q=80',
                    'alt' => 'A laptop showing lines of code, representing structured data markup',
                ],
                'seo' => [
                    'meta_title' => 'Why Structured Data Matters for Your Blog',
                    'meta_description' => 'Understand how structured data and JSON-LD help search engines read your blog and unlock rich results in the SERPs.',
                    'og_title' => 'Why Structured Data Matters for Your Blog',
                    'og_description' => 'How JSON-LD structured data earns rich results for your content.',
                    'robots' => 'index, follow',
                ],
            ],
        ];
    }
}
