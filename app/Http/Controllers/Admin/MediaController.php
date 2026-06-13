<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreMediaRequest;
use App\Http\Requests\UpdateMediaRequest;
use App\Services\MediaService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class MediaController extends Controller
{
    public function __construct(protected MediaService $media) {}

    public function index(): View
    {
        return view('admin.media.index', [
            'media' => $this->media->all(),
        ]);
    }

    /**
     * Handle an upload. Returns an EditorJS-compatible JSON payload for XHR
     * requests, or redirects back for the media grid form (FR-49).
     */
    public function upload(StoreMediaRequest $request): JsonResponse|RedirectResponse
    {
        $media = $this->media->upload($request->file('file'));

        if ($request->expectsJson()) {
            return response()->json([
                'success' => 1,
                'file' => [
                    'url' => $media->getUrl(),
                    'alt' => $media->getCustomProperty('alt', ''),
                    'id' => $media->id,
                ],
            ]);
        }

        return back()->with('status', 'Image uploaded.');
    }

    public function update(UpdateMediaRequest $request, Media $media): RedirectResponse
    {
        $this->media->updateAltText($media, $request->validated('alt'));

        return back()->with('status', 'Alt text updated.');
    }

    public function destroy(Media $media): RedirectResponse
    {
        $usages = $this->media->usages($media);

        if ($usages->isNotEmpty() && ! request()->boolean('force')) {
            return back()->with('error', "This image is used in {$usages->count()} post(s). Confirm to delete it anyway.");
        }

        $this->media->delete($media);

        return back()->with('status', 'Image deleted.');
    }
}
