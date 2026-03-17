<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

/**
 * Service de gestion des images avec nommage métier déterministe
 */
class ImageService
{
    private const TEMP_DIRECTORY = 'tmp';

    private const array MIME_TO_EXTENSION = [
        'image/jpeg' => 'jpg',
        'image/jpg' => 'jpg',
        'image/png' => 'png',
        'image/webp' => 'webp',
    ];

    /**
     * Génère le chemin final que le service utilisera pour stocker l'image
     */
    public function predictPath(
        UploadedFile $file,
        string $directory,
        string $entityId
    ): string {
        $extension = self::getExtensionFromMime($file->getMimeType());
        $fileName = "{$entityId}.{$extension}";

        return "{$directory}/{$fileName}";
    }

    /**
     * Stocke l'image avec nommage métier
     */
    public function store(
        UploadedFile $file,
        string $directory,
        string $entityId,
        ?string $oldPath = null,
        string $disk = 'public'
    ): string {
        if ($oldPath) {
            $this->delete($oldPath, $disk);
        }

        $extension = self::getExtensionFromMime($file->getMimeType());
        $fileName = "{$entityId}.{$extension}";

        return $file->storeAs($directory, $fileName, $disk);
    }

    /**
     * Supprime une image du storage
     *
     * @param string|null $path
     * @param string $disk
     * @return bool
     */
    public function delete(?string $path, string $disk = 'public'): bool
    {
        if ($path && Storage::disk($disk)->exists($path)) {
            return Storage::disk($disk)->delete($path);
        }

        return true;
    }

    /**
     * Récupère l'extension appropriée depuis le MIME type
     *
     * @param string|null $mimeType
     * @return string
     */
    private static function getExtensionFromMime(?string $mimeType): string
    {
        return self::MIME_TO_EXTENSION[$mimeType] ?? 'jpg';
    }

    /**
     * Vérifie si un fichier existe
     *
     * @param string|null $path
     * @param string $disk
     * @return bool
     */
    public function exists(?string $path, string $disk = 'public'): bool
    {
        if (!$path) {
            return false;
        }

        return Storage::disk($disk)->exists($path);
    }

    /**
     * Récupère l'URL publique d'une image
     *
     * @param string|null $path
     * @param string $disk
     * @return string|null
     */
    public function getUrl(?string $path, string $disk = 'public'): ?string
    {
        if (!$path) {
            return null;
        }

        return Storage::disk($disk)->url($path);
    }
}
