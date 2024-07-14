<?php

declare(strict_types=1);

namespace App\Services;

use Symfony\Component\Filesystem\Filesystem;

readonly class FileManagerService
{
    public function __construct(
        private Filesystem $filesystem
    ) {
    }

    public function deleteFile(string $filePath): void
    {
        // Si le fichier existe, on le supprime
        if ($this->filesystem->exists($filePath)) {
            $this->filesystem->remove($filePath);
        }
    }
}
