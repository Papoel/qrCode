<?php

namespace App\Controller;

use App\Entity\QrcodeEntity;
use App\Repository\QrcodeEntityRepository;
use App\Services\FileManagerService;
use App\Services\QrCodeFormHandler;
use App\Services\QrcodeService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/qrcode')]
#[IsGranted('ROLE_USER')]
class QrcodeController extends AbstractController
{
    public function __construct(
        private readonly QrCodeFormHandler $qrCodeFormHandler,
    ) {
    }

    #[Route('/', name: 'qrcode_index', methods: [Request::METHOD_GET])]
    public function index(QrcodeEntityRepository $qrcodeEntityRepository): Response
    {
        return $this->render(view: 'qrcode/index.html.twig', parameters: [
            'qrcode_entities' => $qrcodeEntityRepository->findAll(),
        ]);
    }

    #[Route('/new', name: 'qrcode_new', methods: [Request::METHOD_GET, Request::METHOD_POST])]
    public function new(Request $request): Response
    {
        $qrcodeEntity = $this->qrCodeFormHandler->handleForm($request);

        if (null !== $qrcodeEntity) {
            return $this->redirectToRoute(route: 'qrcode_index');
        }

        $form = $this->qrCodeFormHandler->createForm();

        return $this->render(view: 'qrcode/new.html.twig', parameters: [
            'form' => $form->createView(),
        ]);
    }

    #[Route('/{id}', name: 'qrcode_show', methods: [Request::METHOD_GET])]
    public function show(QrcodeEntity $qrcodeEntity): Response
    {
        return $this->render(view: 'qrcode/show.html.twig', parameters: [
            'qrcode' => $qrcodeEntity,
        ]);
    }

    #[Route('/{id}/edit', name: 'qrcode_edit', methods: [Request::METHOD_GET, Request::METHOD_POST])]
    public function edit(Request $request, QrcodeEntity $qrcodeEntity): Response
    {
        $updatedQrcodeEntity = $this->qrCodeFormHandler->handleEditForm($request, $qrcodeEntity);

        if (null !== $updatedQrcodeEntity) {
            return $this->redirectToRoute(route: 'qrcode_index');
        }

        $form = $this->qrCodeFormHandler->getForm($qrcodeEntity);

        return $this->render(view: 'qrcode/edit.html.twig', parameters: [
            'form' => $form->createView(),
            'qrcode' => $qrcodeEntity,
        ]);
    }

    #[Route('/{id}', name: 'qrcode_delete', methods: [Request::METHOD_POST])]
    public function delete(Request $request, QrcodeEntity $qrcodeEntity, EntityManagerInterface $entityManager, FileManagerService $fileManagerService): Response
    {
        // Vérifier le token CSRF
        /* @phpstan-ignore-next-line */
        if ($this->isCsrfTokenValid(id: 'delete'.$qrcodeEntity->getId(), token: $request->get(key: '_token'))) {
            // Effacer le fichier image du QR code
            $filePath = $qrcodeEntity->getFilePath();
            if ($filePath) {
                $fileManagerService->deleteFile($filePath);
            }

            // Supprimer l'entité
            $entityManager->remove($qrcodeEntity);
            $entityManager->flush();

            sweetalert()->success('Le QR code a été supprimé avec succès !');
        }

        return $this->redirectToRoute(route: 'qrcode_index', status: Response::HTTP_SEE_OTHER);
    }

    #[Route('/', name: 'qrcode_delete_all', methods: [Request::METHOD_POST])]
    public function deleteAll(Request $request, QrcodeService $qrcodeService): Response
    {
        $qrcodeService->deleteAllQrcodes();

        return $this->redirectToRoute(route: 'qrcode_index', status: Response::HTTP_SEE_OTHER);
    }
}
