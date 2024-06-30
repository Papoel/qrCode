<?php

declare(strict_types=1);

namespace App\Command;

use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class EditUserCommand extends Command
{
    public const CMD_NAME = 'app:edit:user';

    private SymfonyStyle $io;

    public function __construct(
        private readonly EntityManagerInterface $em,
        private readonly UserRepository $userRepository,
        private readonly UserPasswordHasherInterface $passwordHasher,
    ) {
        parent::__construct(name: self::CMD_NAME);
    }

    protected function configure(): void
    {
        $this->setDescription(description: 'Modifier un utilisateur');
    }

    protected function initialize(InputInterface $input, OutputInterface $output): void
    {
        $this->io = new SymfonyStyle(input: $input, output: $output);
    }

    /**
     * @throws \Exception
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $users = $this->userRepository->findAll();

        if (empty($users)) {
            $this->io->warning('Aucun utilisateur trouvé.');

            return Command::FAILURE;
        }

        $userChoices = [];
        foreach ($users as $user) {
            $userChoices[$user->getFullName()] = $user;
        }

        $email = $this->io->choice(question: 'Sélectionnez un utilisateur à modifier', choices: array_keys($userChoices));
        $user = $userChoices[$email];

        $firstname = $this->io->ask(question: 'Quel est le nouveau prénom de l\'utilisateur ?', default: $user->getFirstname());
        $lastname = $this->io->ask(question: 'Quel est le nouveau nom de l\'utilisateur ?', default: $user->getLastname());
        $email = $this->io->ask(question: 'Quel est le nouvel email de l\'utilisateur ?', default: $user->getEmail());
        $roles = $this->io->choice(
            question: 'Quel est le rôle de l\'utilisateur ?',
            choices: [
                'ROLE_USER',
                'ROLE_ADMIN',
            ],
            default: $user->getRoles()[0]
        );

        $updatePassword = $this->io->confirm(question: 'Voulez-vous modifier le mot de passe de l\'utilisateur ?', default: false);

        if ($updatePassword) {
            $password = $this->io->askHidden(question: 'Quel est le nouveau mot de passe de l\'utilisateur ?');
            $user->setPassword($this->passwordHasher->hashPassword(user: $user, plainPassword: $password));
        }

        $user->setFirstname($firstname);
        $user->setLastname($lastname);
        $user->setEmail($email);
        $user->setRoles([$roles]);

        $this->em->persist($user);
        $this->em->flush();

        $this->io->success('L\'utilisateur a bien été modifié !');

        return Command::SUCCESS;
    }
}
