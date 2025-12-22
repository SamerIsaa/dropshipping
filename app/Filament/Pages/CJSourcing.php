<?php

declare(strict_types=1);

namespace App\Filament\Pages;

use BackedEnum;
use App\Infrastructure\Fulfillment\Clients\CJDropshippingClient;
use App\Services\Api\ApiException;
use Filament\Notifications\Notification;
use App\Filament\Pages\BasePage;
use UnitEnum;

class CJSourcing extends BasePage
{
    protected static BackedEnum|string|null $navigationIcon = 'heroicon-o-paper-airplane';

    protected static UnitEnum|string|null $navigationGroup = 'Integrations';

    protected static ?int $navigationSort = 93;

    protected string $view = 'filament.pages.cj-sourcing';

    public ?string $productUrl = null;
    public ?string $note = null;
    public ?string $sourceId = null;
    public ?array $results = null;
    public int $pageNum = 1;
    public int $pageSize = 20;

    public function mount(): void
    {
        $this->refreshList();
    }

    public function createRequest(): void
    {
        $this->validate([
            'productUrl' => ['required', 'url'],
            'sourceId' => ['required', 'string', 'max:80'],
            'note' => ['nullable', 'string', 'max:500'],
        ]);

        try {
            app(CJDropshippingClient::class)->createSourcing($this->productUrl, $this->note, $this->sourceId);
            Notification::make()->title('Sourcing submitted')->success()->send();
            $this->productUrl = null;
            $this->note = null;
            $this->sourceId = null;
            $this->refreshList();
        } catch (ApiException $e) {
            Notification::make()->title('CJ error')->body($e->getMessage())->danger()->send();
        } catch (\Throwable $e) {
            Notification::make()->title('Error')->body($e->getMessage())->danger()->send();
        }
    }

    public function refreshList(): void
    {
        try {
            $resp = app(CJDropshippingClient::class)->querySourcing(null, $this->pageNum, $this->pageSize);
            $this->results = $resp->data ?? null;
        } catch (ApiException $e) {
            Notification::make()->title('CJ error')->body($e->getMessage())->danger()->send();
        } catch (\Throwable $e) {
            Notification::make()->title('Error')->body($e->getMessage())->danger()->send();
        }
    }
}

