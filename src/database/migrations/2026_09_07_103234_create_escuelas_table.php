<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('escuelas', function (Blueprint $table) {
            $table->unsignedBigInteger('id_escuela')->primary();

            $table->string('nombre');
            $table->string('cue')->nullable();
            $table->string('anexo')->nullable();

            $table->string('localidad')->nullable();
            $table->string('provincia')->nullable();
            $table->string('region')->nullable();
            $table->string('subregion')->nullable();
            $table->text('detalle')->nullable();

            $table->timestamps();

            $table->index('cue');
            $table->index('nombre');
            $table->index('localidad');
            $table->index('provincia');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('escuelas');
    }
};
