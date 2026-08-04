<?php

namespace App\Console\Commands;

use App\Models\BillingRecord;
use App\Models\SifEvent;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;
use ZipArchive;

class VerifactuExportCommand extends Command
{
    protected $signature = 'presufactura:verifactu-export {user : ID del usuario}';

    protected $description = 'Exporta registros SIF de un tenant a ZIP';

    public function handle(): int
    {
        $user = User::findOrFail($this->argument('user'));

        $records = BillingRecord::query()
            ->where('user_id', $user->id)
            ->orderBy('id')
            ->get();

        if ($records->isEmpty()) {
            $this->warn('No hay registros SIF para este usuario.');

            return self::SUCCESS;
        }

        $exportDir = storage_path('app/sif/exports');
        if (! is_dir($exportDir)) {
            mkdir($exportDir, 0755, true);
        }

        $zipPath = $exportDir.'/sif_export_user_'.$user->id.'_'.now()->format('YmdHis').'.zip';
        $zip = new ZipArchive;

        if ($zip->open($zipPath, ZipArchive::CREATE) !== true) {
            $this->error('No se pudo crear el ZIP.');

            return self::FAILURE;
        }

        foreach ($records as $record) {
            if ($record->xml_path && Storage::disk('local')->exists($record->xml_path)) {
                $zip->addFromString(
                    basename($record->xml_path),
                    Storage::disk('local')->get($record->xml_path)
                );
            }
        }

        $zip->close();

        SifEvent::create([
            'user_id' => $user->id,
            'event_type' => SifEvent::TYPE_EXPORT,
            'payload' => [
                'records_count' => $records->count(),
                'zip_path' => $zipPath,
            ],
        ]);

        $this->info("Exportación creada: {$zipPath}");

        return self::SUCCESS;
    }
}
