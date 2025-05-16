<?php

namespace App\Command;
use App\Entity\TokenDb;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(name: 'app:create-api-token')]
class CreateApiTokenCommand extends Command
{

    public function __construct(

         private EntityManagerInterface $em
    ) {
        parent::__construct();
    }

    public function execute(InputInterface $input, OutputInterface $output): int
    {
        $token = new TokenDb();
        $token->setToken("WebSiteToken");
        $token->setServiceName("WebSite");
        $this->em->persist($token);
        $this->em->flush();
        $output->writeln('Token created successfully!');
        return Command::SUCCESS;
    }
}
