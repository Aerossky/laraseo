<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StorePostRequest;
use App\Http\Requests\UpdatePostRequest;
use App\Models\Category;
use App\Models\Post;
use App\Services\MediaService;
use App\Services\PostService;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class PostController extends Controller
{
    public function __construct(protected PostService $posts, protected MediaService $media) {}

    public function index(): View
    {
        $this->authorize('viewAny', Post::class);

        $user = request()->user();

        return view('admin.posts.index', [
            'posts' => Post::with(['category', 'author'])
                // Authors only see their own posts; content managers see all.
                ->unless($user->managesContent(), fn ($query) => $query->where('author_id', $user->id))
                ->latest()
                ->paginate(15),
        ]);
    }

    public function create(): View
    {
        $this->authorize('create', Post::class);

        return view('admin.posts.create', [
            'post' => null,
            'categories' => Category::orderBy('name')->get(),
            'library' => $this->media->all(),
        ]);
    }

    public function store(StorePostRequest $request): RedirectResponse
    {
        $this->authorize('create', Post::class);

        $this->posts->create(
            [...$request->validated(), 'author_id' => $request->user()->id],
            $request->file('featured_image'),
        );

        return redirect()->route('admin.posts.index')->with('status', 'Post created.');
    }

    public function edit(Post $post): View
    {
        $this->authorize('update', $post);

        $post->load('seoMeta', 'media');

        return view('admin.posts.edit', [
            'post' => $post,
            'categories' => Category::orderBy('name')->get(),
            'library' => $this->media->all(),
        ]);
    }

    public function update(UpdatePostRequest $request, Post $post): RedirectResponse
    {
        $this->authorize('update', $post);

        $this->posts->update($post, $request->validated(), $request->file('featured_image'));

        return redirect()->route('admin.posts.index')->with('status', 'Post updated.');
    }

    public function destroy(Post $post): RedirectResponse
    {
        $this->authorize('delete', $post);

        $post->delete();

        return back()->with('status', 'Post deleted.');
    }
}
