<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('inscripcion_personas', function (Blueprint $table) {
            if (! Schema::hasColumn('inscripcion_personas', 'evaluacion_bloqueada')) {
                $table->boolean('evaluacion_bloqueada')
                    ->default(false)
                    ->after('activo');
            }
        });
    }

    public function down(): void
    {
        Schema::table('inscripcion_personas', function (Blueprint $table) {
            if (Schema::hasColumn('inscripcion_personas', 'evaluacion_bloqueada')) {
                $table->dropColumn('evaluacion_bloqueada');
            }
        });
    }
};
