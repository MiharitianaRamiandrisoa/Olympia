<?php

namespace App\Tests\Unit;

use App\Entity\Billet;
use App\Entity\ControleEntree;
use App\Repository\BilletRepository;
use App\Repository\ControleEntreeRepository;
use App\Service\EntryControlService;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\TestCase;

final class EntryControlServiceTest extends TestCase
{
    public function testAValidTicketBecomesUsed(): void
    {
        $ticket = (new Billet())->setReference('TKT-TEST')->setJeton(str_repeat('a', 64));
        $repository = $this->createMock(BilletRepository::class);
        $repository->expects(self::once())->method('findForControl')->with('TKT-TEST', true)->willReturn($ticket);
        $controls = $this->createMock(ControleEntreeRepository::class);
        $controls->expects(self::once())->method('existsSuccessfulFor')->with($ticket)->willReturn(false);

        $entityManager = $this->createMock(EntityManagerInterface::class);
        $entityManager->expects(self::once())->method('beginTransaction');
        $entityManager->expects(self::once())->method('persist')->with(self::callback(static fn (ControleEntree $control): bool => $control->getResultat() === ControleEntree::RESULTAT_VALIDE));
        $entityManager->expects(self::once())->method('flush');
        $entityManager->expects(self::once())->method('commit');

        $result = (new EntryControlService($entityManager, $repository, $controls))->control('TKT-TEST', null);

        self::assertSame(ControleEntree::RESULTAT_VALIDE, $result['resultat']);
        self::assertSame(Billet::STATUT_UTILISE, $ticket->getStatut());
        self::assertNotNull($ticket->getDateUtilisation());
    }

    public function testAnAlreadyUsedTicketIsRejected(): void
    {
        $ticket = (new Billet())->setReference('TKT-TEST')->setJeton(str_repeat('b', 64))->setStatut(Billet::STATUT_UTILISE);
        $repository = $this->createMock(BilletRepository::class);
        $repository->method('findForControl')->willReturn($ticket);
        $controls = $this->createMock(ControleEntreeRepository::class);
        $entityManager = $this->createMock(EntityManagerInterface::class);
        $entityManager->method('getConnection')->willReturn($this->createMock(\Doctrine\DBAL\Connection::class));

        $service = new EntryControlService($entityManager, $repository, $controls);
        $result = $service->control('TKT-TEST', null);

        self::assertSame(ControleEntree::RESULTAT_DEJA_UTILISE, $result['resultat']);
    }
}
