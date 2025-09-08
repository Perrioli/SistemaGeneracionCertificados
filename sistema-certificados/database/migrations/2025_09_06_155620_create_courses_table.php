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
        Schema::create('courses', function (Blueprint $table) {
            $table->id();
            $table->string('area');
            $table->string('nro_curso');
            $table->string('nombre');
            $table->string('periodo');
            $table->integer('horas');
            $table->enum('tipo_horas', ['Reloj', 'Cátedra']);

            $table->foreignId('resolution_id')->constrained('resolutions');

            $table->text('objetivo')->nullable();
            $table->text('contenido')->nullable();
            $table->integer('maxima_nota');

            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('courses');
    }
};
