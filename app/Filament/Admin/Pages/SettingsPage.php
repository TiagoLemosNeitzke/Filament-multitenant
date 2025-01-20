<?php

namespace App\Filament\Admin\Pages;

use App\Enums\{Permission};
use App\Filament\Admin\Clusters\ManagementCluster;
use App\Models\{Settings};
use Filament\Forms\Components\{Section};
use Filament\Forms\{Form};
use Filament\Notifications\Notification;
use Filament\Pages\{Page, SubNavigationPosition};
use Filament\{Forms};

class SettingsPage extends Page
{
    protected static string $view = 'filament.pages.settings-page';

    protected static ?string $cluster = ManagementCluster::class;

    protected static ?string $navigationIcon = 'heroicon-o-cog';

    protected static bool $isScopedToTenant = false;

    public Settings $settings;

    public static function canAccess(): bool
    {
        $user = auth_user();

        return $user->hasAbility(Permission::MASTER->value);
    }

    protected static ?int $navigationSort = 15;

    /** @var array<string, mixed> */
    public ?array $data = [];

    public function mount(): void
    {
        static::$subNavigationPosition = auth_user()->navigation_mode ? SubNavigationPosition::Start : SubNavigationPosition::Top;
        $this->settings                = Settings::query()->first();
        $this->form->fill($this->settings->toArray()); //@phpstan-ignore-line
    }

    public static function getNavigationLabel(): string
    {
        return __('Settings');
    }

    public function getTitle(): string
    {
        return __('Settings');
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('Dados')->schema([
                    Forms\Components\TextInput::make('name')
                        ->label('Name')
                        ->maxLength(50)
                        ->required(),
                    Forms\Components\TextInput::make('cnpj')
                        ->label('CNPJ')
                        ->mask('99.999.999/9999-99')
                        ->length(18)
                        ->validationAttribute('CNPJ'),
                    Forms\Components\DatePicker::make('opened_in')
                        ->label('Opened in'),
                    Forms\Components\TextInput::make('email')
                        ->label('Email')
                        ->email()
                        ->maxLength(255)
                        ->prefixIcon('heroicon-m-envelope'),
                    Forms\Components\FileUpload::make('logo')
                        ->image()
                        ->directory('settings'),
                    Forms\Components\FileUpload::make('favicon')
                        ->image()
                        ->directory('settings'),
                    Forms\Components\MarkdownEditor::make('about')
                        ->label('About')
                        ->disableToolbarButtons([
                            'attachFiles',
                        ])
                        ->columnSpanFull(),
                ])->columns(3),
            ])
            ->statePath('data');
    }

    public function save(): void
    {
        $data = $this->form->getState(); //@phpstan-ignore-line

        $this->settings->fill($data);
        $this->settings->save();

        Notification::make()->body(__('Settings updated successfully'))->icon('heroicon-o-check-circle')->iconColor('success')->send();
    }
}
