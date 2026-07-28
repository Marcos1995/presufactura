<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\File;
use ZipArchive;

class DataExportService
{
    public function createZip(User $user): string
    {
        if (! class_exists(ZipArchive::class)) {
            throw new \RuntimeException('La extensión ZipArchive no está disponible en el servidor.');
        }

        $dir = storage_path('app/exports');
        File::ensureDirectoryExists($dir);

        $zipPath = $dir.'/presufactura-datos-'.$user->id.'-'.time().'.zip';
        $zip = new ZipArchive;

        if ($zip->open($zipPath, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
            throw new \RuntimeException('No se pudo crear el archivo ZIP.');
        }

        $zip->addFromString('profile.json', $this->encode($this->profileData($user)));
        $zip->addFromString('clients.json', $this->encode($this->clientsData($user)));
        $zip->addFromString('documents.json', $this->encode($this->documentsData($user)));
        $zip->close();

        return $zipPath;
    }

    public function filename(): string
    {
        return 'presufactura-datos-'.now()->format('Y-m-d').'.zip';
    }

    private function profileData(User $user): array
    {
        return $user->makeHidden(['password', 'remember_token'])->toArray();
    }

    private function clientsData(User $user): array
    {
        return $user->clients()->orderBy('name')->get()->toArray();
    }

    private function documentsData(User $user): array
    {
        return $user->documents()
            ->with(['lineItems', 'events'])
            ->orderByDesc('created_at')
            ->get()
            ->map(fn ($document) => [
                ...$document->withoutRelations()->toArray(),
                'line_items' => $document->lineItems->toArray(),
                'events' => $document->events->map(fn ($event) => [
                    'event_type' => $event->event_type,
                    'meta' => $event->meta,
                    'created_at' => $event->created_at?->toIso8601String(),
                ])->all(),
            ])
            ->values()
            ->all();
    }

    private function encode(array $data): string
    {
        return json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR);
    }
}
