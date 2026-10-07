<?php

namespace App\Filament\Resources\StockReceiptResource\Pages;

use App\Filament\Resources\StockReceiptResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateStockReceipt extends CreateRecord
{
    protected static string $resource = StockReceiptResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
