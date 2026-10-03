<?php

namespace App\Command;

use App\Entity\Utilisateur;
use App\Repository\UtilisateurRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Question\Question;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

#[AsCommand(name: 'app:admin:create', description: 'Crée ou met à jour l’unique administrateur Olympia.')]
final class CreateAdminCommand extends Command
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly UtilisateurRepository $utilisateurRepository,
        private readonly UserPasswordHasherInterface $passwordHasher,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addArgument('email', InputArgument::REQUIRED, 'Email de l’administrateur')
            ->addArgument('password', InputArgument::OPTIONAL, 'Mot de passe (sera demandé en mode interactif)');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $email = mb_strtolower(trim((string) $input->getArgument('email')));
        $password = (string) $input->getArgument('password');
        if ($password === '') {
            $question = new Question('Mot de passe : ');
            $question->setHidden(true);
            $password = (string) $this->getHelper('question')->ask($input, $output, $question);
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL) || strlen($password) < 8) {
            $output->writeln('<error>Email invalide ou mot de passe trop court (8 caractères minimum).</error>');
            return Command::INVALID;
        }

        $admin = $this->utilisateurRepository->findOneBy(['email' => $email]);
        if ($admin === null && $this->utilisateurRepository->count([]) > 0) {
            $output->writeln('<error>Un utilisateur existe déjà. Utilisez son email pour mettre à jour l’administrateur unique.</error>');
            return Command::FAILURE;
        }
        $admin ??= new Utilisateur();
        $admin->setEmail($email)->setNom('Administrateur')->setRole('ROLE_ADMIN')->setEstActif(true);
        $admin->setMotDePasse($this->passwordHasher->hashPassword($admin, $password));
        $this->entityManager->persist($admin);
        $this->entityManager->flush();

        $output->writeln('<info>Administrateur enregistré. Vous pouvez vous connecter sur /olympia-admin/login.</info>');
        return Command::SUCCESS;
    }
}
