<?php

namespace Database\Seeders;

use App\Enums\CommentStatus;
use App\Enums\PostStatus;
use App\Enums\RedirectType;
use App\Enums\UserRole;
use App\Models\Category;
use App\Models\Post;
use App\Models\Redirect;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

/**
 * Seeds a working example so a fresh clone shows a live blog post, a category
 * archive, structured SEO meta, and a redirect out of the box. Idempotent —
 * safe to run more than once.
 */
class DemoContentSeeder extends Seeder
{
    use WithoutModelEvents;

    public function run(): void
    {
        $category = Category::firstOrCreate(
            ['slug' => 'getting-started'],
            [
                'name' => 'Getting Started',
                'description' => 'Guides and tips for getting the most out of laraseo.',
            ],
        );

        $category->seoMeta()->updateOrCreate([], [
            'meta_title' => 'Getting Started Guides',
            'meta_description' => 'Tutorials and tips to help you launch an SEO-first blog with laraseo.',
        ]);

        $author = User::where('role', UserRole::Admin)->first();

        $post = Post::firstOrCreate(
            ['slug' => 'welcome-to-laraseo'],
            [
                'category_id' => $category->id,
                'author_id' => $author?->id,
                'title' => 'Welcome to laraseo',
                'excerpt' => 'An SEO-first Laravel blog starter — H1 locking, canonical URLs, sitemaps, and structured data, all enforced at the system level.',
                'content' => $this->sampleContent(),
                'status' => PostStatus::Published,
                'show_toc' => true,
                'published_at' => now()->subDay(),
            ],
        );

        $post->seoMeta()->updateOrCreate([], [
            'meta_title' => 'Welcome to laraseo — the SEO-first blog starter',
            'meta_description' => 'See how laraseo enforces SEO at the system level: locked H1, required alt text, auto canonical URLs, sitemaps, and JSON-LD structured data.',
            'og_title' => 'Welcome to laraseo',
            'og_description' => 'An SEO-first Laravel 13 blog starter kit.',
            'robots' => 'index, follow',
        ]);

        // An approved sample comment plus one awaiting moderation, so a fresh
        // clone shows both the public comment list and the admin badge.
        $post->comments()->firstOrCreate(
            ['author_email' => 'reader@example.com'],
            [
                'author_name' => 'Sam Reader',
                'body' => 'This is exactly the SEO-first starter I was looking for. Thanks!',
                'status' => CommentStatus::Approved,
                'approved_at' => now()->subHours(12),
            ],
        );

        $post->comments()->firstOrCreate(
            ['author_email' => 'pending@example.com'],
            [
                'author_name' => 'Pat Pending',
                'body' => 'Looks great — does it support multiple authors?',
                'status' => CommentStatus::Pending,
            ],
        );

        Redirect::firstOrCreate(
            ['from_url' => '/welcome'],
            [
                'to_url' => '/blog/welcome-to-laraseo',
                'type' => RedirectType::Permanent,
                'is_active' => true,
            ],
        );
    }

    /**
     * A sample EditorJS document exercising every block type the server-side
     * renderer supports (paragraph, header H2/H3, list, quote, code, table,
     * image, delimiter).
     *
     * @return array<string, mixed>
     */
    protected function sampleContent(): array
    {
        return [
            'time' => now()->timestamp,
            'version' => '2.31.0',
            'blocks' => [
                ['type' => 'paragraph', 'data' => ['text' => 'laraseo is a Laravel 13 blog starter where SEO is enforced by the system, not left to the developer. This sample post shows the available content blocks.']],
                ['type' => 'header', 'data' => ['text' => 'Why SEO-first', 'level' => 2]],
                ['type' => 'paragraph', 'data' => ['text' => 'Every page ships a canonical URL, Open Graph tags, and <b>JSON-LD structured data</b> automatically. You write content; the system handles the metadata.']],
                ['type' => 'header', 'data' => ['text' => 'What is enforced', 'level' => 3]],
                ['type' => 'list', 'data' => ['style' => 'unordered', 'items' => [
                    'The H1 is always and only the post title.',
                    'Alt text is required on every image before publishing.',
                    'A canonical URL is always present in the head.',
                ]]],
                ['type' => 'header', 'data' => ['text' => 'Launch checklist', 'level' => 3]],
                ['type' => 'list', 'data' => ['style' => 'checklist', 'items' => [
                    ['content' => 'Clone the repository', 'meta' => ['checked' => true], 'items' => []],
                    ['content' => 'Run migrations and seed', 'meta' => ['checked' => true], 'items' => []],
                    ['content' => 'Write your first post', 'meta' => ['checked' => false], 'items' => []],
                ]]],
                ['type' => 'quote', 'data' => ['text' => 'Good SEO is the result of good defaults, not constant vigilance.', 'caption' => 'The laraseo philosophy']],
                ['type' => 'image', 'data' => ['file' => ['url' => '/images/seo-demo.svg'], 'caption' => 'A diagram showing how laraseo wires SEO metadata into every page.']],
                ['type' => 'header', 'data' => ['text' => 'Code example', 'level' => 2]],
                ['type' => 'paragraph', 'data' => ['text' => 'Rendering a post is as simple as resolving the SEO service for the model:']],
                ['type' => 'code', 'data' => ['code' => "\$this->seo->for(\$post);\nreturn view('blog.show', compact('post'));"]],
                ['type' => 'header', 'data' => ['text' => 'Feature comparison', 'level' => 2]],
                ['type' => 'table', 'data' => ['withHeadings' => true, 'content' => [
                    ['Feature', 'Enforced'],
                    ['Locked H1', 'Yes'],
                    ['Required alt text', 'Yes'],
                    ['Auto canonical URL', 'Yes'],
                ]]],
                ['type' => 'delimiter', 'data' => []],
                ['type' => 'paragraph', 'data' => ['text' => 'Edit or delete this post from the admin panel to make laraseo your own.']],
            ],
        ];
    }
}
