<?php

namespace App\Command;

use App\Repository\ReservationRepository;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(
    name: 'app:reservations:cleanup-expired',
    description: 'Supprime les reservations payantes dont le delai de paiement est expire.',
)]
final class CleanupExpiredReservationsCommand extends Command
{
    public function __construct(private readonly ReservationRepository $reservations)
    {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $deleted = $this->reservations->deleteExpiredPaymentReservations();
        $output->writeln(sprintf('<info>%d reservation(s) expiree(s) supprimee(s).</info>', $deleted));

        return Command::SUCCESS;
    }
}
