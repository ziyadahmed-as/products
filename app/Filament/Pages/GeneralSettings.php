<?php

namespace App\Filament\Pages;

use App\Models\Setting;
use Filament\Actions\Action;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Pages\Page;

class GeneralSettings extends Page implements HasForms
{
    use InteractsWithForms;

    protected static ?string $navigationIcon = 'heroicon-o-cog-6-tooth';
    protected static ?string $navigationGroup = 'Settings';
    protected static ?int $navigationSort = 5;
    protected static ?string $title = 'General & Logo Settings';

    protected static string $view = 'filament.pages.general-settings';

    public static function canAccess(): bool
    {
        return auth()->check() && auth()->user()->hasAnyRole(['Super Admin', 'Admin']);
    }

    public ?array $data = [];

    public function mount(): void
    {
        $this->form->fill([
            'site_name'        => Setting::get('site_name', config('app.name', 'Albareck')),
            'site_tagline'     => Setting::get('site_tagline', 'Integrated Stock & Manufacturing Management System'),
            'site_logo'        => Setting::get('site_logo'),
            'contact_email'    => Setting::get('contact_email', 'info@albareck.com'),
            'contact_phone'    => Setting::get('contact_phone', '+251 900 000 000'),
            'contact_address'  => Setting::get('contact_address', 'Addis Ababa, Ethiopia'),
        ]);
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('Brand & Logo')
                    ->description('Manage application logo and branding displayed across all portals')
                    ->schema([
                        FileUpload::make('site_logo')
                            ->label('Application Logo')
                            ->image()
                            ->disk('public')
                            ->directory('settings')
                            ->maxSize(4096)
                            ->helperText('Recommended: High-resolution PNG or JPG image. Max 4MB.'),
                        TextInput::make('site_name')
                            ->label('Company / Site Name')
                            ->required()
                            ->maxLength(100),
                        TextInput::make('site_tagline')
                            ->label('Tagline / Subtitle')
                            ->maxLength(255),
                    ]),

                Section::make('Contact Information')
                    ->description('Official contact details for invoices and customer communication')
                    ->schema([
                        TextInput::make('contact_email')
                            ->label('Email Address')
                            ->email(),
                        TextInput::make('contact_phone')
                            ->label('Phone Number'),
                        TextInput::make('contact_address')
                            ->label('Physical Address'),
                    ]),
            ])
            ->statePath('data');
    }

    protected function getFormActions(): array
    {
        return [
            Action::make('save')
                ->label('Save Settings')
                ->submit('save'),
        ];
    }

    public function save(): void
    {
        $state = $this->form->getState();

        foreach ($state as $key => $value) {
            Setting::set($key, $value);
        }

        Notification::make()
            ->title('Settings Saved Successfully')
            ->success()
            ->send();
    }
}
