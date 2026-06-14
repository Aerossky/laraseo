<?php

namespace App\Services;

use App\Models\Category;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;

class CategoryService
{
    /**
     * @param  array<string, mixed>  $data
     */
    public function create(array $data): Category
    {
        return DB::transaction(function () use ($data) {
            $category = Category::create($this->attributes($data));
            $this->syncSeo($category, $data['seo'] ?? []);

            return $category;
        });
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function update(Category $category, array $data): Category
    {
        return DB::transaction(function () use ($category, $data) {
            $category->update($this->attributes($data));
            $this->syncSeo($category, $data['seo'] ?? []);

            return $category;
        });
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    protected function attributes(array $data): array
    {
        return Arr::only($data, ['name', 'slug', 'description']);
    }

    /**
     * @param  array<string, mixed>  $seo
     */
    protected function syncSeo(Category $category, array $seo): void
    {
        $category->seoMeta()->updateOrCreate([], [
            'meta_title' => $seo['meta_title'] ?? null,
            'meta_description' => $seo['meta_description'] ?? null,
            'canonical_url' => $seo['canonical_url'] ?? null,
            'robots' => $seo['robots'] ?? 'index, follow',
            'og_title' => $seo['og_title'] ?? null,
            'og_description' => $seo['og_description'] ?? null,
            'og_image' => $seo['og_image'] ?? null,
        ]);
    }
}
