<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ProjectResource\Pages;
use App\Filament\Resources\ProjectResource\RelationManagers;
use App\Models\Project;
use App\Models\User;
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
use Filament\Forms\Components\MultiSelect;
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

    public static function getEloquentQuery(): Builder
    {
        $user = auth()->user();

        // Si el usuario tiene el rol de cliente
        if ($user->hasRole('Cliente')) {
            return parent::getEloquentQuery()
                ->whereHas('Customer', function ($query) use ($user) {
                    // Filtra los proyectos cuyo cliente coincida con el nombre del usuario
                    $query->where('name', $user->name);
                });
        }

        if ($user->hasRole('Técnico')) {
            return parent::getEloquentQuery()->where('technician_id', $user->id);
        }

        // Si el usuario tiene el rol de "coordinador", filtrar los proyectos donde está asignado como coordinador.
        if ($user->hasRole('Coordinador')) {
            return parent::getEloquentQuery()->where('coordinator_id', $user->id);
        }

        // Si no es un cliente, técnico o coordinador, retorna todos los proyectos
        return parent::getEloquentQuery();
    }

    



    

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('nombre')
                    ->required()
                    ->maxLength(255)
                    ->disabled(fn() => in_array(auth()->user()->getRoleNames()->first(), ['Cliente', 'Técnico', 'Coordinador']))
                    ,

                Forms\Components\Select::make('customer_id')
                    ->label('Cliente')
                    ->relationship('customer', 'name')
                    ->required()
                    ->disabled(fn() => in_array(auth()->user()->getRoleNames()->first(), ['Cliente', 'Técnico', 'Coordinador']))
                    ,

                Forms\Components\Select::make('status')
                    ->label('Estado del Proyecto')
                    ->options([
                        'no iniciado' => 'No Iniciado',
                        'en curso' => 'En Curso',
                        'terminado' => 'Terminado',
                    ])
                    ->required()
                    ->disabled(fn() => in_array(auth()->user()->getRoleNames()->first(), ['Cliente', 'Técnico', 'Coordinador']))
                    ,

                Forms\Components\TextInput::make('budget')
                    ->label('Presupuesto')
                    ->numeric()
                    ->required()
                    ->minValue(1)
                    ->visible(fn (Project $record) => auth()->user()->can('view budget'))

                    ->disabled(fn() => in_array(auth()->user()->getRoleNames()->first(), ['Cliente', 'Técnico', 'Coordinador']))
                    ,
                    

                Forms\Components\FileUpload::make('quote_files')
                    ->label('Archivos de Cotización')
                    ->multiple()
                    ->directory('uploads/quote_files')
                    ->preserveFilenames()
                    ->acceptedFileTypes(['application/pdf', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet', // .xlsx
                    'application/vnd.ms-excel', ])
                    ->openable()->uploadingMessage('Subiendo archivo de cotización...')
                    ->disabled(fn() => auth()->user()->hasRole('Cliente'))
                    ->visible(fn (Project $record) => auth()->user()->can('view quotes'))
                    ,

                Forms\Components\FileUpload::make('plan_files')
                    ->label('Archivos de Planos')
                    ->multiple()
                    ->directory('uploads/plan_files')
                    ->preserveFilenames()
                    ->acceptedFileTypes(['application/pdf', 'image/*', 'application/acad', // .dwg
                    'application/dxf', // .dxf
                    'image/vnd.dwg', // otra variante para .dwg
                    'application/x-dwg'])
                    ->openable()->uploadingMessage('Subiendo planos...')
                    ->disabled(fn() => auth()->user()->hasRole('Técnico'))
                     ,
// Campo para "Informes y Reportes"
                    Forms\Components\FileUpload::make('report_files')
                    ->multiple()
                    ->directory('uploads/reports')
                    ->preserveFilenames()
                    ->acceptedFileTypes(['application/pdf'])
                    ->label('Informes y Reportes'),

                    // Campo "Técnico Asignado" (solo usuarios con rol de técnico)
                    // Forms\Components\Select::make('technician_id')
                    // ->label('Técnico Asignado')
                    // ->relationship('technician', 'name')
                    // ->options(
                    //     User::role('Técnico')->pluck('name', 'id') // Solo usuarios con el rol de técnico
                    // )
                    // ->required()

                    Forms\Components\MultiSelect::make('technicians')
                    ->relationship('technicians', 'name')
                    ->label('Técnicos Asignados')
                    ->options(function () {
                        return User::whereHas('roles', function ($query) {
                            $query->where('name', 'Técnico');
                        })->pluck('name', 'id')->toArray();
                    })
                    ->label('Seleccionar Técnicos')
                    
                    ,

                    // Campo "Coordinador de Proyecto" (solo usuarios con rol de coordinador)
                    Forms\Components\Select::make('coordinator_id')
                    ->label('Coordinador del Proyecto')
                    ->relationship('coordinator', 'name')
                    ->options(
                        User::role('Coordinador')->pluck('name', 'id') // Solo usuarios con el rol de coordinador
                    )
                    ->required(),
                Forms\Components\Textarea::make('notes')
                    ->label('Notas')
                    ->rows(3)
                    ->disabled(fn() => auth()->user()->hasRole('Cliente'))
                    ->visible(fn (Project $record) => auth()->user()->can('view minutes')),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table

        // ->query(function (Builder $query) {
        //     $user = auth()->user();

        //     if ($user->hasRole('cliente')) {
        //         return $query->where('customer_id', $user->customer->id);
        //     }

        //     if ($user->hasRole('técnico')) {
        //         return $query->whereHas('technicians', function ($query) use ($user) {
        //             $query->where('id', $user->id);
        //         });
        //     }

        //     if ($user->hasRole('coordinador')) {
        //         return $query->where('assigned_coordinator', $user->id);
        //     }

        //     return $query;
        // })
            ->columns([
                Tables\Columns\TextColumn::make('nombre'),
                Tables\Columns\TextColumn::make('customer.name')->label('Cliente'),
                Tables\Columns\TextColumn::make('status')->label('Estado del Proyecto'),
                Tables\Columns\TextColumn::make('budget')->label('Presupuesto'),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->label('Creado en')
                    ->toggleable(isToggledHiddenByDefault: true),

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
                    Tables\Columns\TextColumn::make('technicians.name')
                    ->label('Técnicos Asignados')
                    ->formatStateUsing(function ($state, $record) {
                        // Accedemos a la relación "technicians" desde el proyecto (record)
                        $technicians = $record->technicians;

                        if ($technicians->isNotEmpty()) {
                            return $technicians->pluck('name')->implode(', ');
                        }

                        return 'Sin técnicos asignados';
                    }),
                Tables\Columns\TextColumn::make('coordinator.name')->label('Coordinador del Proyecto'),
            ])
            ->filters([
                //
            ])
            ->actions([
                
                Tables\Actions\ViewAction::make()->label('Ver'),
                Tables\Actions\EditAction::make()->label('Editar'),
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
