@php
    $json = fn ($value) => $value ? json_encode($value, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) : null;
    $items = $items ?? [];
    $totalPages = max($totalPages ?? 1, 1);
    $totalPagesKnown = $totalPagesKnown ?? false;
    $canLoadMore = $canLoadMore ?? false;
@endphp

<x-filament-panels::page>
    <div class="grid gap-6 xl:grid-cols-[320px_1fr]">
        <x-filament::section
            heading="Search and filters"
            description="Narrow the CJ catalog with keywords, SKU, or category."
            icon="heroicon-o-adjustments-horizontal"
        >
            <div class="space-y-6">
                <form wire:submit.prevent="applyFilters" class="space-y-4">
                    <div class="space-y-1">
                        <label class="text-sm font-medium text-gray-700 dark:text-gray-200">Product name</label>
                        <x-filament::input
                            wire:model.defer="productName"
                            type="text"
                            class="w-full"
                            placeholder="Search by name or keyword"
                        />
                    </div>
                    <div class="space-y-1">
                        <label class="text-sm font-medium text-gray-700 dark:text-gray-200">Product SKU</label>
                        <x-filament::input
                            wire:model.defer="productSku"
                            type="text"
                            class="w-full"
                            placeholder="Exact SKU from CJ"
                        />
                    </div>
                    <div class="space-y-1">
                        <label class="text-sm font-medium text-gray-700 dark:text-gray-200">Material key</label>
                        <x-filament::input
                            wire:model.defer="materialKey"
                            type="text"
                            class="w-full"
                            placeholder="Cotton, alloy, etc."
                        />
                    </div>
                    <div class="space-y-1">
                        <label class="text-sm font-medium text-gray-700 dark:text-gray-200">Category ID</label>
                        <x-filament::input
                            wire:model.defer="categoryId"
                            type="text"
                            class="w-full"
                            placeholder="CJ category ID"
                        />
                    </div>
                    <div class="space-y-1">
                        <label class="text-sm font-medium text-gray-700 dark:text-gray-200">Store product ID</label>
                        <x-filament::input
                            wire:model.defer="storeProductId"
                            type="text"
                            class="w-full"
                            placeholder="Optional CJ store product ID"
                        />
                    </div>

                    <div class="grid gap-3 sm:grid-cols-2">
                        <div class="space-y-1">
                            <label class="text-sm font-medium text-gray-700 dark:text-gray-200">Page</label>
                            <x-filament::input
                                wire:model.defer="pageNum"
                                type="number"
                                min="1"
                                class="w-full"
                            />
                        </div>
                        <div class="space-y-1">
                            <label class="text-sm font-medium text-gray-700 dark:text-gray-200">Page size</label>
                            <x-filament::input
                                wire:model.defer="pageSize"
                                type="number"
                                min="10"
                                max="200"
                                class="w-full"
                            />
                        </div>
                    </div>

                    <x-filament::actions>
                        <x-filament::button type="submit" wire:loading.attr="disabled">Apply filters</x-filament::button>
                        <x-filament::button type="button" color="gray" wire:click="resetFilters" wire:loading.attr="disabled">
                            Reset
                        </x-filament::button>
                    </x-filament::actions>
                </form>

                <div class="rounded-lg border border-gray-200 bg-gray-50 p-3 text-xs text-gray-600 dark:border-gray-800 dark:bg-gray-900 dark:text-gray-300">
                    <p class="font-semibold text-gray-700 dark:text-gray-200">Tips</p>
                    <p class="mt-1">Filters use CJ catalog fields. Combine name + SKU for best matches.</p>
                    <p class="mt-1">Use Import to sync a product into your catalog with sync enabled.</p>
                    <p class="mt-1">Page size minimum is 10. Store product ID will query your CJ My Products list.</p>
                </div>
            </div>
        </x-filament::section>

        <div class="space-y-6">
            <x-filament::section
                heading="Catalog snapshot"
                description="Live totals for the current page."
                icon="heroicon-o-squares-2x2"
            >
                <div class="space-y-4">
                    <div class="flex flex-wrap gap-2">
                        <x-filament::badge color="gray">Total: {{ number_format($total) }}</x-filament::badge>
                        <x-filament::badge color="gray">Page {{ $pageNum }} of {{ $totalPagesKnown ? $totalPages : '--' }}</x-filament::badge>
                        <x-filament::badge color="gray">Loaded: {{ number_format($loaded) }}</x-filament::badge>
                        <x-filament::badge color="gray">Avg price: {{ $avgPrice ? '$' . $avgPrice : '--' }}</x-filament::badge>
                        <x-filament::badge color="success">Inventory: {{ number_format($inventoryTotal) }}</x-filament::badge>
                        <x-filament::badge color="gray">Images: {{ number_format($withImages) }}</x-filament::badge>
                        <x-filament::badge color="success">Sync enabled: {{ number_format($syncEnabledCount) }}</x-filament::badge>
                        <x-filament::badge color="warning">Sync disabled: {{ number_format($syncDisabledCount) }}</x-filament::badge>
                        @if ($syncStaleCount > 0)
                            <x-filament::badge color="danger">Stale sync: {{ number_format($syncStaleCount) }}</x-filament::badge>
                        @endif
                    </div>

                    <x-filament::actions>
                        <x-filament::button type="button" color="gray" wire:click="previousPage" :disabled="$pageNum <= 1" wire:loading.attr="disabled">
                            Previous
                        </x-filament::button>
                        <x-filament::button type="button" color="gray" wire:click="nextPage" :disabled="! $canLoadMore" wire:loading.attr="disabled">
                            Next
                        </x-filament::button>
                        <x-filament::button type="button" wire:click="loadMore" :disabled="! $canLoadMore" wire:loading.attr="disabled">
                            Load more
                        </x-filament::button>
                        <x-filament::button type="button" wire:click="fetch" wire:loading.attr="disabled">
                            Refresh
                        </x-filament::button>
                    </x-filament::actions>
                </div>
            </x-filament::section>

            <x-filament::section
                heading="Command center"
                description="Run bulk import and sync commands while keeping an eye on the latest job status."
                icon="heroicon-o-cog"
            >
                <div class="space-y-4">
                    <div class="space-y-1 text-xs text-gray-500 dark:text-gray-400">
                        <p>Last command: {{ $lastCommandMessage ?? 'No commands executed yet.' }}</p>
                        @if ($lastCommandAt)
                            <p>Last run: {{ $lastCommandAt }}</p>
                        @endif
                    </div>
                    <div class="grid gap-2 md:grid-cols-2">
                        <x-filament::button type="button" color="primary" wire:click="importDisplayedProducts" wire:loading.attr="disabled">
                            Import current page
                        </x-filament::button>
                        <x-filament::button type="button" color="secondary" wire:click="importMyProductsNow" wire:loading.attr="disabled">
                            Import CJ My Products
                        </x-filament::button>
                        <x-filament::button type="button" color="gray" wire:click="queueSyncJob" wire:loading.attr="disabled">
                            Queue sync job
                        </x-filament::button>
                        <x-filament::button type="button" wire:click="resetFilters" color="gray" wire:loading.attr="disabled">
                            Reset filters
                        </x-filament::button>
                    </div>
                </div>
            </x-filament::section>

            <x-filament::section
                heading="Catalog results"
                description="Sort, filter, and import CJ products with table actions."
                icon="heroicon-o-archive-box"
            >
                {{ $this->table }}
            </x-filament::section>

            <x-filament::section
                heading="Debug payload"
                description="Inspect the raw CJ response when troubleshooting."
                icon="heroicon-o-code-bracket-square"
            >
                <details class="group rounded-lg border border-gray-200 bg-gray-50 p-3 text-xs text-gray-700 dark:border-gray-800 dark:bg-gray-900 dark:text-gray-200">
                    <summary class="cursor-pointer font-medium text-gray-700 dark:text-gray-200">
                        Show raw payload
                    </summary>
                    <pre class="mt-3 overflow-auto">{{ $json($products) }}</pre>
                </details>
            </x-filament::section>
        </div>
    </div>

    @php
        $imagePreviewModalId = $this->getImagePreviewModalId();
    @endphp

    <x-filament::modal
        :id="$imagePreviewModalId"
        :heading="$imagePreviewName ?? 'Image preview'"
        :close-by-clicking-away="true"
        :close-by-escaping="true"
        :teleport="'body'"
        :width="'6xl'"
        :x-on:modal-closed="'if ($event.detail.id === ' . \Illuminate\Support\Js::from($imagePreviewModalId) . ') $wire.closeImagePreview()'"
    >
        <div class="space-y-4">
            <div class="flex items-center justify-center">
                @if ($imagePreviewUrl)
                    <img
                        src="{{ $imagePreviewUrl }}"
                        alt="{{ $imagePreviewName ?? 'CJ product image' }}"
                        class="max-h-[75vh] w-full rounded-lg bg-gray-50 object-contain dark:bg-gray-800"
                        loading="lazy"
                    />
                @else
                    <p class="text-sm text-gray-500 dark:text-gray-400">No image available.</p>
                @endif
            </div>

            @if (count($imagePreviewUrls) > 1)
                <div class="grid grid-cols-3 gap-2 sm:grid-cols-6">
                    @foreach ($imagePreviewUrls as $url)
                        <button
                            type="button"
                            wire:click="setActivePreviewImage({{ \Illuminate\Support\Js::from($url) }})"
                            @class([
                                'overflow-hidden rounded-lg border transition',
                                'border-primary-500 ring-2 ring-primary-200' => $imagePreviewUrl === $url,
                                'border-gray-200 hover:border-primary-300 dark:border-gray-800' => $imagePreviewUrl !== $url,
                            ])
                        >
                            <img
                                src="{{ $url }}"
                                alt="Thumbnail"
                                class="h-16 w-full object-cover"
                                loading="lazy"
                            />
                        </button>
                    @endforeach
                </div>
            @endif

            @if (count($videoPreviewUrls) > 0)
                <div class="space-y-2">
                    <div class="text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Videos</div>
                    <div class="grid gap-3 md:grid-cols-2">
                        @foreach ($videoPreviewUrls as $videoUrl)
                            <video
                                controls
                                preload="metadata"
                                class="w-full rounded-lg bg-black"
                                src="{{ $videoUrl }}"
                            ></video>
                        @endforeach
                    </div>
                </div>
            @endif
        </div>
    </x-filament::modal>
</x-filament-panels::page>
