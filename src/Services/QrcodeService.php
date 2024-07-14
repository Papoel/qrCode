<?php

declare(strict_types=1);

namespace App\Services;

use App\Entity\QrcodeEntity;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\Filesystem\Filesystem;

class QrcodeService
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly ParameterBagInterface $parameterBag,
        private readonly Filesystem $filesystem,
    ) {
    }

    public function deleteAllQrcodes(): void
    {
        $qrcodes = $this->entityManager->getRepository(QrcodeEntity::class)->findAll();
        foreach ($qrcodes as $qrcode) {
            // Suppression des QRCodes
            $this->entityManager->remove($qrcode);
        }

        // Suppression des fichiers
        $path = $this->parameterBag->get('project_dir').'/'.$this->parameterBag->get('upload_dir');
        $this->filesystem->remove($path);

        // Recréer le répertoire vide
        $this->filesystem->mkdir($path);

        // Valider les changements dans la base de données
        $this->entityManager->flush();
    }
}
