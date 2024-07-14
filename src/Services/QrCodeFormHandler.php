<?php

declare(strict_types=1);

namespace App\Services;

use App\Entity\QrcodeEntity;
use App\Form\QrcodeType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;

class QrCodeFormHandler
{
    public function __construct(
        private FormFactoryInterface $formFactory,
        private EntityManagerInterface $entityManager,
        private Security $security,
        private QrCodeGeneratorService $qrCodeGeneratorService,
        private RequestStack $requestStack,
    ) {
    }

    public function handleForm(Request $request): ?QrcodeEntity
    {
        $qrcodeEntity = new QrcodeEntity();
        $form = $this->formFactory->create(type: QrcodeType::class, data: $qrcodeEntity);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $qrcodeEntity->setType(type: 'png');
            $qrcodeEntity->setUser(user: $this->security->getUser());

            // Generate QR code and get the file path
            $this->qrCodeGeneratorService->generateQrCode(qrcodeEntity: $qrcodeEntity);

            $this->addFlash(type: 'success', message: 'QR code généré avec succès !');

            // Persist and save the entity
            $this->entityManager->persist(object: $qrcodeEntity);
            $this->entityManager->flush();

            return $qrcodeEntity;
        }

        return null;
    }

    public function handleEditForm(Request $request, QrcodeEntity $qrcodeEntity): ?QrcodeEntity
    {
        $form = $this->formFactory->create(type: QrcodeType::class, data: $qrcodeEntity);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Mettre à jour le QR code
            $this->qrCodeGeneratorService->generateQrCode(qrcodeEntity: $qrcodeEntity);

            $this->addFlash(type: 'success', message: "Le QRCode '".$qrcodeEntity->getData()."' a été mis à jour avec succès !");

            // Persister et sauvegarder l'entité
            $this->entityManager->persist(object: $qrcodeEntity);
            $this->entityManager->flush();

            return $qrcodeEntity;
        }

        return null;
    }

    public function createForm(): FormInterface
    {
        $qrcodeEntity = new QrcodeEntity();

        return $this->formFactory->create(type: QrcodeType::class, data: $qrcodeEntity);
    }

    public function getForm(?QrcodeEntity $qrcodeEntity = null): FormInterface
    {
        $qrcodeEntity = $qrcodeEntity ?? new QrcodeEntity();

        return $this->formFactory->create(type: QrcodeType::class, data: $qrcodeEntity);
    }

    public function addFlash(string $type, string $message): void
    {
        $this->requestStack->getCurrentRequest()->getSession()->getFlashBag()->add($type, $message);
    }
}
