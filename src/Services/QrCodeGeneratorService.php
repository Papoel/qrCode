<?php

declare(strict_types=1);

namespace App\Services;

use App\Entity\QrcodeEntity;
use Endroid\QrCode\Color\Color;
use Endroid\QrCode\Encoding\Encoding;
use Endroid\QrCode\Label\Font\NotoSans;
use Endroid\QrCode\Label\Label;
use Endroid\QrCode\QrCode;
use Endroid\QrCode\Writer\PngWriter;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;

readonly class QrCodeGeneratorService
{
    public function __construct(
        private ParameterBagInterface $parameterBag
    ) {
    }

    public function generateQrCode(QrcodeEntity $qrcodeEntity): void
    {
        $data = $qrcodeEntity->getData();
        $prefix = 'qrcode_papoel';

        if (null !== $data) {
            $parsedUrl = parse_url(url: $data);
            // Verifier si filename est renseigné retourne true ou false
            $filenameExist = $qrcodeEntity->getFilename();
            // Si filename n'est pas renseigné, on génère un nom de fichier unique sinon on supprime le fichier existant
            if ($filenameExist) {
                $projectDir = $this->parameterBag->get(name: 'project_dir');
                $uploadedDir = $this->parameterBag->get(name: 'upload_dir');
                $filePath = sprintf('%s/%s/%s', $projectDir, $uploadedDir, $qrcodeEntity->getFilename());
                if (file_exists($filePath)) {
                    // delete the file
                    unlink($filePath);
                }
            }

            if (false !== $parsedUrl && isset($parsedUrl['host'])) {
                $prefix = str_replace(search: ['www.', '.fr', '.com'], replace: '', subject: $parsedUrl['host']);
                $prefix = uniqid(prefix: $prefix, more_entropy: true);
            }
        }

        $filename = sprintf('%s.png', (string) $prefix);
        $projectDir = $this->parameterBag->get(name: 'project_dir');
        $uploadedDir = $this->parameterBag->get(name: 'upload_dir');
        $filePath = sprintf('%s/%s/%s', $projectDir, $uploadedDir, $filename);

        // Convertir les couleurs hexadécimales en valeurs RGB
        $foregroundColor = $this->hexToRgb(hex: $qrcodeEntity->getForegroundColor() ?? '#000000');
        $backgroundColor = $this->hexToRgb(hex: $qrcodeEntity->getBackgroundColor() ?? '#ffffff');

        // Générer et sauvegarder l'image QR Code avec les couleurs
        $writer = new PngWriter();
        $qrCode = QrCode::create(data: $data)
            ->setEncoding(encoding: new Encoding(value: 'UTF-8'))
            ->setSize(size: 300)
            ->setMargin(margin: 10)
            ->setForegroundColor(foregroundColor: new Color(red: $foregroundColor['red'], green: $foregroundColor['green'], blue: $foregroundColor['blue']))
            ->setBackgroundColor(backgroundColor: new Color(red: $backgroundColor['red'], green: $backgroundColor['green'], blue: $backgroundColor['blue']));

        $labelText = $qrcodeEntity->getLabel();
        $label = null;
        if ($labelText) {
            $label = Label::create(text: $labelText)->setFont(font: new NotoSans(size: 10));
        }

        // Écrire le fichier
        $writer->write(qrCode: $qrCode, label: $label)->saveToFile($filePath);

        // Définir le chemin et le nom de fichier dans l'entité
        $qrcodeEntity->setFilePath($filePath);
        $qrcodeEntity->setFilename($filename);
    }

    private function hexToRgb(string $hex): array
    {
        $hex = ltrim(string: $hex, characters: '#');
        if (3 === strlen(string: $hex)) {
            $hex = str_repeat(string: $hex, times: 2);
        }
        $rgb = array_map(callback: 'hexdec', array: str_split(string: $hex, length: 2));

        return [
            'red' => $rgb[0],
            'green' => $rgb[1],
            'blue' => $rgb[2],
        ];
    }
}
