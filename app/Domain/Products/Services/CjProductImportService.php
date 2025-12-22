<?php

declare(strict_types=1);

namespace App\Domain\Products\Services;

use App\Domain\Products\Models\Category;
use App\Domain\Products\Models\Product;
use App\Domain\Products\Models\ProductVariant;
use App\Infrastructure\Fulfillment\Clients\CJDropshippingClient;
use Illuminate\Support\Str;

class CjProductImportService
{
    public function __construct(
        private readonly CJDropshippingClient $client,
        private readonly CjProductMediaService $mediaService,
    ) {
    }

    public function importByLookup(string $lookupType, string $lookupValue, array $options = []): ?Product
    {
        $productResp = $this->client->getProductBy([$lookupType => $lookupValue]);
        $productData = $productResp->data ?? null;

        if (! is_array($productData) || $productData === []) {
            return null;
        }

        return $this->importFromPayload($productData, null, $options);
    }

    public function importByPid(string $pid, array $options = []): ?Product
    {
        $productResp = $this->client->getProduct($pid);
        $productData = $productResp->data ?? null;

        if (! is_array($productData) || $productData === []) {
            return null;
        }

        return $this->importFromPayload($productData, null, $options);
    }

    public function importFromPayload(array $productData, ?array $variants = null, array $options = []): ?Product
    {
        $pid = $this->resolvePid($productData);
        if ($pid === '') {
            return null;
        }

        $product = Product::query()->where('cj_pid', $pid)->first();

        $respectSyncFlag = (bool) ($options['respectSyncFlag'] ?? true);
        $defaultSyncEnabled = (bool) ($options['defaultSyncEnabled'] ?? true);
        $respectLocks = (bool) ($options['respectLocks'] ?? true);
        if ($product && $respectSyncFlag && $product->cj_sync_enabled === false) {
            return $product;
        }

        if ($product && ! ($options['updateExisting'] ?? true)) {
            return $product;
        }

        $lockPrice = $respectLocks && (bool) ($product?->cj_lock_price);
        $lockDescription = $respectLocks && (bool) ($product?->cj_lock_description);
        $lockImages = $respectLocks && (bool) ($product?->cj_lock_images);
        $lockVariants = $respectLocks && (bool) ($product?->cj_lock_variants);

        if ($variants === null) {
            $variantResp = $this->client->getVariantsByPid($pid);
            $variants = $variantResp->data ?? [];
        }

        $category = $this->resolveCategoryFromPayload($productData);

        $name = $productData['productNameEn'] ?? $productData['productName'] ?? ($productData['name'] ?? 'CJ Product');
        $slug = Str::slug($name . '-' . $pid);
        $price = $productData['productSellPrice'] ?? null;
        $priceValue = is_numeric($price) ? (float) $price : null;
        $incomingDescription = $this->mediaService->cleanDescription(
            $productData['descriptionEn']
                ?? $productData['productDescriptionEn']
                ?? $productData['description']
                ?? $productData['productDescription']
                ?? null
        );
        $description = $lockDescription ? ($product?->description ?? $incomingDescription) : $incomingDescription;

        $payloadAttributes = is_array($productData['attributes'] ?? null) ? $productData['attributes'] : [];
        $existingAttributes = is_array($product?->attributes) ? $product->attributes : [];

        $attributes = array_merge(
            $existingAttributes,
            $payloadAttributes,
            [
                'cj_pid' => $pid,
                'cj_payload' => $productData,
            ]
        );

        $payload = [
            'name' => $name,
            'category_id' => $category?->id,
            'description' => $description,
            'selling_price' => $lockPrice ? ($product?->selling_price ?? 0) : ($priceValue ?? ($product?->selling_price ?? 0)),
            'cost_price' => $lockPrice ? ($product?->cost_price ?? 0) : ($priceValue ?? ($product?->cost_price ?? 0)),
            'currency' => $productData['currency'] ?? 'USD',
            'attributes' => $attributes,
            'source_url' => $productData['productUrl'] ?? $productData['sourceUrl'] ?? null,
            'cj_synced_at' => now(),
        ];

        $syncVariants = ($options['syncVariants'] ?? true) === true && ! $lockVariants;
        $syncImages = ($options['syncImages'] ?? true) === true && ! $lockImages;
        $imagesUpdated = false;
        $videosUpdated = false;

        $changedFields = $product
            ? $this->diffFields($product, [
                'name' => $payload['name'],
                'description' => $payload['description'],
                'selling_price' => $payload['selling_price'],
                'cost_price' => $payload['cost_price'],
                'category_id' => $payload['category_id'],
                'currency' => $payload['currency'],
                'source_url' => $payload['source_url'],
            ])
            : ['created'];

        if ($syncVariants) {
            $changedFields[] = 'variants';
        }

        $payload['cj_last_payload'] = $productData;
        $payload['cj_last_changed_fields'] = array_values(array_unique($changedFields));

        if (! $product) {
            $payload['cj_pid'] = $pid;
            $payload['slug'] = $slug;
            $payload['status'] = 'active';
            $payload['is_active'] = true;
            $payload['is_featured'] = false;
            $payload['cj_sync_enabled'] = $defaultSyncEnabled;
            $product = Product::create($payload);
        } else {
            $product->fill($payload);
            if (! $product->slug) {
                $product->slug = $slug;
            }
            $product->save();
        }

        if ($syncVariants) {
            $this->syncVariants($product, $variants, $pid);
        }

        if ($syncImages) {
            $imagesUpdated = $this->mediaService->syncImages($product, $productData, $variants);
            $videosUpdated = $this->mediaService->syncVideos($product, $productData, $variants);
        }

        if ($imagesUpdated || $videosUpdated) {
            if ($imagesUpdated) {
                $changedFields[] = 'images';
            }
            if ($videosUpdated) {
                $changedFields[] = 'videos';
            }

            $product->update([
                'cj_last_changed_fields' => array_values(array_unique($changedFields)),
            ]);
        }

        return $product;
    }

