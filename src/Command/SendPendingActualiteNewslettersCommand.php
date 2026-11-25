<?php

declare(strict_types=1);

namespace App\Command;

use App\Repository\ActualiteRepository;
use App\Service\NewsletterMailer;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(
    name: 'app:newsletter:send-actualites',
    description: 'Envoie les actualités publiées dont la newsletter n’a pas encore été envoyée.',
)]
final class SendPendingActualiteNewslettersCommand extends Command
{
    public function __construct(
        private readonly ActualiteRepository $actualites,
        private readonly NewsletterMailer $newsletterMailer,
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $pending = $this->actualites->findPendingNewsletters();
        $sent = 0;

        foreach ($pending as $actualite) {
            if ($this->newsletterMailer->sendActualite($actualite)) {
                ++$sent;
                $output->writeln(sprintf('<info>Newsletter envoyée : %s</info>', $actualite->getTitre()));
            } else {
                $output->writeln(sprintf('<error>Échec de la newsletter : %s</error>', $actualite->getTitre()));
            }
        }

        $output->writeln(sprintf('%d actualité(s) traitée(s), %d envoyée(s).', count($pending), $sent));

        return $sent === count($pending) ? Command::SUCCESS : Command::FAILURE;
    }
}
