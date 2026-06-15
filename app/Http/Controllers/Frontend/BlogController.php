<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Post;
use App\Seo\SeoService;
use Illuminate\Contracts\View\View;

class BlogController extends Controller
{
    public function __construct(protected SeoService $seo) {}

    public function index(): View
    {
        $this->seo->title('Blog');

        return view('blog.index', [
            'posts' => Post::query()
                ->published()
                ->with('category')
                ->latest('published_at')
                ->paginate(12),
        ]);
    }

    public function show(Post $post): View
    {
        abort_unless($post->isPublished(), 404);

        $post->load(['category', 'seoMeta', 'media', 'approvedComments.user']);

        $this->seo->for($post);

        return view('blog.show', ['post' => $post]);
    }

    public function category(Category $category): View
    {
        $this->seo->for($category);

        return view('blog.category', [
            'category' => $category,
            'posts' => $category->posts()
                ->published()
                ->with('category')
                ->latest('published_at')
                ->paginate(12),
        ]);
    }
}
