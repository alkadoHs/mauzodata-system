<?php

namespace App\Filament\Pages\Tenancy;
 
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Pages\Tenancy\EditTenantProfile;
 
class EditTeamProfile extends EditTenantProfile
{
    public static function getLabel(): string
    {
        return 'Company profile';
    }
 
    public function form(Form $form): Form
    {
        return $form
             ->schema([
                Section::make()
                    ->schema([
                        TextInput::make('name')
                            ->maxLength(40)
                            ->required(),
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
                        ->minSize(10)
                        ->maxSize(500)
                    ])
            ]);
    }
}