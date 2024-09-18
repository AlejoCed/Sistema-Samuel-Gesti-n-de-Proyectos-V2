<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('project_technician', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->constrained('projects')->onDelete('cascade');
            $table->foreignId('technician_id')->constrained('users')->onDelete('cascade'); // Suponiendo que 'users' es la tabla de técnicos
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('project_technician');
    }
};

