<?php

use App\Http\Controllers\Admin\CategoryController;
use App\Http\Controllers\Admin\CommentController as AdminCommentController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\MediaController;
use App\Http\Controllers\Admin\PostController;
use App\Http\Controllers\Admin\RedirectController;
use App\Http\Controllers\Admin\SettingController;
use App\Http\Controllers\Frontend\BlogController;
use App\Http\Controllers\Frontend\CommentController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\RobotsController;
use App\Http\Controllers\SitemapController;
use Illuminate\Support\Facades\Route;

// The blog index is the site's home page.
Route::get('/', [BlogController::class, 'index'])->name('blog.index');

Route::get('sitemap.xml', SitemapController::class)->name('sitemap');
Route::get('robots.txt', RobotsController::class)->name('robots');

Route::get('blog/category/{category:slug}', [BlogController::class, 'category'])->name('blog.category');
Route::get('blog/{post:slug}', [BlogController::class, 'show'])->name('blog.show');
Route::post('blog/{post:slug}/comments', [CommentController::class, 'store'])
    ->middleware('throttle:6,1')
    ->name('blog.comments.store');

// The admin panel is the home for authenticated users. Keep the "dashboard"
// name so Breeze's post-auth redirects land on /admin.
Route::redirect('/dashboard', '/admin')->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

Route::middleware('auth')->prefix('admin')->name('admin.')->group(function () {
    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');

    Route::resource('posts', PostController::class)->except('show');
    Route::resource('categories', CategoryController::class)->except('show');

    Route::patch('redirects/{redirect}/toggle', [RedirectController::class, 'toggle'])->name('redirects.toggle');
    Route::resource('redirects', RedirectController::class)->except('show');

    Route::get('media', [MediaController::class, 'index'])->name('media.index');
    Route::post('media/upload', [MediaController::class, 'upload'])->name('media.upload');
    Route::patch('media/{media}', [MediaController::class, 'update'])->name('media.update');
    Route::delete('media/{media}', [MediaController::class, 'destroy'])->name('media.destroy');

    Route::get('settings', [SettingController::class, 'index'])->name('settings.index');
    Route::put('settings', [SettingController::class, 'update'])->name('settings.update');

    Route::get('comments', [AdminCommentController::class, 'index'])->name('comments.index');
    Route::patch('comments/{comment}/approve', [AdminCommentController::class, 'approve'])->name('comments.approve');
    Route::patch('comments/{comment}/spam', [AdminCommentController::class, 'spam'])->name('comments.spam');
    Route::delete('comments/{comment}', [AdminCommentController::class, 'destroy'])->name('comments.destroy');
});

require __DIR__.'/auth.php';
