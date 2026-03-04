<?php
// app/Services/BackupService.php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Artisan;
use App\Repositories\Eloquent\RespaldoRepository; // ← Cambiar a implementación concreta
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use ZipArchive;

class BackupService
{
    protected $respaldoRepository;

    public function __construct(RespaldoRepository $respaldoRepository) // ← Cambiar el tipo
    {
        $this->respaldoRepository = $respaldoRepository;
    }

    /**
     * Generar un respaldo completo de la base de datos
     */
    public function generarRespaldoCompleto(User $usuario, $observaciones = null)
    {
        try {
            $fecha = Carbon::now()->format('Y-m-d_H-i-s');
            $nombre = "respaldo_completo_{$fecha}";
            $archivo = "respaldos/{$nombre}.sql";
            
            // Obtener configuración
            $config = $this->respaldoRepository->getConfiguracion();
            
            // Ruta completa del archivo
            $rutaCompleta = storage_path("app/{$archivo}");
            
            // Crear directorio si no existe
            $directorio = storage_path('app/respaldos');
            if (!file_exists($directorio)) {
                mkdir($directorio, 0755, true);
            }
            
            // Ruta de mysqldump en XAMPP
            $rutaMysqldump = 'C:\xampp\mysql\bin\mysqldump.exe';
            
            // Verificar que mysqldump existe
            if (!file_exists($rutaMysqldump)) {
                throw new \Exception("No se encontró mysqldump en: {$rutaMysqldump}");
            }
            
            // Obtener credenciales de la BD
            $host = config('database.connections.mysql.host');
            $puerto = config('database.connections.mysql.port');
            $base = config('database.connections.mysql.database');
            $usuarioBD = config('database.connections.mysql.username');
            $password = config('database.connections.mysql.password');
            
            // Comando con la ruta completa de mysqldump
            if ($password) {
                $comando = "\"{$rutaMysqldump}\" --host={$host} --port={$puerto} --user={$usuarioBD} --password='{$password}' {$base} > \"{$rutaCompleta}\" 2>&1";
            } else {
                $comando = "\"{$rutaMysqldump}\" --host={$host} --port={$puerto} --user={$usuarioBD} {$base} > \"{$rutaCompleta}\" 2>&1";
            }
            
            // Ejecutar comando
            $output = [];
            $returnVar = 0;
            exec($comando, $output, $returnVar);
            
            // Guardar log para depuración
            Log::info('Comando ejecutado: ' . $comando);
            Log::info('Return code: ' . $returnVar);
            Log::info('Output: ' . implode("\n", $output));
            
            // Verificar si el archivo se creó
            if (!file_exists($rutaCompleta)) {
                throw new \Exception('No se pudo generar el archivo de respaldo');
            }
            
            $tamano = filesize($rutaCompleta);
            if ($tamano === 0) {
                throw new \Exception('El archivo de respaldo está vacío. Error: ' . implode("\n", $output));
            }
            
            // Si incluye archivos, crear ZIP
            $archivoFinal = $archivo;
            if ($config && $config->incluir_archivos) {
                $rutaZip = $this->crearRespaldoCompleto($nombre, $archivo);
                if ($rutaZip) {
                    $archivoFinal = "respaldos/{$nombre}.zip";
                }
            }
            
            // Registrar en base de datos
            $respaldo = $this->respaldoRepository->registrarRespaldo([
                'nombre' => $nombre,
                'archivo' => $archivoFinal,
                'tamano' => file_exists(storage_path("app/{$archivoFinal}")) ? filesize(storage_path("app/{$archivoFinal}")) : 0,
                'tipo' => $config && $config->incluir_archivos ? 'completo' : 'base_datos',
                'estado' => 'completado',
                'fecha_generacion' => Carbon::now(),
                'usuario_id' => $usuario->id,
                'observaciones' => $observaciones
            ]);
            
            // Limpiar respaldos antiguos
            $this->limpiarRespaldosAntiguos();
            
            // Actualizar fecha de último respaldo
            if ($config) {
                $config->ultimo_respaldo = Carbon::now();
                $config->proximo_respaldo = $this->calcularProximoRespaldo($config);
                $config->save();
            }
            
            return $respaldo;
            
        } catch (\Exception $e) {
            // Registrar error
            Log::error('Error en respaldo: ' . $e->getMessage());
            
            $this->respaldoRepository->registrarRespaldo([
                'nombre' => 'respaldo_fallido_' . Carbon::now()->format('Y-m-d_H-i-s'),
                'archivo' => '',
                'tamano' => 0,
                'tipo' => 'error',
                'estado' => 'fallido',
                'fecha_generacion' => Carbon::now(),
                'usuario_id' => $usuario->id,
                'observaciones' => 'Error: ' . $e->getMessage()
            ]);
            
            throw $e;
        }
    }

