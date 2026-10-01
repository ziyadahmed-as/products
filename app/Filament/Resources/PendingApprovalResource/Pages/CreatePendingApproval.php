<?php

namespace App\Filament\Resources\PendingApprovalResource\Pages;

use App\Filament\Resources\PendingApprovalResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreatePendingApproval extends CreateRecord
{
    protected static string $resource = PendingApprovalResource::class;
}
