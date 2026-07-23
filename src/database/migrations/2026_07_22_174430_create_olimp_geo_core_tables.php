<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('olimpiadas', function (Blueprint $table) {
            $table->id('id_olimpiada');
            $table->string('nombre');
            $table->unsignedSmallInteger('anio');
            $table->date('fecha_inicio')->nullable();
            $table->date('fecha_fin')->nullable();
            $table->date('fecha_inicio_inscripcion')->nullable();
            $table->date('fecha_fin_inscripcion')->nullable();
            $table->date('fecha_inicio_carga_evaluaciones')->nullable();
            $table->date('fecha_fin_carga_evaluaciones')->nullable();
            $table->string('estado', 40)->default('borrador');
            $table->boolean('activa')->default(false);
            $table->timestamps();

            $table->unique('anio');
            $table->index(['activa', 'estado']);
        });

        Schema::create('persona_tipos', function (Blueprint $table) {
            $table->id('id_persona_tipo');
            $table->string('codigo', 40)->unique();
            $table->string('nombre', 120);
            $table->text('descripcion')->nullable();
            $table->boolean('activo')->default(true);
            $table->timestamps();
        });

        Schema::create('personas', function (Blueprint $table) {
            $table->id('id_persona');
            $table->string('apellido', 120);
            $table->string('nombre', 120);
            $table->string('tipo_documento', 20)->nullable();
            $table->string('numero_documento', 40)->nullable();
            $table->string('email')->nullable();
            $table->string('telefono', 80)->nullable();
            $table->timestamps();

            $table->index(['tipo_documento', 'numero_documento']);
            $table->index('email');
        });

        Schema::create('escuela_user', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('id_escuela');
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->boolean('active')->default(true);
            $table->timestamps();

            $table->unique(['id_escuela', 'user_id']);
            $table->index('id_escuela');
        });

        Schema::create('inscripciones', function (Blueprint $table) {
            $table->id('id_inscripcion');
            $table->unsignedBigInteger('id_olimpiada');
            $table->unsignedBigInteger('id_escuela');
            $table->unsignedInteger('numero');
            $table->string('auditoria_origen')->nullable();
            $table->string('estado', 40)->default('borrador');
            $table->timestamps();

            $table->foreign('id_olimpiada')
                ->references('id_olimpiada')
                ->on('olimpiadas')
                ->cascadeOnDelete();

            $table->unique(['id_olimpiada', 'id_escuela']);
            $table->unique(['id_olimpiada', 'numero']);
            $table->index('id_escuela');
            $table->index('estado');
        });

        Schema::create('inscripcion_categorias', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('id_inscripcion');
            $table->char('categoria', 1);
            $table->boolean('activa')->default(true);
            $table->timestamps();

            $table->foreign('id_inscripcion')
                ->references('id_inscripcion')
                ->on('inscripciones')
                ->cascadeOnDelete();

            $table->unique(['id_inscripcion', 'categoria']);
            $table->index(['categoria', 'activa']);
        });

        Schema::create('inscripcion_personas', function (Blueprint $table) {
            $table->id('id_inscripcion_persona');
            $table->unsignedBigInteger('id_inscripcion');
            $table->unsignedBigInteger('id_persona');
            $table->unsignedBigInteger('id_persona_tipo');
            $table->char('categoria', 1)->nullable();
            $table->string('email_contacto')->nullable();
            $table->text('observaciones')->nullable();
            $table->boolean('activo')->default(true);
            $table->timestamps();

            $table->foreign('id_inscripcion')
                ->references('id_inscripcion')
                ->on('inscripciones')
                ->cascadeOnDelete();

            $table->foreign('id_persona')
                ->references('id_persona')
                ->on('personas')
                ->cascadeOnDelete();

            $table->foreign('id_persona_tipo')
                ->references('id_persona_tipo')
                ->on('persona_tipos')
                ->restrictOnDelete();

            $table->unique([
                'id_inscripcion',
                'id_persona',
                'id_persona_tipo',
                'categoria',
            ], 'insc_pers_tipo_cat_unique');

            $table->index(['id_inscripcion', 'categoria']);
            $table->index(['id_persona_tipo', 'categoria']);
        });

        Schema::create('evaluaciones', function (Blueprint $table) {
            $table->id('id_evaluacion');
            $table->unsignedBigInteger('id_inscripcion_persona');
            $table->string('estado', 40)->default('pendiente');
            $table->timestamps();

            $table->foreign('id_inscripcion_persona')
                ->references('id_inscripcion_persona')
                ->on('inscripcion_personas')
                ->cascadeOnDelete();

            $table->unique('id_inscripcion_persona');
            $table->index('estado');
        });

        Schema::create('evaluacion_archivos', function (Blueprint $table) {
            $table->id('id_archivo');
            $table->unsignedBigInteger('id_evaluacion');
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('nombre_original');
            $table->string('nombre_interno');
            $table->string('ruta');
            $table->string('mime', 120);
            $table->unsignedBigInteger('tamano');
            $table->string('sha256', 64);
            $table->unsignedInteger('version');
            $table->boolean('vigente')->default(true);
            $table->text('observacion')->nullable();
            $table->timestamps();

            $table->foreign('id_evaluacion')
                ->references('id_evaluacion')
                ->on('evaluaciones')
                ->cascadeOnDelete();

            $table->unique(['id_evaluacion', 'version']);
            $table->index(['id_evaluacion', 'vigente']);
            $table->index('sha256');
        });

        DB::table('persona_tipos')->insert([
            [
                'codigo' => 'director',
                'nombre' => 'Director',
                'descripcion' => 'Director o directivo de la institución.',
                'activo' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'codigo' => 'docente',
                'nombre' => 'Docente',
                'descripcion' => 'Docente tutor o referente institucional.',
                'activo' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'codigo' => 'responsable',
                'nombre' => 'Responsable',
                'descripcion' => 'Responsable de categoría o responsable operativo de inscripción.',
                'activo' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'codigo' => 'alumno',
                'nombre' => 'Alumno',
                'descripcion' => 'Alumno participante de la Olimpiada.',
                'activo' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);

        DB::table('olimpiadas')->insert([
            [
                'id_olimpiada' => 50,
                'nombre' => 'Olimpiada de Geografía 2026',
                'anio' => 2026,
                'estado' => 'inscripcion_abierta',
                'activa' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('evaluacion_archivos');
        Schema::dropIfExists('evaluaciones');
        Schema::dropIfExists('inscripcion_personas');
        Schema::dropIfExists('inscripcion_categorias');
        Schema::dropIfExists('inscripciones');
        Schema::dropIfExists('escuela_user');
        Schema::dropIfExists('personas');
        Schema::dropIfExists('persona_tipos');
        Schema::dropIfExists('olimpiadas');
    }
};