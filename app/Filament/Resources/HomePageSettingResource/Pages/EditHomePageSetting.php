<?php

declare(strict_types=1);

namespace App\Filament\Resources\HomePageSettingResource\Pages;

use App\Filament\Resources\HomePageSettingResource;
use Filament\Resources\Pages\EditRecord;

class EditHomePageSetting extends EditRecord
{
    protected static string $resource = HomePageSettingResource::class;
}