    /**
     * Crear respaldo completo (BD + archivos)
     */
    protected function crearRespaldoCompleto($nombre, $archivoBD)
    {
        $zip = new ZipArchive();
        $rutaZip = storage_path("app/respaldos/{$nombre}.zip");

        if ($zip->open($rutaZip, ZipArchive::CREATE) === true) {
            // Agregar respaldo de BD
            $rutaBD = storage_path("app/{$archivoBD}");
            if (file_exists($rutaBD)) {
                $zip->addFile($rutaBD, 'database.sql');
            }

            // Agregar archivos de storage (imágenes, etc.)
            $this->agregarCarpetaAlZip($zip, storage_path('app/public'), 'public');

            $zip->close();

            // Eliminar archivo SQL individual
            if (file_exists($rutaBD)) {
                unlink($rutaBD);
            }

            return $rutaZip;
        }

        return null;
    }

    /**
     * Agregar carpeta recursivamente al ZIP
     */
    protected function agregarCarpetaAlZip($zip, $carpeta, $nombreRelativo)
    {
        if (!is_dir($carpeta)) {
            return;
        }

        $archivos = scandir($carpeta);

        foreach ($archivos as $archivo) {
            if ($archivo == '.' || $archivo == '..') continue;

            $rutaCompleta = $carpeta . '/' . $archivo;
            $nombreEnZip = $nombreRelativo . '/' . $archivo;

            if (is_dir($rutaCompleta)) {
                $zip->addEmptyDir($nombreEnZip);
                $this->agregarCarpetaAlZip($zip, $rutaCompleta, $nombreEnZip);
            } else {
                $zip->addFile($rutaCompleta, $nombreEnZip);
            }
        }
    }

    /**
     * Calcular próxima fecha de respaldo según configuración
     */
    protected function calcularProximoRespaldo($config)
    {
        if (!$config->activo || $config->frecuencia == 'manual') {
            return null;
        }

        $ahora = Carbon::now();

        switch ($config->frecuencia) {
            case 'diario':
                $proximo = Carbon::today()->setTimeFromTimeString($config->hora_programada);
                if ($proximo->isPast()) {
                    $proximo->addDay();
                }
                break;

            case 'semanal':
                $proximo = Carbon::now()->next($config->dia_semana)
                    ->setTimeFromTimeString($config->hora_programada);
                break;

            case 'mensual':
                $proximo = Carbon::now()->startOfMonth()->addDays($config->dia_mes - 1)
                    ->setTimeFromTimeString($config->hora_programada);
                if ($proximo->isPast()) {
                    $proximo->addMonth();
                }
                break;

            default:
                return null;
        }

        return $proximo;
    }

    /**
     * Limpiar respaldos antiguos según configuración
     */
    protected function limpiarRespaldosAntiguos()
    {
        $config = $this->respaldoRepository->getConfiguracion();

        if (!$config || !$config->mantener_respaldos) {
            return;
        }

        $respaldos = $this->respaldoRepository->getHistorialRespaldos();
        $items = $respaldos->items();

        if (count($items) > $config->mantener_respaldos) {
            $eliminar = array_slice($items, $config->mantener_respaldos);

            foreach ($eliminar as $respaldo) {
                $this->respaldoRepository->eliminarRespaldo($respaldo->id);
            }
        }
    }