    public function syncMedia(Product $product, array $options = []): bool
    {
        if (! $product->cj_pid) {
            return false;
        }

        $respectSyncFlag = (bool) ($options['respectSyncFlag'] ?? true);
        $respectLocks = (bool) ($options['respectLocks'] ?? true);

        if ($respectSyncFlag && $product->cj_sync_enabled === false) {
            return false;
        }

        if ($respectLocks && $product->cj_lock_images) {
            return false;
        }

        $productResp = $this->client->getProduct($product->cj_pid);
        $productData = $productResp->data ?? null;

        if (! is_array($productData) || $productData === []) {
            return false;
        }

        $variantResp = $this->client->getVariantsByPid($product->cj_pid);
        $variants = $variantResp->data ?? [];

        $imagesUpdated = $this->mediaService->syncImages($product, $productData, $variants);
        $videosUpdated = $this->mediaService->syncVideos($product, $productData, $variants);

        if (! $imagesUpdated && ! $videosUpdated) {
            return false;
        }

        $changedFields = is_array($product->cj_last_changed_fields) ? $product->cj_last_changed_fields : [];

        if ($imagesUpdated) {
            $changedFields[] = 'images';
        }

        if ($videosUpdated) {
            $changedFields[] = 'videos';
        }

        $product->update([
            'cj_last_payload' => $productData,
            'cj_last_changed_fields' => array_values(array_unique($changedFields)),
            'cj_synced_at' => now(),
        ]);

        return true;
    }

    public function syncMyProducts(int $startPage = 1, int $pageSize = 24, int $maxPages = 50, bool $forceUpdate = false): array
    {
        $imported = 0;
        $processed = 0;
        $errors = 0;
        $lastPage = $startPage;

        for ($i = 0; $i < $maxPages; $i++) {
            $page = $startPage + $i;
            $lastPage = $page;

            $resp = $this->client->listMyProducts([
                'pageNum' => $page,
                'pageSize' => $pageSize,
            ]);

            $content = $resp->data['content'][0]['productList'] ?? [];

            if (! is_array($content) || $content === []) {
                break;
            }

            foreach ($content as $item) {
                if (! is_array($item)) {
                    continue;
                }

                $pid = (string) ($item['pid'] ?? $item['id'] ?? $item['productId'] ?? $item['product_id'] ?? '');
                if ($pid === '') {
                    continue;
                }

                $processed++;

                try {
                    $product = $this->importByPid($pid, [
                        'respectSyncFlag' => ! $forceUpdate,
                        'defaultSyncEnabled' => true,
                    ]);

                    if ($product) {
                        $imported++;
                    }
                } catch (\Throwable) {
                    $errors++;
                }
            }

            if (count($content) < $pageSize) {
                break;
            }
        }

        return [
            'imported' => $imported,
            'processed' => $processed,
            'errors' => $errors,
            'last_page' => $lastPage,
        ];
    }

