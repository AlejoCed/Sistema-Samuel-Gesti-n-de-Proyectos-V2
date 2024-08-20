<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ProjectResource\Pages;
use App\Filament\Resources\ProjectResource\RelationManagers;
use App\Models\Project;
use Filament\Forms;
use Filament\Forms\Form;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\ProjectsExport;
use Barryvdh\DomPDF\Facade as PDF;
use Barryvdh\DomPDF\Facade\Pdf as FacadePdf;
use Illuminate\Support\Facades\View;
use Illuminate\Contracts\Auth;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;


class ProjectResource extends Resource
{
    protected static ?string $model = Project::class;

    protected static ?string $navigationIcon = 'heroicon-o-briefcase';

    public static ?string  $navigationLabel = 'Proyectos';

    protected static ?string $label = 'Proyecto';

    protected static ?string $navigationGroup = 'Gestión';

    

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('nombre')
                    ->required()
                    ->maxLength(255),

                Forms\Components\Select::make('customer_id')
                    ->label('Cliente')
                    ->relationship('customer', 'name')
                    ->required(),

                Forms\Components\Select::make('status')
                    ->options([
                        'no iniciado' => 'No Iniciado',
                        'en curso' => 'En Curso',
                        'terminado' => 'Terminado',
                    ])
                    ->required(),

                Forms\Components\TextInput::make('budget')
                    ->numeric()
                    ->required(),
                    

                Forms\Components\FileUpload::make('quote_files')
                    ->multiple()
                    ->directory('uploads/quote_files')
                    ->preserveFilenames()
                    ->acceptedFileTypes(['application/pdf', 'image/*'])
                    ->openable()->uploadingMessage('Subiendo archivo de cotización...'),

                Forms\Components\FileUpload::make('plan_files')
                    ->multiple()
                    ->directory('uploads/plan_files')
                    ->preserveFilenames()
                    ->acceptedFileTypes(['application/pdf', 'image/*'])
                    ->openable()->uploadingMessage('Subiendo planos...') ,

                Forms\Components\Textarea::make('notes')
                    ->rows(3),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('nombre'),
                Tables\Columns\TextColumn::make('customer.name')->label('Cliente'),
                Tables\Columns\TextColumn::make('status')->label('Estado del Proyecto'),
                Tables\Columns\TextColumn::make('budget')->label('Presupuesto'),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->label('Creado en'),

                // Columna para mostrar archivos de cotización
                Tables\Columns\TextColumn::make('quote_files')
                    ->label('Archivos de Cotización')
                    ->formatStateUsing(function ($state) {
                        if ($state) {
                            return collect(json_decode($state))->map(function ($file) {
                                return "<a href='" . asset('storage/' . $file) . "' target='_blank'>" . basename($file) . "</a>";
                            })->implode('<br>');
                        }
                        return 'Ninguno';
                    })
                    ->html()
                    ,

                // Columna para mostrar archivos de planos
                Tables\Columns\TextColumn::make('plan_files')
                    ->label('Archivos de Planos')
                    ->formatStateUsing(function ($state) {
                        if ($state) {
                            return collect(json_decode($state))->map(function ($file) {
                                return "<a href='" . asset('storage/' . $file) . "' target='_blank'>" . basename($file) . "</a>";
                            })->implode('<br>');
                        }
                        return 'Ninguno';
                    })
                    ->html(),
            ])
            ->filters([
                //
            ])
            ->actions([
                
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make()->visible(fn (Project $record) => auth()->user()->can('edit projects')),
                Tables\Actions\DeleteAction::make()->visible(fn (Project $record) => auth()->user()->can('delete projects')),
                Tables\Actions\Action::make('export')
                    ->label('Exportar a Excel')
                    ->icon('heroicon-o-arrow-down-tray')
                    ->action(function () {
                        return Excel::download(new ProjectsExport, 'projects.xlsx');
                    }),
                // Tables\Actions\Action::make('exportPdf')
                //     ->label('Exportar a PDF')
                //     ->icon('heroicon-o-arrow-down-tray')
                //     ->action(function (Project $record) {
                //         $html = self::generateProjectPDF($record);
                //         $pdf = FacadePdf::loadHTML($html);
                //         return $pdf->download("project_{$record->id}.pdf");
                //     }),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                    Tables\Actions\Action::make('export')
                    ->label('Exportar a Excel')
                    ->icon('heroicon-o-arrow-down-tray')
                    ->action(function () {
                        return Excel::download(new ProjectsExport, 'projects.xlsx');
                    }),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    protected function generateProjectPDF(Project $project)
    {
        // Aquí puedes generar el contenido HTML del PDF directamente en el Resource
        $html = '
            <h1>Detalles del Proyecto</h1>
            <p>Nombre: ' . $project->nombre . '</p>
            <p>Status: ' . $project->status . '</p>
            <p>Presupuesto: ' . $project->budget . '</p>
            <!-- Agrega más campos según sea necesario -->
        ';

        return $html;
    }


    public static function getPages(): array
    {
        return [
            'index' => Pages\ListProjects::route('/'),
            'create' => Pages\CreateProject::route('/create'),
            'view' => Pages\ViewProject::route('/{record}'),
            'edit' => Pages\EditProject::route('/{record}/edit'),
        ];
    }
}
