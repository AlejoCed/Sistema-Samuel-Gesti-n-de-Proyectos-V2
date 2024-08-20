<?php

namespace App\Filament\Widgets;

use App\Models\Customer;
use App\Models\Project;
use App\Models\User;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class StatsAdminOverview extends BaseWidget
{
    protected function getStats(): array
    {

        $completedProjects = Project::query()->where('status','terminado')->count();
        $inProgressProjects = Project::query()->where('status', 'en curso')->count();
        $notStartedProjects = Project::query()->where('status', 'no iniciado')->count();
        return [
            Stat::make('Usuarios en Sistema', User::query()->count())
            ->description('Total de usuarios registrados')
            ->descriptionIcon('heroicon-m-arrow-trending-up')
            ->color('success')
            ->chart([7, 2, 10, 3, 15, 4, 17]),
        Stat::make('Clientes', Customer::query()->count())
            ->description('Total de Clientes Registrados')
            ->descriptionIcon('heroicon-m-arrow-trending-up')
            ->color('success')
            ->chart([7, 2, 10, 3, 15, 4, 17]),
        Stat::make('Proyectos', Project::query()->count())
            ->description('Total de Proyectos')
            ->description("Completados: $completedProjects | En Curso: $inProgressProjects | No Iniciados: $notStartedProjects")
            // ->descriptionIcon('heroicon-m-arrow-trending-up')
            ->color('success'),
        ];
    }
}
