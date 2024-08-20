<?php

namespace App\Exports;

use App\Models\Project;
use Barryvdh\DomPDF\Facade as PDF;
use Barryvdh\DomPDF\Facade\Pdf as FacadePdf;
use Maatwebsite\Excel\Concerns\FromCollection;

class ProjectsExport implements FromCollection
{
    /**
    * @return \Illuminate\Support\Collection
    */
    public function collection()
    {
        return Project::all();
    }

    public function headings(): array
    {
        return [
            'ID', 'Nombre', 'Cliente', 'Estado', 'Presupuesto', 'Archivos de Cotización', 'Archivos de Planos', 'Notas', 'Creado en', 'Actualizado en'
        ];
    }

    public function downloadPDF()
    {
        $projects = $this->collection();

        $pdf = FacadePdf::loadView('reports.projects_pdf', ['projects' => $projects]);

        return $pdf->download('projects_report.pdf');
    }
}
