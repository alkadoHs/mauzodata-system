<?php

namespace App\Filament\Pages\Tenancy;
 
use App\Models\Team;
use Filament\Forms\Components\Section;
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
                    ])
            ]);
    }
 
    protected function handleRegistration(array $data): Team
    {
        $team = Team::create($data);
 
        $team->users()->attach(auth()->user());
 
        return $team;
    }
}