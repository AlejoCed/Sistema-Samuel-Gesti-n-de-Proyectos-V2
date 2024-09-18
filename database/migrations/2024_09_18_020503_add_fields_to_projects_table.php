<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('projects', function (Blueprint $table) {
            $table->json('report_files')->nullable();  // Campo para informes y reportes

            // Campos para Técnico Asignado y Coordinador del Proyecto
            $table->foreignId('technician_id')->nullable()->constrained('users')->nullOnDelete(); 
            $table->foreignId('coordinator_id')->nullable()->constrained('users')->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('projects', function (Blueprint $table) {
             // Eliminamos los campos si hacemos rollback
             $table->dropColumn('report_files');
             $table->dropForeign(['technician_id']);
             $table->dropColumn('technician_id');
             $table->dropForeign(['coordinator_id']);
             $table->dropColumn('coordinator_id');
        });
    }
};
