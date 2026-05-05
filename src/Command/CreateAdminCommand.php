<?php

namespace App\Command;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

#[AsCommand(
    name: 'app:create-admin',
    description: 'Crée un nouvel utilisateur administrateur dans la table user.',
)]
class CreateAdminCommand extends Command
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private UserPasswordHasherInterface $passwordHasher
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addArgument('email', InputArgument::REQUIRED, 'L\'adresse email de l\'admin')
            ->addArgument('password', InputArgument::REQUIRED, 'Le mot de passe de l\'admin')
            ->addArgument('nom', InputArgument::OPTIONAL, 'Le nom complet de l\'admin', 'Administrateur');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $email = $input->getArgument('email');
        $password = $input->getArgument('password');
        $nom = $input->getArgument('nom');

        // Vérifier si l'utilisateur existe déjà
        $user = $this->entityManager->getRepository(User::class)->findOneBy(['email' => $email]);
        $isNew = false;

        if (!$user) {
            $user = new User();
            $user->setEmail($email);
            $user->setDateCreation(new \DateTime());
            $isNew = true;
        }

        $user->setNom($nom);
        $user->setRole('ADMIN');
        $user->setEtatCompte('ACTIF');
        $user->setIsVerified(true);
        $user->setIsPhoneVerified(true);

        // Hacher le mot de passe
        $hashedPassword = $this->passwordHasher->hashPassword($user, $password);
        $user->setMotDePasse($hashedPassword);

        $this->entityManager->persist($user);
        $this->entityManager->flush();

        $io->success(sprintf('L\'administrateur %s a été %s avec succès.', $email, $isNew ? 'créé' : 'mis à jour'));

        return Command::SUCCESS;
    }
}
