<?php

namespace App\Filament\Resources\SaleResource\Pages;

use App\Filament\Resources\SaleResource;
use App\Services\SaleService;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Filament\Notifications\Notification;

class EditSale extends EditRecord
{
    protected static string $resource = SaleResource::class;

    // ─────────────────────────────────────────────────────────────────
    //  Backend Authorization & Data Mutation
    // ─────────────────────────────────────────────────────────────────

    /**
     * Called before Filament saves the record.
     * Re-enforces: user_id = authenticated user, branch authorization.
     * Does NOT re-process stock if it was already deducted.
     */
    protected function mutateFormDataBeforeSave(array $data): array
    {
        $user    = auth()->user();
        $service = app(SaleService::class);

        // 1. Force user_id to the authenticated user — ignore submitted value.
        $data['user_id'] = $user->id;

        // 2. Resolve & validate branch.
        $branch = $service->resolveAuthorizedBranch($user, $data['branch_id'] ?? null);
        $data['branch_id'] = $branch->id;

        // 3. Resolve storage location within the validated branch.
        $location = $service->resolveStorageLocation($branch, $data['storage_location_id'] ?? null);
        $data['storage_location_id'] = $location->id;

        return $data;
    }

    /**
     * After saving: if the order transitions to "confirmed" and stock hasn't
     * been deducted yet, run the atomic deduction.
     */
    protected function afterSave(): void
    {
        $sale    = $this->record->fresh(['lines', 'branch', 'storageLocation']);
        $user    = auth()->user();
        $service = app(SaleService::class);

        if ($sale->status === 'confirmed' && !$sale->is_stock_deducted) {
            try {
                $service->confirmAndDeductStock($sale, $user);

                Notification::make()
                    ->title('Stock Deducted')
                    ->body("Inventory updated for sale {$sale->reference}.")
                    ->success()
                    ->send();
            } catch (\RuntimeException $e) {
                Notification::make()
                    ->title('Stock Deduction Failed')
                    ->body($e->getMessage())
                    ->danger()
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
