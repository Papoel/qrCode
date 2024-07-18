<?php

namespace App\Controller;

use App\Services\Contact\ContactFormHandler;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class HomeController extends AbstractController
{
    #[Route('/', name: 'home_index', methods: [Request::METHOD_GET, Request::METHOD_POST])]
    public function index(Request $request, ContactFormHandler $contactFormHandler): Response
    {
        $contact = $contactFormHandler->handleForm($request);
        $form = $contactFormHandler->createForm();

        if (null !== $contact) {
            // Redirigez l'utilisateur vers la même page
            return $this->redirectToRoute(route: 'home_index');
        }

        return $this->render(view: 'home/index.html.twig', parameters: [
            'form' => $form->createView(),
        ]);
    }
}
