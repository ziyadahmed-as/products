<?php

namespace App\Filament\Resources\SaleResource\Pages;

use App\Filament\Resources\SaleResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateSale extends CreateRecord
{
    protected static string $resource = SaleResource::class;

    protected array $paymentData = [];

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        if (!empty($data['new_payment_amount']) && $data['new_payment_amount'] > 0) {
            $this->paymentData = [
                'amount'    => $data['new_payment_amount'],
                'method'    => $data['new_payment_method'] ?? 'cash',
                'reference' => $data['new_payment_reference'] ?? null,
            ];
        }

        unset($data['new_payment_amount']);
        unset($data['new_payment_method']);
        unset($data['new_payment_reference']);

        return $data;
    }

    protected function afterCreate(): void
    {
        if (!empty($this->paymentData)) {
            $this->record->payments()->create([
                'amount'    => $this->paymentData['amount'],
                'method'    => $this->paymentData['method'],
                'reference' => $this->paymentData['reference'],
                'date'      => now(),
            ]);

            // Recalculate Sale payment status
            $totalPaid = $this->record->payments()->sum('amount');
            $this->record->paid_amount = $totalPaid;
            if ($totalPaid >= $this->record->total) {
                $this->record->payment_status = 'paid';
            } elseif ($totalPaid > 0) {
                $this->record->payment_status = 'partial';
            } else {
                $this->record->payment_status = 'pending';
            }
            $this->record->save();
        }
    }
}
