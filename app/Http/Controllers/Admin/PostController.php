<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StorePostRequest;
use App\Http\Requests\UpdatePostRequest;
use App\Models\Category;
use App\Models\Post;
use App\Services\PostService;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class PostController extends Controller
{
    public function __construct(protected PostService $posts) {}

    public function index(): View
    {
        return view('admin.posts.index', [
            'posts' => Post::with('category')->latest()->paginate(15),
        ]);
    }

    public function create(): View
    {
        return view('admin.posts.create', [
            'post' => null,
            'categories' => Category::orderBy('name')->get(),
        ]);
    }

    public function store(StorePostRequest $request): RedirectResponse
    {
        $this->posts->create($request->validated(), $request->file('featured_image'));

        return redirect()->route('admin.posts.index')->with('status', 'Post created.');
    }

    public function edit(Post $post): View
    {
        $post->load('seoMeta', 'media');

        return view('admin.posts.edit', [
            'post' => $post,
            'categories' => Category::orderBy('name')->get(),
        ]);
    }

    public function update(UpdatePostRequest $request, Post $post): RedirectResponse
    {
        $this->posts->update($post, $request->validated(), $request->file('featured_image'));

        return redirect()->route('admin.posts.index')->with('status', 'Post updated.');
    }

    public function destroy(Post $post): RedirectResponse
    {
        $post->delete();

        return back()->with('status', 'Post deleted.');
    }
}
