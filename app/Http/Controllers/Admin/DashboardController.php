<?php

namespace App\Http\Controllers\Admin;

use App\Enums\PostStatus;
use App\Http\Controllers\Controller;
use App\Models\Post;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(): View
    {
        return view('admin.dashboard', [
            'totalPosts' => Post::count(),
            'draftPosts' => Post::where('status', PostStatus::Draft)->count(),
            'publishedPosts' => Post::where('status', PostStatus::Published)->count(),
            'recentPosts' => Post::with('category')->latest()->take(5)->get(),
        ]);
    }
}
