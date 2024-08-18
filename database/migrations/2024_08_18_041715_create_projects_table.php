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
        Schema::create('projects', function (Blueprint $table) {
            $table->id();
            $table->string('nombre')->nullable();
            $table->foreignId('customer_id')->constrained('customers')->onDelete('cascade');
            $table->enum('status', ['no iniciado', 'en curso', 'terminado']);
            $table->decimal('budget', 15, 2)->nullable();
            $table->json('quote_files')->nullable();  // Para múltiples archivos de cotización
            $table->json('plan_files')->nullable();   // Para múltiples archivos de planos
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('projects');
    }
};
