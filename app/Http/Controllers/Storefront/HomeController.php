<?php

declare(strict_types=1);

namespace App\Http\Controllers\Storefront;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Storefront\Concerns\FormatsCategories;
use App\Http\Controllers\Storefront\Concerns\TransformsProducts;
use App\Models\Category;
use App\Models\HomePageSetting;
use App\Models\OrderItem;
use App\Models\Product;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use Inertia\Response;

class HomeController extends Controller
{
    use TransformsProducts;
    use FormatsCategories;

    public function index(): Response
    {
        $baseQuery = Product::query()
            ->where('is_active', true)
            ->with(['images', 'category', 'variants'])
            ->withAvg('reviews', 'rating')
            ->withCount('reviews');

        $featured = (clone $baseQuery)
            ->where('is_featured', true)
            ->latest()
            ->take(6)
            ->get();

        if ($featured->isEmpty()) {
            $featured = (clone $baseQuery)
                ->latest()
                ->take(6)
                ->get();
        }

        $bestSellerIds = $this->topSellingProductIds();
        $bestSellersQuery = (clone $baseQuery);
        if (! empty($bestSellerIds)) {
            $bestSellersQuery
                ->whereIn('products.id', $bestSellerIds)
                ->orderByRaw(DB::raw('FIELD(products.id, ' . implode(',', $bestSellerIds) . ')'));
        } else {
            $bestSellersQuery->orderByDesc('selling_price');
        }
        $bestSellers = $bestSellersQuery
            ->take(6)
            ->get();

        $recommendedQuery = clone $baseQuery;
        if ($featured->isNotEmpty()) {
            $recommendedQuery->whereNotIn('id', $featured->pluck('id'));
        }
        $recommended = $recommendedQuery
            ->inRandomOrder()
            ->take(6)
            ->get();

        $categoryList = $this->rootCategoriesTree(['children', 'children.children']);

        $categoryHighlights = Cache::remember('home:category-highlights', now()->addMinutes(15), function () {
            return Category::query()
                ->withCount('products')
                ->orderByDesc('products_count')
                ->take(8)
                ->get()
                ->map(fn (Category $category) => [
                    'name' => $category->name,
                    'count' => $category->products_count,
                ]);
        });

        $homeContent = HomePageSetting::query()->latest()->first();
        $heroSlides = $homeContent?->hero_slides ?? [];
        if (is_array($heroSlides)) {
            $heroSlides = collect($heroSlides)->map(function (array $slide) {
                $image = $slide['image'] ?? null;
                if ($image && ! str_starts_with($image, 'http://') && ! str_starts_with($image, 'https://')) {
                    $slide['image'] = Storage::disk('public')->url($image);
                }
                return $slide;
            })->values()->all();
        }

        return Inertia::render('Home', [
            'featured' => $featured->map(fn (Product $product) => $this->transformProduct($product)),
            'bestSellers' => $bestSellers->map(fn (Product $product) => $this->transformProduct($product)),
            'recommended' => $recommended->map(fn (Product $product) => $this->transformProduct($product)),
            'categories' => $categoryList,
            'categoryHighlights' => $categoryHighlights,
            'currency' => 'USD',
            'homeContent' => $homeContent ? [
                'top_strip' => $homeContent->top_strip,
                'hero_slides' => $heroSlides,
                'rail_cards' => $homeContent->rail_cards,
                'banner_strip' => $homeContent->banner_strip,
            ] : null,
        ]);
    }

    private function topSellingProductIds(): array
    {
        return Cache::remember('home:top-selling-product-ids', now()->addMinutes(8), function () {
            return OrderItem::query()
                ->select('product_variants.product_id', DB::raw('SUM(order_items.quantity) as units'))
                ->join('product_variants', 'product_variants.id', '=', 'order_items.product_variant_id')
                ->groupBy('product_variants.product_id')
                ->orderByDesc('units')
                ->limit(6)
                ->pluck('product_variants.product_id')
                ->map(fn ($value) => (int) $value)
                ->values()
                ->all();
        });
    }
}
