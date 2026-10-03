<?php

namespace App\Filament\Pages;

use App\Models\Setting;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Pages\Page;
use Filament\Actions\Action;
use Filament\Notifications\Notification;

class PublicPagesSettings extends Page implements HasForms
{
    use InteractsWithForms;

    protected static ?string $navigationIcon = 'heroicon-o-document-text';
    protected static ?string $navigationGroup = 'Settings';
    protected static ?int $navigationSort = 10;
    protected static ?string $title = 'Public Pages Content';

    protected static string $view = 'filament.pages.public-pages-settings';

    public static function canAccess(): bool
    {
        return auth()->user()->hasRole('super_admin');
    }

    public ?array $data = [];

    public function mount(): void
    {
        $this->form->fill([
            'about_title' => Setting::get('about_title', 'About Albareck'),
            'about_description' => Setting::get('about_description', 'We are a premier provider of high-quality manufactured goods and raw materials.'),
            'about_vision' => Setting::get('about_vision', 'To be the global standard in industrial supply and manufacturing quality.'),
            'about_mission' => Setting::get('about_mission', 'Providing robust, reliable, and sustainable products through advanced manufacturing processes.'),
            
            'contact_email' => Setting::get('contact_email', 'info@albareck.local'),
            'contact_phone' => Setting::get('contact_phone', '+1 (234) 567-8900'),
            'contact_address' => Setting::get('contact_address', "123 Industrial Parkway\nManufacturing District, TX 75001"),
        ]);
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('About Page')
                    ->schema([
                        TextInput::make('about_title')->required(),
                        Textarea::make('about_description')->rows(3)->required(),
                        Textarea::make('about_vision')->rows(2)->required(),
                        Textarea::make('about_mission')->rows(2)->required(),
                    ]),
                Section::make('Contact Page')
                    ->schema([
                        TextInput::make('contact_email')->email()->required(),
                        TextInput::make('contact_phone')->required(),
                        Textarea::make('contact_address')->rows(2)->required(),
                    ]),
            ])
            ->statePath('data');
    }

    protected function getFormActions(): array
    {
        return [
            Action::make('save')
                ->label('Save Changes')
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
            ->title('Settings Saved')
            ->success()
            ->send();
    }
}
