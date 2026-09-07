<?php

namespace App\Console\Commands;

use App\Models\Escuela;
use App\Models\Inscripcion;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class ImportarUsuariosInscripcionesExcel extends Command
{
    protected $signature = 'olimp:importar-usuarios-inscripciones-excel
        {--dry-run : Simula la importación sin grabar cambios}
        {--password=olimp2026 : Clave inicial para los usuarios}
        {--olimpiada=50 : ID de la olimpiada}';

    protected $description = 'Importa escuelas, responsables e inscripciones desde la tabla staging inscripciones_excel';

    public function handle(): int
    {
        $dryRun = (bool) $this->option('dry-run');
        $passwordInicial = (string) $this->option('password');
        $idOlimpiada = (int) $this->option('olimpiada');

        $filas = DB::table('inscripciones_excel')
            ->select([
                'id',
                'email_tutor',
                'email_1',
                'email_2',
                'email_3',
                'nombre_escuela',
                'localidad',
                'provincia',
                'region',
                'subregion',
                'detalle',
                'cue',
                'anexo',
            ])
            ->orderBy('id')
            ->get();

        $totalFilas = 0;
        $escuelasCreadas = 0;
        $escuelasActualizadas = 0;
        $usuariosCreados = 0;
        $usuariosActualizados = 0;
        $relaciones = 0;
        $inscripciones = 0;
        $emailsOmitidos = 0;
        $escuelasSinEmail = 0;

        $this->info('Importando desde inscripciones_excel');
        $this->line('Modo: ' . ($dryRun ? 'SIMULACIÓN' : 'REAL'));
        $this->line('Olimpiada: ' . $idOlimpiada);
        $this->line('Password inicial: ' . $passwordInicial);
        $this->newLine();

        DB::beginTransaction();

        try {
            foreach ($filas as $fila) {
                $totalFilas++;

                $idEscuela = (int) $fila->id;
                $nombreEscuela = trim((string) ($fila->nombre_escuela ?? ''));

                if ($nombreEscuela === '') {
                    $nombreEscuela = 'Escuela #' . $idEscuela;
                }

                if (Escuela::where('id_escuela', $idEscuela)->exists()) {
                    $escuelasActualizadas++;
                    $this->line("ACTUALIZA escuela id {$idEscuela} - {$nombreEscuela}");
                } else {
                    $escuelasCreadas++;
                    $this->line("CREA escuela id {$idEscuela} - {$nombreEscuela}");
                }

                if (! $dryRun) {
                    Escuela::updateOrCreate(
                        [
                            'id_escuela' => $idEscuela,
                        ],
                        [
                            'nombre' => $nombreEscuela,
                            'cue' => $this->normalizarTexto($fila->cue ?? null),
                            'anexo' => $this->normalizarTexto($fila->anexo ?? null),
                            'localidad' => $this->normalizarTexto($fila->localidad ?? null),
                            'provincia' => $this->normalizarTexto($fila->provincia ?? null),
                            'region' => $this->normalizarTexto($fila->region ?? null),
                            'subregion' => $this->normalizarTexto($fila->subregion ?? null),
                            'detalle' => $this->normalizarTexto($fila->detalle ?? null),
                        ]
                    );
                }

                if (! $dryRun) {
                    Inscripcion::updateOrCreate(
                        [
                            'id_olimpiada' => $idOlimpiada,
                            'id_escuela' => $idEscuela,
                        ],
                        [
                            'numero' => $idEscuela,
                            'auditoria_origen' => 'inscripciones_excel',
                            'estado' => 'borrador',
                        ]
                    );
                }

                $inscripciones++;

                [$emails, $invalidos] = $this->emailsDeFila($fila);
                $emailsOmitidos += $invalidos;

                if ($emails->isEmpty()) {
                    $escuelasSinEmail++;
                    $this->warn("ESCUELA SIN EMAIL id={$idEscuela} - {$nombreEscuela}");
                    continue;
                }

                foreach ($emails as $email) {
                    $nombreUsuario = $this->nombreUsuario($fila, $email);

                    $user = User::where('email', $email)->first();

                    if ($user) {
                        $usuariosActualizados++;
                        $this->line("ACTUALIZA/VINCULA {$email} -> escuela id {$idEscuela}");

                        if (! $dryRun) {
                            $user->forceFill([
                                'name' => $user->name ?: $nombreUsuario,
                                'role' => 'responsible',
                                'active' => true,
                                'must_change_password' => false,
                            ])->save();
                        }
                    } else {
                        $usuariosCreados++;
                        $this->line("CREA usuario {$email} -> escuela id {$idEscuela}");

                        if (! $dryRun) {
                            $user = User::create([
                                'name' => $nombreUsuario,
                                'email' => $email,
                                'password' => Hash::make($passwordInicial),
                                'role' => 'responsible',
                                'active' => true,
                                'must_change_password' => false,
                            ]);
                        }
                    }

                    if (! $dryRun) {
                        DB::table('escuela_user')->updateOrInsert(
                            [
                                'id_escuela' => $idEscuela,
                                'user_id' => $user->id,
                            ],
                            [
                                'active' => true,
                                'created_at' => now(),
                                'updated_at' => now(),
                            ]
                        );
                    }

                    $relaciones++;
                }
            }

            if ($dryRun) {
                DB::rollBack();
            } else {
                DB::commit();
            }
        } catch (\Throwable $exception) {
            DB::rollBack();

            $this->error($exception->getMessage());

            return self::FAILURE;
        }

        $this->newLine();
        $this->info('Resumen');
        $this->table(
            ['Concepto', 'Cantidad'],
            [
                ['Filas/escuelas leídas', $totalFilas],
                ['Escuelas creadas', $escuelasCreadas],
                ['Escuelas actualizadas', $escuelasActualizadas],
                ['Usuarios creados', $usuariosCreados],
                ['Usuarios existentes actualizados/vinculados', $usuariosActualizados],
                ['Relaciones escuela_user', $relaciones],
                ['Inscripciones creadas/actualizadas', $inscripciones],
                ['Escuelas sin ningún email válido', $escuelasSinEmail],
                ['Emails omitidos por inválidos', $emailsOmitidos],
            ]
        );

        return self::SUCCESS;
    }

    private function emailsDeFila(object $fila): array
    {
        $validos = collect();
        $invalidos = 0;

        foreach ([
            $fila->email_tutor ?? null,
            $fila->email_1 ?? null,
            $fila->email_2 ?? null,
            $fila->email_3 ?? null,
        ] as $valor) {
            foreach ($this->separarEmails($valor) as $emailCrudo) {
                $email = $this->normalizarEmail($emailCrudo);

                if ($email) {
                    $validos->push($email);
                } else {
                    $invalidos++;
                }
            }
        }

        return [$validos->unique()->values(), $invalidos];
    }

    private function separarEmails(?string $valor): array
    {
        $valor = trim((string) $valor);

        if ($valor === '') {
            return [];
        }

        return preg_split('/[;,[:space:]]+/', $valor) ?: [];
    }

    private function normalizarEmail(?string $email): ?string
    {
        $email = Str::lower(trim((string) $email));

        if ($email === '') {
            return null;
        }

        if (! filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return null;
        }

        return $email;
    }

    private function normalizarTexto(?string $valor): ?string
    {
        $valor = trim((string) $valor);

        return $valor === '' ? null : $valor;
    }

    private function nombreUsuario(object $fila, string $email): string
    {
        $escuela = trim((string) ($fila->nombre_escuela ?? ''));

        if ($escuela !== '') {
            return $escuela;
        }

        return $email;
    }
}
