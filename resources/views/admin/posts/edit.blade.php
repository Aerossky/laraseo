<x-admin-layout>
    <x-slot name="header">
        <h1 class="text-xl font-semibold text-gray-800">Edit post</h1>
        <x-post-status :status="$post->status" />
    </x-slot>

    @include('admin.posts._form')
</x-admin-layout>
