<?php

namespace App\Filament\Pages\Tenancy;
 
use App\Models\Team;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Pages\Tenancy\RegisterTenant;
 
class RegisterTeam extends RegisterTenant
{
    public static function getLabel(): string
    {
        return 'Register branch';
    }
 
    public function form(Form $form): Form
    {
        return $form
            ->schema([
                    TextInput::make('name')
                        ->maxLength(40)
                        ->required(),
                    Select::make('categoty')
                        ->options([
                            'shop' => 'Shop',
                            'store' => 'Store',
                            'vendors' => 'Vendors Branch',
                        ])
                        ->default('shop')
                        ->required()
                        ->disablePlaceholderSelection()
                        ->native(false),
                    TextInput::make('phone')
                        ->numeric()
                        ->maxLength(15)
                        ->required(),
                    TextInput::make('address')
                        ->required()
                        ->maxLength(100),
                    FileUpload::make('logo_url')
                        ->label('Branch Logo')
                        ->imageEditor()
                        ->imageResizeMode('cover')
                        ->imageEditorAspectRatios(['1:1', '4:3'])
                        ->imagePreviewHeight('250')
                        ->maxSize(2000)
            ]);
    }
 
    protected function handleRegistration(array $data): Team
    {
        $team = Team::create($data);
 
        $team->users()->attach(auth()->user());
 
        return $team;
    }
}