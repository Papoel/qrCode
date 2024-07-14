<?php

namespace App\Controller;

use Endroid\QrCode\Color\Color;
use Endroid\QrCode\Encoding\Encoding;
use Endroid\QrCode\Label\Font\NotoSans;
use Endroid\QrCode\Label\Label;
use Endroid\QrCode\Logo\Logo;
use Endroid\QrCode\QrCode;
use Endroid\QrCode\Writer\PngWriter;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class HomeController extends AbstractController
{
    #[Route('/', name: 'home_index', methods: [Request::METHOD_GET])]
    public function index(): Response
    {
        // dd('BORDEL DE NAVBAR QUI ME REND FLOU !');
        // $this->addFlash(type: 'success', message: 'Bienvenue sur la page d\'accueil !');
        /** @var string $projectDir */
        $projectDir = $this->getParameter(name: 'kernel.project_dir');
        $logoPath = sprintf('%s/assets/images/papoel.jpg', $projectDir);

        $writer = new PngWriter();
        $qrCode = QrCode::create(data: 'https://www.cyrcrea.com/')
            ->setEncoding(encoding: new Encoding(value: 'UTF-8'))
            ->setSize(size: 250)
            ->setMargin(margin: 0)
            ->setForegroundColor(foregroundColor: new Color(red: 0, green: 0, blue: 0))
            ->setBackgroundColor(backgroundColor: new Color(red: 255, green: 255, blue: 255));

        $logo = Logo::create(path: $logoPath)
            ->setResizeToWidth(resizeToWidth: 100);
        $label = Label::create(text: '')->setFont(font: new NotoSans(size: 8));

        /**
         * @var array<string, string> $qrCodes
         */
        $qrCodes = [];

        $qrCodes['img'] = $writer->write(qrCode: $qrCode, logo: $logo)->getDataUri();

        $qrCodes['simple'] = $writer->write(
            qrCode: $qrCode,
            logo: null,
            label: $label->setText(text: '') // Sert à ajouter un texte en bas du QR code
        )->getDataUri();

        $qrCode->setForegroundColor(foregroundColor: new Color(red: 255, green: 0, blue: 0));
        $qrCodes['changeColor'] = $writer->write(
            qrCode: $qrCode,
            logo: null,
            label: $label->setText(text: '')
        )->getDataUri();

        $qrCode->setForegroundColor(foregroundColor: new Color(red: 0, green: 0, blue: 0))->setBackgroundColor(new Color(255, 0, 0));
        $qrCodes['changeBgColor'] = $writer->write(
            qrCode: $qrCode,
            logo: null,
            label: $label->setText('Background Color Change')
        )->getDataUri();

        $qrCode->setSize(size: 200)->setForegroundColor(foregroundColor: new Color(red: 0, green: 0, blue: 0))->setBackgroundColor(new Color(255, 255, 255));
        $qrCodes['withImage'] = $writer->write(
            qrCode: $qrCode,
            logo: $logo,
            label: $label->setText('With Image')->setFont(new NotoSans(20))
        )->getDataUri();

        return $this->render(view: 'home/index.html.twig', parameters: [
            'qrCodes' => $qrCodes,
        ]);
    }
}