    /**
     * Restaurar un respaldo
     */
    public function restaurarRespaldo($id)
    {
        try {
            $respaldo = $this->respaldoRepository->findById($id);

            if (!$respaldo || !$respaldo->archivo) {
                throw new \Exception('Respaldo no encontrado');
            }

            $ruta = storage_path("app/{$respaldo->archivo}");

            if (!file_exists($ruta)) {
                throw new \Exception('Archivo de respaldo no encontrado: ' . $ruta);
            }

            // Si es ZIP, extraer
            if (pathinfo($ruta, PATHINFO_EXTENSION) == 'zip') {
                $zip = new ZipArchive();
                if ($zip->open($ruta) === true) {
                    $tempDir = storage_path('app/temp_restore_' . time()); // ← Nombre único
                    
                    // Crear directorio temporal
                    if (!file_exists($tempDir)) {
                        mkdir($tempDir, 0755, true);
                    }

                    $zip->extractTo($tempDir);
                    $zip->close();

                    // Restaurar BD desde el SQL extraído
                    $sqlPath = $tempDir . '/database.sql';
                    if (file_exists($sqlPath)) {
                        $sql = file_get_contents($sqlPath);
                        
                        // Ejecutar el SQL (cuidado: esto borra datos existentes)
                        DB::unprepared($sql);
                    } else {
                        throw new \Exception('No se encontró el archivo database.sql en el ZIP');
                    }

                    // Restaurar archivos
                    $this->restaurarArchivos($tempDir);

                    // Limpiar temporal (versión para Windows)
                    $this->eliminarDirectorio($tempDir);
                    
                } else {
                    throw new \Exception('No se pudo abrir el archivo ZIP');
                }
            } else {
                // Restaurar directamente archivo SQL
                $sql = file_get_contents($ruta);
                
                // Verificar que no esté vacío
                if (empty($sql)) {
                    throw new \Exception('El archivo SQL está vacío');
                }
                
                DB::unprepared($sql);
            }

            return true;
            
        } catch (\Exception $e) {
            Log::error('Error al restaurar respaldo: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Restaurar archivos desde el respaldo (versión para Windows)
     */
    protected function restaurarArchivos($tempDir)
    {
        $publicDir = $tempDir . '/public';
        if (is_dir($publicDir)) {
            $destino = storage_path('app/public');
            
            // Crear destino si no existe
            if (!file_exists($destino)) {
                mkdir($destino, 0755, true);
            }
            
            // Copiar archivos (versión Windows)
            $this->copiarDirectorio($publicDir, $destino);
        }
    }

    /**
     * Copiar directorio recursivamente (compatible con Windows)
     */
    protected function copiarDirectorio($origen, $destino)
    {
        if (!is_dir($origen)) {
            return;
        }
        
        if (!file_exists($destino)) {
            mkdir($destino, 0755, true);
        }
        
        $items = scandir($origen);
        foreach ($items as $item) {
            if ($item == '.' || $item == '..') continue;
            
            $origenItem = $origen . DIRECTORY_SEPARATOR . $item;
            $destinoItem = $destino . DIRECTORY_SEPARATOR . $item;
            
            if (is_dir($origenItem)) {
                $this->copiarDirectorio($origenItem, $destinoItem);
            } else {
                copy($origenItem, $destinoItem);
            }
        }
    }

    /**
     * Eliminar directorio recursivamente (compatible con Windows)
     */
    protected function eliminarDirectorio($dir)
    {
        if (!file_exists($dir)) {
            return;
        }
        
        $items = scandir($dir);
        foreach ($items as $item) {
            if ($item == '.' || $item == '..') continue;
            
            $path = $dir . DIRECTORY_SEPARATOR . $item;
            if (is_dir($path)) {
                $this->eliminarDirectorio($path);
            } else {
                unlink($path);
            }
        }
        
        rmdir($dir);
    }

}
