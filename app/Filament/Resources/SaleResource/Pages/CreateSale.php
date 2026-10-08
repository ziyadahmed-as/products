<?php

namespace App\Filament\Resources\SaleResource\Pages;

use App\Filament\Resources\SaleResource;
use App\Services\SaleService;
use Filament\Resources\Pages\CreateRecord;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\DB;

class CreateSale extends CreateRecord
{
    protected static string $resource = SaleResource::class;

    // ─────────────────────────────────────────────────────────────────
    //  Backend Authorization & Data Mutation
    // ─────────────────────────────────────────────────────────────────

    /**
     * Called before Filament writes the record.
     * Enforces: user_id = authenticated user (never from form), branch authorization.
     */
    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $user    = auth()->user();
        $service = app(SaleService::class);

        // 1. Force user_id to the authenticated user — ignore any submitted value.
        $data['user_id'] = $user->id;

        // 2. Resolve & validate the branch (throws on violation).
        $branch = $service->resolveAuthorizedBranch($user, $data['branch_id'] ?? null);
        $data['branch_id'] = $branch->id;

        // 3. Resolve storage location within the validated branch.
        $location = $service->resolveStorageLocation($branch, $data['storage_location_id'] ?? null);
        $data['storage_location_id'] = $location->id;

        return $data;
    }

    /**
     * After the Sale and its lines are persisted, deduct stock atomically.
     * Stock deduction only occurs for "confirmed" status orders.
     */
    protected function afterCreate(): void
    {
        $sale    = $this->record;
        $user    = auth()->user();
        $service = app(SaleService::class);

        // Only auto-deduct if the order was created with "confirmed" status.
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

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
