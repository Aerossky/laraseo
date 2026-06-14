<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreRedirectRequest;
use App\Http\Requests\UpdateRedirectRequest;
use App\Models\Redirect;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class RedirectController extends Controller
{
    public function index(): View
    {
        return view('admin.redirects.index', [
            'redirects' => Redirect::latest()->paginate(15),
        ]);
    }

    public function create(): View
    {
        return view('admin.redirects.create', [
            'redirect' => null,
        ]);
    }

    public function store(StoreRedirectRequest $request): RedirectResponse
    {
        Redirect::create($request->validated());

        return redirect()->route('admin.redirects.index')->with('status', 'Redirect created.');
    }

    public function edit(Redirect $redirect): View
    {
        return view('admin.redirects.edit', [
            'redirect' => $redirect,
        ]);
    }

    public function update(UpdateRedirectRequest $request, Redirect $redirect): RedirectResponse
    {
        $redirect->update($request->validated());

        return redirect()->route('admin.redirects.index')->with('status', 'Redirect updated.');
    }

    public function toggle(Redirect $redirect): RedirectResponse
    {
        $redirect->update(['is_active' => ! $redirect->is_active]);

        return back()->with('status', $redirect->is_active ? 'Redirect activated.' : 'Redirect deactivated.');
    }

    public function destroy(Redirect $redirect): RedirectResponse
    {
        $redirect->delete();

        return back()->with('status', 'Redirect deleted.');
    }
}
