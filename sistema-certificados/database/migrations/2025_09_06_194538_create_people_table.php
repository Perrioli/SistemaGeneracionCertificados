<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
{
    Schema::create('persons', function (Blueprint $table) {
        $table->id();
        $table->string('dni');
        $table->string('apellido');
        $table->string('nombre');
        $table->string('titulo');
        $table->string('domicilio');
        $table->string('telefono');
        $table->string('email');
        $table->softDeletes();
        $table->timestamps();
    });
}

    public function down(): void
    {
        Schema::dropIfExists('persons');
    }
};
