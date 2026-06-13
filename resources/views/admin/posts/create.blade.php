<x-admin-layout>
    <x-slot name="header">
        <h1 class="text-xl font-semibold text-gray-800">New post</h1>
    </x-slot>

    @include('admin.posts._form')
</x-admin-layout>
