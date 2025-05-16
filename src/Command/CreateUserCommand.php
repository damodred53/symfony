<?php
namespace App\Command;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

#[AsCommand(name: 'app:create-user')]
class CreateUserCommand extends Command
{
    public function __construct(
        private EntityManagerInterface $em,
        private UserPasswordHasherInterface $passwordHasher
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        try {


            $user = new User();
            $user->setUsername('test');
            $user->setEmail('test@example.com');
            $user->setCreatedAt(new \DateTimeImmutable());

            // Mot de passe hashé
            $hashedPassword = $this->passwordHasher->hashPassword($user, 'password123');
            $user->setPassword($hashedPassword);
            $user->setRoles(['ROLE_ADMIN']);

            $this->em->persist($user);
            $this->em->flush();

            $output->writeln('Utilisateur créé !');

            return Command::SUCCESS;
        }catch (\Throwable $e) {
            $output->writeln('Erreur lors de la création de l\'utilisateur : ' . $e->getMessage());
            return Command::FAILURE;
        }
    }
}
