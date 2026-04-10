<?php

declare(strict_types=1);

namespace App\Services;

use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class SignatureStorageService
{
    /**
     * Directory where signatures are stored (relative to private disk).
     */
    private const string STORAGE_DIR = 'signatures';

    /**
     * Convert a Base64-encoded signature to PNG and store it.
     *
     * @param  string $base64Data The data URI or raw Base64 string (e.g. "data:image/png;base64,iVBOR...")
     * @return string             The relative file path to the stored signature
     *
     * @throws \InvalidArgumentException If the Base64 data is invalid
     */
    public function store(string $base64Data): string
    {
        // Strip the data URI prefix if present
        $base64Data = preg_replace(
            '/^data:image\/\w+;base64,/',
            '',
            $base64Data,
        );

        $binary = base64_decode($base64Data, true);

        if ($binary === false) {
            throw new \InvalidArgumentException('Données de signature Base64 invalides.');
        }

        $filename = self::STORAGE_DIR . '/' . Str::uuid() . '.png';

        Storage::disk('local')->put($filename, $binary);

        return $filename;
    }

    /**
     * Delete a stored signature file.
     */
    public function delete(string $path): bool
    {
        return Storage::disk('local')->delete($path);
    }
}
