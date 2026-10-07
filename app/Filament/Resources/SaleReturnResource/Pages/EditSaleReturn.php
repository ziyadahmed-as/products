<?php

namespace App\Filament\Resources\SaleReturnResource\Pages;

use App\Filament\Resources\SaleReturnResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Filament\Notifications\Notification;

class EditSaleReturn extends EditRecord
{
    protected static string $resource = SaleReturnResource::class;

    protected function afterSave(): void
    {
        $return = $this->record->fresh();

        // Only process once when status becomes approved or completed
        if (
            in_array($return->status, ['approved', 'completed']) &&
            !$return->is_refund_processed &&
            $return->refund_amount > 0 &&
            $return->sale_id
        ) {
            $sale = $return->sale;

            if ($sale) {
                // Deduct refund from paid_amount
                $newPaidAmount = max(0, $sale->paid_amount - $return->refund_amount);
                
                // Update payment_status accordingly
                if ($newPaidAmount <= 0) {
                    $paymentStatus = 'pending';
                } elseif ($newPaidAmount < $sale->total) {
                    $paymentStatus = 'partial';
                } else {
                    $paymentStatus = 'paid';
                }

                // Also mark sale as refunded if full refund
                $saleStatus = $return->refund_amount >= $sale->total ? 'cancelled' : $sale->status;

                $sale->update([
                    'paid_amount'    => $newPaidAmount,
                    'payment_status' => $paymentStatus,
                    'status'         => $saleStatus,
                ]);

                // Mark this return as processed (prevents double deduction)
                $return->updateQuietly(['is_refund_processed' => true]);

                Notification::make()
                    ->title('Refund Applied')
                    ->body("Br " . number_format($return->refund_amount, 2) . " deducted from Order #{$sale->reference}. New balance: Br " . number_format($newPaidAmount, 2))
                    ->success()
                    ->send();
            }
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
