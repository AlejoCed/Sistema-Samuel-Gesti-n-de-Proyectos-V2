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
        'notes',
    ];

    protected $casts = [
        'quote_files' => 'array',
        'plan_files' => 'array',
        'budget' => 'decimal:2', // Si deseas mantener el formato decimal
    ];

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }
}
