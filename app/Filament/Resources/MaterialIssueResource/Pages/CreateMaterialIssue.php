<?php

namespace App\Filament\Resources\MaterialIssueResource\Pages;

use App\Filament\Resources\MaterialIssueResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateMaterialIssue extends CreateRecord
{
    protected static string $resource = MaterialIssueResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