    private function syncVariants(Product $product, mixed $variants, string $pid): void
    {
        if (is_array($variants) && $variants !== []) {
            foreach ($variants as $variant) {
                if (! is_array($variant)) {
                    continue;
                }

                $vid = (string) ($variant['vid'] ?? '');
                $sku = $variant['variantSku'] ?? $variant['sku'] ?? null;

                if (! $sku && ! $vid) {
                    continue;
                }

                ProductVariant::updateOrCreate(
                    [
                        'product_id' => $product->id,
                        'cj_vid' => $vid ?: null,
                        'sku' => $sku,
                    ],
                    [
                        'title' => $variant['variantName'] ?? ($variant['variantKey'] ?? 'Variant'),
                        'price' => is_numeric($variant['variantSellPrice'] ?? null) ? (float) $variant['variantSellPrice'] : ($product->selling_price ?? 0),
                        'cost_price' => is_numeric($variant['variantSellPrice'] ?? null) ? (float) $variant['variantSellPrice'] : ($product->cost_price ?? 0),
                        'currency' => $product->currency ?? 'USD',
                        'metadata' => [
                            'cj_vid' => $vid,
                            'cj_variant' => $variant,
                        ],
                    ]
                );
            }

            return;
        }

        if (! $product->variants()->exists()) {
            $product->variants()->create([
                'title' => 'Default',
                'price' => $product->selling_price ?? 0,
                'cost_price' => $product->cost_price ?? 0,
                'currency' => $product->currency ?? 'USD',
                'metadata' => [
                    'cj_pid' => $pid,
                ],
            ]);
        }
    }

    private function resolvePid(array $productData): string
    {
        return (string) ($productData['pid']
            ?? $productData['productId']
            ?? $productData['product_id']
            ?? $productData['id']
            ?? '');
    }

    private function resolveCategoryFromPayload(array $productData): ?Category
    {
        $categoryId = (string) ($productData['categoryId'] ?? '');

        if ($categoryId !== '') {
            $existing = Category::query()->where('cj_id', $categoryId)->first();
            if ($existing) {
                return $existing;
            }
        }

        $rawName = $productData['categoryName']
            ?? $productData['categoryNameEn']
            ?? $productData['category_name']
            ?? null;

        if (! is_string($rawName) || $rawName === '') {
            return null;
        }

        $segments = array_filter(array_map('trim', explode('/', $rawName)));
        if ($segments === []) {
            return null;
        }

        $parent = null;
        $category = null;
        $totalSegments = count($segments);

        foreach ($segments as $position => $segment) {
            if ($segment === '') {
                continue;
            }

            $slug = Str::slug($parent ? "{$parent->slug} {$segment}" : $segment);
            $category = Category::firstOrCreate(
                [
                    'name' => $segment,
                    'parent_id' => $parent?->id,
                ],
                [
                    'slug' => $slug,
                    'parent_id' => $parent?->id,
                ]
            );

            if ($position === $totalSegments - 1 && $categoryId !== '' && $category->cj_id !== $categoryId) {
                $category->update(['cj_id' => $categoryId]);
            }

            $parent = $category;
        }

        return $category;
    }

    private function diffFields(Product $product, array $incoming): array
    {
        $changed = [];

        foreach ($incoming as $field => $value) {
            $current = $product->{$field};

            if (in_array($field, ['selling_price', 'cost_price'], true)) {
                $current = $current !== null ? (float) $current : null;
                $value = $value !== null ? (float) $value : null;
            }

            if ($current !== $value) {
                $changed[] = $field;
            }
        }

        return $changed;
    }
}
