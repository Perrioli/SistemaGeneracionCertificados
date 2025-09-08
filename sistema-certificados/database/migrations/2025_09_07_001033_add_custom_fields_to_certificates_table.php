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
        Schema::table('certificates', function (Blueprint $table) {
            $table->string('codigo_incremental')->nullable()->after('nota');
            $table->year('anio')->nullable()->after('codigo_incremental');
            $table->string('tipo_certificado')->nullable()->after('anio');
            $table->string('iniciales')->nullable()->after('tipo_certificado');
            $table->string('tres_ultimos_digitos_dni')->nullable()->after('iniciales');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('certificates', function (Blueprint $table) {
            //
        });
    }
};
