<?php

namespace App\Filament\Resources\SaleResource\Pages;

use App\Filament\Resources\SaleResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

use App\Models\InventoryBalance;
use App\Models\StockMovement;
use App\Models\Product;
use Filament\Notifications\Notification;

class EditSale extends EditRecord
{
    protected static string $resource = SaleResource::class;

    protected function afterSave(): void
    {
        $sale = $this->record;
        
        if ($sale->status === 'completed' && !$sale->is_stock_deducted) {
            foreach ($sale->lines as $line) {
                $productId = $line->product_id;
                $qtyToDeduct = (float)$line->quantity;
                $locationId = $sale->storage_location_id;
                
                $balance = InventoryBalance::firstOrCreate(
                    [
                        'product_id' => $productId,
                        'storage_location_id' => $locationId
                    ],
                    ['quantity' => 0]
                );
                
                $balance->decrement('quantity', $qtyToDeduct);
                
                StockMovement::create([
                    'product_id' => $productId,
                    'storage_location_id' => $locationId,
                    'user_id' => auth()->id(),
                    'type' => 'out',
                    'quantity' => $qtyToDeduct,
                    'reference' => 'Sale: ' . $sale->reference,
                    'notes' => 'Sale completed',
                ]);
                
                // Sync master product quantity
                $product = Product::find($productId);
                if ($product) {
                    $product->syncQuantity();
                }
            }
            
            $sale->updateQuietly(['is_stock_deducted' => true]);
            
            Notification::make()
                ->title('Stock Deducted')
                ->success()
                ->send();
        }
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
