<?php

declare(strict_types=1);

namespace App\Services\Contact;

use App\Entity\Contact;
use App\Form\ContactType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;

class ContactFormHandler
{
    public function __construct(
        private readonly FormFactoryInterface $formFactory,
        private readonly EntityManagerInterface $entityManager,
    ) {
    }

    public function handleForm(Request $request): ?Contact
    {
        $contact = new Contact();
        $form = $this->formFactory->create(type: ContactType::class, data: $contact);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Persist and save the entity
            $this->entityManager->persist(object: $contact);
            $this->entityManager->flush();

            sweetalert()->info('Votre message a été envoyé avec succès, nous vous répondrons dans les plus brefs délais !');

            return $contact;
        }

        return null;
    }

    public function createForm(): FormInterface
    {
        $contact = new Contact();

        return $this->formFactory->create(type: ContactType::class, data: $contact);
    }

    public function getForm(?Contact $contact = null): FormInterface
    {
        $contact = $contact ?? new Contact();

        return $this->formFactory->create(type: ContactType::class, data: $contact);
    }
}
