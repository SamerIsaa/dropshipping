@php
    $json = fn ($value) => $value ? json_encode($value, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) : null;
    $list = $results['list'] ?? $results['data'] ?? $results;
    $list = is_array($list) ? $list : [];
@endphp

<x-filament-panels::page>
    <div class="space-y-4">
        <x-filament::section
            heading="Create sourcing request"
            description="Submit a product URL with its source marketplace id."
            icon="heroicon-o-paper-airplane"
        >
            <form wire:submit.prevent="createRequest" class="space-y-4">
                <div class="grid gap-4 md:grid-cols-3">
                    <div class="space-y-1 md:col-span-2">
                        <label class="text-sm font-medium text-gray-700 dark:text-gray-200">Product URL</label>
                        <x-filament::input
                            wire:model.defer="productUrl"
                            type="url"
                            required
                            class="w-full"
                            placeholder="https://..."
                        />
                        @error('productUrl')
                            <p class="text-xs text-danger-600">{{ $message }}</p>
                        @enderror
                    </div>
                    <div class="space-y-1">
                        <label class="text-sm font-medium text-gray-700 dark:text-gray-200">Source ID</label>
                        <x-filament::input
                            wire:model.defer="sourceId"
                            type="text"
                            required
                            class="w-full"
                            placeholder="AliExpress, 1688, Amazon"
                        />
                        <p class="text-xs text-gray-500">CJ requires the source marketplace id.</p>
                        @error('sourceId')
                            <p class="text-xs text-danger-600">{{ $message }}</p>
                        @enderror
                    </div>
                </div>
                <div class="space-y-1">
                    <label class="text-sm font-medium text-gray-700 dark:text-gray-200">Note (optional)</label>
                    <textarea
                        wire:model.defer="note"
                        rows="3"
                        class="fi-input block w-full"
                        placeholder="Any context to help sourcing"
                    ></textarea>
                </div>
                <x-filament::actions>
                    <x-filament::button type="submit">Submit sourcing</x-filament::button>
                </x-filament::actions>
            </form>
        </x-filament::section>

        <x-filament::section
            heading="Sourcing requests"
            description="Review the most recent sourcing activity."
            icon="heroicon-o-clipboard-document-list"
        >
            <form wire:submit.prevent="refreshList" class="flex flex-wrap items-end gap-4">
                <div class="space-y-1">
                    <label class="text-sm font-medium text-gray-700 dark:text-gray-200">Page</label>
                    <x-filament::input
                        wire:model.defer="pageNum"
                        type="number"
                        min="1"
                        class="w-24"
                    />
                </div>
                <div class="space-y-1">
                    <label class="text-sm font-medium text-gray-700 dark:text-gray-200">Page size</label>
                    <x-filament::input
                        wire:model.defer="pageSize"
                        type="number"
                        min="1"
                        max="200"
                        class="w-24"
                    />
                </div>
                <x-filament::actions>
                    <x-filament::button type="submit" color="gray">Refresh list</x-filament::button>
                </x-filament::actions>
            </form>

            <div class="mt-4 overflow-auto rounded-lg border bg-white dark:bg-gray-900 dark:border-gray-800">
                <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-800">
                    <thead class="bg-gray-50 dark:bg-gray-800">
                        <tr>
                            <th class="px-4 py-2 text-left text-xs font-semibold text-gray-600 dark:text-gray-200">Sourcing ID</th>
                            <th class="px-4 py-2 text-left text-xs font-semibold text-gray-600 dark:text-gray-200">Product</th>
                            <th class="px-4 py-2 text-left text-xs font-semibold text-gray-600 dark:text-gray-200">Status</th>
                            <th class="px-4 py-2 text-left text-xs font-semibold text-gray-600 dark:text-gray-200">Created</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 dark:divide-gray-800">
                        @forelse ($list as $item)
                            <tr>
                                <td class="px-4 py-2 text-sm text-gray-900 dark:text-gray-100">{{ $item['cjSourcingId'] ?? $item['sourcingId'] ?? '' }}</td>
                                <td class="px-4 py-2 text-sm text-gray-900 dark:text-gray-100">{{ $item['productName'] ?? $item['productUrl'] ?? '' }}</td>
                                <td class="px-4 py-2 text-sm text-gray-900 dark:text-gray-100">{{ $item['status'] ?? '' }}</td>
                                <td class="px-4 py-2 text-sm text-gray-900 dark:text-gray-100">{{ $item['createDate'] ?? '' }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="px-4 py-3 text-sm text-gray-500">No sourcing records.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </x-filament::section>

        <x-filament::section
            heading="Raw data"
            description="Inspect the CJ payload returned for the sourcing list."
            icon="heroicon-o-code-bracket-square"
        >
            <x-filament::fieldset label="Response">
                <pre class="rounded-lg border bg-gray-50 p-3 text-xs text-gray-700 overflow-auto dark:bg-gray-900 dark:border-gray-800 dark:text-gray-200">{{ $json($results) }}</pre>
            </x-filament::fieldset>
        </x-filament::section>
    </div>
</x-filament-panels::page>
