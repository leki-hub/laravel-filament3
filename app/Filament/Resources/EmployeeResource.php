<?php

namespace App\Filament\Resources;

use App\Filament\Resources\CountryResource\RelationManagers\EmployeesRelationManager;
use Filament\Forms;
use App\Models\City;
use Filament\Tables;
use App\Models\State;
use Filament\Forms\Get;
use Filament\Forms\Set;

use App\Models\Employee;

use Filament\Forms\Form;
use Filament\Tables\Table;
use Filament\Resources\Resource;
use Illuminate\Support\Collection;
use Filament\Tables\Filters\SelectFilter;
use Illuminate\Database\Eloquent\Builder;
use App\Filament\Resources\EmployeeResource\Pages;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use App\Filament\Resources\EmployeeResource\RelationManagers;
use Illuminate\Database\Eloquent\Model;

class EmployeeResource extends Resource
{
    protected static ?string $model = Employee::class;

    protected static ?string $navigationIcon = 'heroicon-o-user-group';
    protected static ?string $recordTitleAttribute = 'first_name';
    protected static ?string $navigationGroup= 'Employee Management';
// Below are a predefined methods/function for global search, can overwrite the above $recordtitleAttribute
    public static function getGlobalSearchResultsTitle(Model $record): string
    {
        return $record->last_name;
    }

    public static function getGloballySearchableAttributes(): array
    {
        return ['first_name', 'second_name', 'last_name'];
    }
//  navigation 
public static function getNavigationBadge():?string
{
    return static::getModel()::count();
}
    
public static function getNavigationBadgeColor(): string|array|null
{
    return static::getModel()::count()>5?'danger':'success';

}
    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                 Forms\Components\Section::make('Country Details')
                 ->description('Put the country details here.')
                 ->schema([
                     Forms\Components\Select::make('country_id')
                    ->relationship(name:'country',titleAttribute:'name')
                    ->searchable()
                     ->preload()
                     ->live()
                       ->afterStateUpdated(function (Set $set) {
                                $set('state_id', null);
                                $set('city_id', null);
                            })
                      ->required(),

                      Forms\Components\Select::make('state_id')
                          ->options(fn (Get $get): Collection => State::query()
                                ->where('country_id', $get('country_id'))
                                ->pluck('name', 'id'))
                      ->searchable()
                       ->preload()
                       ->live()
                       ->afterStateUpdated(fn (Set $set) => $set('city_id', null))
                        ->required(),

                        Forms\Components\Select::make('city_id')
                        ->options(function (Get $get): Collection {
                            $stateId = $get('state_id');
                            return City::query()
                                ->where('state_id', $stateId)
                                ->pluck('name', 'id');
                        })
                        ->searchable()
                        ->preload()
                        ->required(),


                          Forms\Components\Select::make('department_id')
                          ->relationship(name:'department',titleAttribute:'name')
                          ->searchable()
                           ->preload()
                            ->required(),



                 ])->columns(2)
                 ,


                 Forms\Components\Section::make('USER NAME')
                 ->description('Put the user name details here.')
                 ->schema([
            
                Forms\Components\TextInput::make('first_name')
                    ->required()
                    ->maxLength(255),
                Forms\Components\TextInput::make('last_name')
                    ->required()
                    ->maxLength(255),
                Forms\Components\TextInput::make('second_name')
                    ->required()
                    ->maxLength(255)
                 ])->columns(3),





                 Forms\Components\Section::make('ADDRESS DETAILS')
                 
                 ->schema([
                    Forms\Components\TextInput::make('address')
                    ->required()
                    ->maxLength(255),
                Forms\Components\TextInput::make('zip_code')
                    ->required()
                    ->maxLength(255),
                 ])->columns(2),
                 Forms\Components\Section::make('DATE OF BIRTH')
        
                 ->schema([
                    Forms\Components\DatePicker::make('date_of_birth')
                    ->native(false)
                    ->displayFormat('d/m/Y')
                    ->required(),
                Forms\Components\DatePicker::make('date_hired')
                    ->native(false)
                    ->required()
                 ])->columns(2)
            
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('country_id')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('state_id')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('city_id')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('department_id')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('first_name')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('second_name')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('last_name')
                    ->searchable(),
                Tables\Columns\TextColumn::make('address')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('zip_code')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('date_of_birth')
                    ->date()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('date_hired')
                    ->date()
                    ->sortable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('Department')
                ->relationship('department', 'name')
                ->searchable()
                ->preload()
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
          
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListEmployees::route('/'),
            'create' => Pages\CreateEmployee::route('/create'),
            'view' => Pages\ViewEmployee::route('/{record}'),
            'edit' => Pages\EditEmployee::route('/{record}/edit'),
        ];
    }
}
