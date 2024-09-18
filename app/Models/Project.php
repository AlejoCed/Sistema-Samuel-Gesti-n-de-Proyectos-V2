<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Project extends Model
{
    use HasFactory;

    protected $fillable = [
        'nombre',
        'customer_id',
        'status',
        'budget',
        'quote_files',
        'plan_files',
        'report_files',        // Añadimos el campo de informes y reportes
        'technician_id',       // Técnico asignado
        'coordinator_id', 
        'notes',
    ];

    protected $casts = [
        'quote_files' => 'array',
        'plan_files' => 'array',
        'report_files' => 'array',
        'budget' => 'decimal:2', // Si deseas mantener el formato decimal
    ];

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    // public function technician()
    // {
    //     return $this->belongsTo(User::class, 'technician_id');
    // }

    public function technicians()
    {
        return $this->belongsToMany(User::class, 'project_technician', 'project_id', 'technician_id');
    }
    // Relación con coordinador (usuario con rol de coordinador)
    public function coordinator()
    {
        return $this->belongsTo(User::class, 'coordinator_id');
    }
}
