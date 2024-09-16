<?php

namespace App\Filament\Resources;

use App\Filament\Resources\CustomerResource\Pages;
use App\Filament\Resources\CustomerResource\RelationManagers;
use App\Models\Customer;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Filament\Forms\Components\FileUpload;

class CustomerResource extends Resource
{
    protected static ?string $model = Customer::class;

    protected static ?string $navigationIcon = 'heroicon-o-user-plus';

    public static ?string  $navigationLabel = 'Clientes';

    protected static ?string $label = 'Clientes';

    protected static ?string $navigationGroup = 'Gestión';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('name')->required()->label('nombre'),
                Forms\Components\TextInput::make('email')->email()->required()->label('correo')
                ->unique(function (Builder $query, string $attribute, mixed $requestValue): Builder {
                    return $query->where('email', $requestValue);
                }),
                Forms\Components\FileUpload::make('image')->required()
                ->openable()->image()   
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')->searchable()->label('Nombre'),
            Tables\Columns\TextColumn::make('email')->searchable()->label('Correo'),
            Tables\Columns\ImageColumn::make('image')->label('Imagen'),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make()->label('Editar'),
                Tables\Actions\DeleteAction::make()->label('Borrar'),Tables\Actions\Action::make('sendEmail')
                ->label('Enviar Correo')
                ->icon('heroicon-o-envelope')
                ->url(fn ($record) => 'mailto:' . $record->email) // El enlace mailto
                ->openUrlInNewTab(),
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
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListCustomers::route('/'),
            'create' => Pages\CreateCustomer::route('/create'),
            'edit' => Pages\EditCustomer::route('/{record}/edit'),
        ];
    }
}
