<?php

namespace Database\Seeders;

use App\Enums\PostStatus;
use App\Models\Category;
use App\Models\Post;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

/**
 * Seeds a "Getting Started" tutorial series that doubles as living documentation
 * for the starter kit: how to publish a post, optimise SEO on a custom page, and
 * get the site indexed in Google Search Console. Each post is a structured EditorJS
 * body with SEO meta and a featured image. Idempotent — safe to run repeatedly.
 */
class BlogSeeder extends Seeder
{
    use WithoutModelEvents;

    public function run(): void
    {
        $gettingStarted = Category::firstOrCreate(
            ['slug' => 'getting-started'],
            [
                'name' => 'Getting Started',
                'description' => 'Set up, use, and extend this SEO-first blog starter kit.',
            ],
        );

        $seo = Category::firstOrCreate(
            ['slug' => 'seo'],
            [
                'name' => 'SEO',
                'description' => 'Practical, on-page SEO techniques you can apply today.',
            ],
        );

        foreach ($this->posts($gettingStarted->id, $seo->id) as $data) {
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
    protected function posts(int $gettingStartedCategoryId, int $seoCategoryId): array
    {
        return [
            [
                'category_id' => $gettingStartedCategoryId,
                'title' => 'Getting Started: Publish Your First Post',
                'slug' => 'getting-started-publish-your-first-post',
                'excerpt' => 'A step-by-step walkthrough of the admin panel — log in, write a post in the block editor, and publish it with SEO handled for you.',
                'content' => [
                    'time' => now()->timestamp,
                    'version' => '2.31.0',
                    'blocks' => [
                        ['type' => 'paragraph', 'data' => ['text' => 'This starter kit takes you from a fresh clone to a live, SEO-ready blog in minutes. This post walks you through the admin panel and publishing your very first article — the SEO is handled for you along the way.']],
                        ['type' => 'header', 'data' => ['text' => 'Log in to the admin panel', 'level' => 2]],
                        ['type' => 'paragraph', 'data' => ['text' => 'After running your migrations and seeders, visit <code>/admin</code> and sign in with the default account (<code>admin@example.com</code> / <code>password</code>). Change these before you go live — they live in <code>database/seeders/DatabaseSeeder.php</code>.']],
                        ['type' => 'header', 'data' => ['text' => 'Write a post', 'level' => 2]],
                        ['type' => 'list', 'data' => ['style' => 'ordered', 'items' => [
                            'Go to <b>Blog → Posts → New post</b>.',
                            'Type your <b>title</b>. It becomes the page\'s single H1 — the editor has no H1 block, so you can never create a second one by accident.',
                            'Write the body in the <b>EditorJS</b> block editor. Press <code>/</code> for the block picker: paragraph, heading, image, quote, list, code, and table.',
                            'Pick a <b>category</b> and, optionally, an <b>excerpt</b>. Leave the excerpt blank and it is derived from your first paragraph.',
                        ]]],
                        ['type' => 'header', 'data' => ['text' => 'Add a featured image', 'level' => 2]],
                        ['type' => 'paragraph', 'data' => ['text' => 'Upload a featured image and give it descriptive alt text. <b>Alt text is required</b> — a post with any image missing alt text cannot be published. This is deliberate: accessibility and image SEO are not optional.']],
                        ['type' => 'header', 'data' => ['text' => 'Publish or schedule', 'level' => 2]],
                        ['type' => 'paragraph', 'data' => ['text' => 'Set the status: <b>Draft</b> keeps it private, <b>Published</b> makes it live immediately, and <b>Scheduled</b> goes live automatically once its publish date is reached.']],
                        ['type' => 'quote', 'data' => ['text' => 'Clone, seed, write, publish — your blog is SEO-ready from the very first post.', 'caption' => 'The whole workflow']],
                        ['type' => 'header', 'data' => ['text' => 'What happens automatically', 'level' => 2]],
                        ['type' => 'paragraph', 'data' => ['text' => 'When you publish, the kit sets the canonical URL, Open Graph and Twitter Card tags, and JSON-LD structured data, then regenerates <code>/sitemap.xml</code> — no extra steps. Next, learn how to optimise a brand-new page such as a company home page.']],
                    ],
                ],
                'status' => PostStatus::Published,
                'show_toc' => true,
                'published_at' => now()->subDays(5),
                'image' => [
                    'url' => 'https://images.unsplash.com/photo-1555066931-4365d14bab8c?w=1200&q=80',
                    'alt' => 'A developer\'s screen showing code, representing setting up the blog starter kit',
                ],
                'seo' => [
                    'meta_title' => 'Getting Started: Publish Your First Post',
                    'meta_description' => 'A step-by-step walkthrough: log in to the admin, write a post in the block editor, and publish it with SEO handled automatically.',
                    'og_title' => 'Getting Started: Publish Your First Post',
                    'og_description' => 'From clone to a live, SEO-ready blog in minutes.',
                    'robots' => 'index, follow',
                ],
            ],
            [
                'category_id' => $seoCategoryId,
                'title' => 'How to Optimize SEO for a New Page',
                'slug' => 'optimize-seo-for-a-new-page',
                'excerpt' => 'Build a custom page — like an about or landing page — in three steps: a view, a route, and a few lines of SEO. The rest is automatic.',
                'content' => [
                    'time' => now()->timestamp,
                    'version' => '2.31.0',
                    'blocks' => [
                        ['type' => 'paragraph', 'data' => ['text' => 'The blog is SEO-ready out of the box, but most sites also need custom pages — an about page, a landing page, a marketing home page. Building one takes three steps: a view, a route, and a few lines of SEO. <i>Note: in this kit the <code>/</code> path already serves the blog, so a marketing page lives at its own path such as <code>/about</code>.</i>']],
                        ['type' => 'header', 'data' => ['text' => 'Step 1 — Create the view', 'level' => 2]],
                        ['type' => 'paragraph', 'data' => ['text' => 'Add a Blade file like <code>resources/views/pages/about.blade.php</code> and wrap it in <code>&lt;x-public-layout&gt;</code>. Use exactly one <code>&lt;h1&gt;</code> for the page heading. The layout already includes <code>&lt;x-seo-head /&gt;</code>, so every meta tag is emitted for you. Give each image descriptive alt text and <code>loading="lazy"</code>.']],
                        ['type' => 'code', 'data' => ['code' => "<x-public-layout>\n    <section class=\"mx-auto max-w-3xl px-4 py-12\">\n        <h1 class=\"text-3xl font-bold\">About Acme</h1>\n\n        <p class=\"mt-4 text-gray-600\">\n            We design and build web apps for growing businesses.\n        </p>\n\n        <img src=\"{{ asset('images/team.jpg') }}\"\n             alt=\"The Acme team at work\"\n             loading=\"lazy\"\n             class=\"mt-8 rounded-lg\">\n    </section>\n</x-public-layout>"]],
                        ['type' => 'header', 'data' => ['text' => 'Step 2 — Register a route', 'level' => 2]],
                        ['type' => 'paragraph', 'data' => ['text' => 'Point a named route at a controller (or a closure for something this simple) in <code>routes/web.php</code>:']],
                        ['type' => 'code', 'data' => ['code' => "Route::get('about', [PageController::class, 'about'])->name('about');"]],
                        ['type' => 'header', 'data' => ['text' => 'Step 3 — Set the page\'s SEO', 'level' => 2]],
                        ['type' => 'paragraph', 'data' => ['text' => 'In the controller, inject <code>SeoService</code> and set the title, description, and image. So is it really just those three? Yes — because everything else is derived automatically.']],
                        ['type' => 'code', 'data' => ['code' => "public function about(\\App\\Seo\\SeoService \$seo): \\Illuminate\\Contracts\\View\\View\n{\n    \$seo->title('About Acme — Custom Software Studio')\n        ->description('We design and build web apps for growing businesses.')\n        ->image(asset('images/og-about.png'));\n\n    return view('pages.about');\n}"]],
                        ['type' => 'header', 'data' => ['text' => 'What you never have to write', 'level' => 2]],
                        ['type' => 'paragraph', 'data' => ['text' => 'Those three lines are all the SEO you touch. From them the kit derives:']],
                        ['type' => 'list', 'data' => ['style' => 'unordered', 'items' => [
                            '<b>Canonical URL</b> — auto-set to the page\'s URL, always present.',
                            '<b>Open Graph and Twitter Card</b> tags, built from your title, description, and image.',
                            '<b>Sensible fallbacks</b> — set nothing at all and the global site defaults apply; the page is still valid.',
                        ]]],
                        ['type' => 'quote', 'data' => ['text' => 'Good SEO is the default here, not a checklist you have to remember.', 'caption' => 'The design goal']],
                        ['type' => 'header', 'data' => ['text' => 'Once it is live', 'level' => 2]],
                        ['type' => 'paragraph', 'data' => ['text' => 'With the page published, the last step is making sure Google can find and index it — covered in the next guide on Google Search Console.']],
                    ],
                ],
                'status' => PostStatus::Published,
                'show_toc' => true,
                'published_at' => now()->subDays(3),
                'image' => [
                    'url' => 'https://images.unsplash.com/photo-1496171367470-9ed9a91ea931?w=1200&q=80',
                    'alt' => 'A laptop showing code, representing adding SEO meta to a custom page',
                ],
                'seo' => [
                    'meta_title' => 'How to Optimize SEO for a New Page',
                    'meta_description' => 'Add a custom page and set its title, description, canonical, and social tags with the built-in SeoService — sensible fallbacks included.',
                    'og_title' => 'How to Optimize SEO for a New Page',
                    'og_description' => 'Give any custom page first-class SEO with the SeoService.',
                    'robots' => 'index, follow',
                ],
            ],
            [
                'category_id' => $seoCategoryId,
                'title' => 'Get Your Site Indexed with Google Search Console',
                'slug' => 'get-indexed-with-google-search-console',
                'excerpt' => 'Verify your domain, submit your sitemap, and request indexing so Google can discover and rank your new pages.',
                'content' => [
                    'time' => now()->timestamp,
                    'version' => '2.31.0',
                    'blocks' => [
                        ['type' => 'paragraph', 'data' => ['text' => 'Publishing a page does not put it in Google. You have to tell Google your site exists and let it crawl your sitemap. Google Search Console (GSC) is the free tool for exactly that.']],
                        ['type' => 'header', 'data' => ['text' => 'Set your production APP_URL', 'level' => 2]],
                        ['type' => 'paragraph', 'data' => ['text' => 'Before anything else, set <code>APP_URL</code> in your <code>.env</code> to your real domain. Canonical URLs, <code>og:url</code>, the sitemap, and media URLs are all absolute and derived from it — a wrong value quietly breaks indexing.']],
                        ['type' => 'header', 'data' => ['text' => 'Verify your domain', 'level' => 2]],
                        ['type' => 'paragraph', 'data' => ['text' => 'Create a property in Google Search Console for your site. Choose the HTML-tag method, copy the verification token, and paste it into <b>Admin → Settings → Google site verification</b>. The kit renders it as a meta tag in your <code>&lt;head&gt;</code> automatically.']],
                        ['type' => 'header', 'data' => ['text' => 'Submit your sitemap', 'level' => 2]],
                        ['type' => 'paragraph', 'data' => ['text' => 'The kit serves a sitemap at <code>/sitemap.xml</code> and keeps it current on every publish. In GSC, open <b>Sitemaps</b> and submit:']],
                        ['type' => 'code', 'data' => ['code' => 'https://yourdomain.com/sitemap.xml']],
                        ['type' => 'header', 'data' => ['text' => 'Request indexing', 'level' => 2]],
                        ['type' => 'paragraph', 'data' => ['text' => 'Use the <b>URL Inspection</b> tool, paste a page URL, and click <i>Request indexing</i> to push important pages to the front of the crawl queue. New pages are usually picked up within a few days.']],
                        ['type' => 'header', 'data' => ['text' => 'Check robots.txt is not blocking you', 'level' => 2]],
                        ['type' => 'paragraph', 'data' => ['text' => 'Visit <code>/robots.txt</code>. It should advertise your sitemap and disallow <code>/admin</code> — but never your public content. The kit generates this from your settings.']],
                        ['type' => 'quote', 'data' => ['text' => 'Indexing is the bridge between publishing and ranking — do not skip it.', 'caption' => 'Why this matters']],
                        ['type' => 'header', 'data' => ['text' => 'What to check back on', 'level' => 2]],
                        ['type' => 'list', 'data' => ['style' => 'unordered', 'items' => [
                            '<b>Coverage report</b> — confirms which pages are indexed and flags errors.',
                            '<b>Performance report</b> — shows impressions, clicks, and average position once data builds up.',
                        ]]],
                    ],
                ],
                'status' => PostStatus::Published,
                'show_toc' => true,
                'published_at' => now()->subDay(),
                'image' => [
                    'url' => 'https://images.unsplash.com/photo-1432888622747-4eb9a8efeb07?w=1200&q=80',
                    'alt' => 'A magnifying glass over an analytics report, representing search indexing in Google Search Console',
                ],
                'seo' => [
                    'meta_title' => 'Get Indexed with Google Search Console',
                    'meta_description' => 'Verify your domain, submit your /sitemap.xml, and request indexing so Google can discover and rank your new pages.',
                    'og_title' => 'Get Your Site Indexed with Google Search Console',
                    'og_description' => 'Verify, submit your sitemap, and request indexing the right way.',
                    'robots' => 'index, follow',
                ],
            ],
        ];
    }
}
