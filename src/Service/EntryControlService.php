<?php

namespace App\Service;

use App\Entity\Billet;
use App\Entity\ControleEntree;
use App\Entity\Utilisateur;
use App\Repository\BilletRepository;
use App\Repository\ControleEntreeRepository;
use Doctrine\ORM\EntityManagerInterface;

final class EntryControlService
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly BilletRepository $billets,
        private readonly ControleEntreeRepository $controles,
    ) {
    }

    /** @return array{resultat: string, billet: ?Billet} */
    public function control(string $value, ?Utilisateur $user): array
    {
        $value = trim($value);
        if (str_starts_with($value, 'OLYMPIA:TICKET:')) {
            $value = substr($value, strlen('OLYMPIA:TICKET:'));
        }

        $this->entityManager->beginTransaction();
        try {
            $ticket = $this->billets->findForControl($value, true);
            if (!$ticket) {
                $this->record(null, $user, ControleEntree::RESULTAT_INCONNU);
                $this->entityManager->flush();
                $this->entityManager->commit();
                return ['resultat' => ControleEntree::RESULTAT_INCONNU, 'billet' => null];
            }

            if ($ticket->getStatut() === Billet::STATUT_ANNULE) {
                $result = ControleEntree::RESULTAT_ANNULE;
            } elseif ($ticket->getStatut() === Billet::STATUT_UTILISE) {
                $result = ControleEntree::RESULTAT_DEJA_UTILISE;
            } elseif ($this->controles->existsSuccessfulFor($ticket)) {
                $result = ControleEntree::RESULTAT_DEJA_UTILISE;
                $ticket->setStatut(Billet::STATUT_UTILISE);
            } else {
                $result = ControleEntree::RESULTAT_VALIDE;
                $ticket->setStatut(Billet::STATUT_UTILISE)->setDateUtilisation(new \DateTimeImmutable());
            }

            $this->record($ticket, $user, $result);
            $this->entityManager->flush();
            $this->entityManager->commit();
            return ['resultat' => $result, 'billet' => $ticket];
        } catch (\Throwable $exception) {
            if ($this->entityManager->getConnection()->isTransactionActive()) {
                $this->entityManager->rollback();
            }
            throw $exception;
        }
    }

    private function record(?Billet $ticket, ?Utilisateur $user, string $result): void
    {
        $control = (new ControleEntree())
            ->setBillet($ticket)
            ->setUtilisateur($user)
            ->setResultat($result);
        if ($result === ControleEntree::RESULTAT_VALIDE && $ticket) {
            $control->setCleSucces($ticket->getJeton());
        }
        $this->entityManager->persist($control);
    }
}
