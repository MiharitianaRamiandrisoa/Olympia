<?php

namespace App\Tests\Unit;

use App\Entity\Evenement;
use App\Entity\Billet;
use App\Service\TicketQrCodeGenerator;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Validator\Validation;

final class EvenementBilletterieTest extends TestCase
{
    public function testDatesIncoherentesAreRejected(): void
    {
        $event = (new Evenement())
            ->setTitre('Evenement test')
            ->setSlug('evenement-test')
            ->setDateDebut(new \DateTimeImmutable('2026-11-10 18:00'))
            ->setDateFin(new \DateTimeImmutable('2026-11-10 17:00'))
            ->setDateOuvertureReservations(new \DateTimeImmutable('2026-11-01 00:00'))
            ->setDateClotureReservations(new \DateTimeImmutable('2026-11-11 00:00'));

        $violations = Validation::createValidatorBuilder()->enableAttributeMapping()->getValidator()->validate($event);

        self::assertGreaterThanOrEqual(2, $violations->count());
    }

    public function testReservationWindowAndCapacityAreExposed(): void
    {
        $event = (new Evenement())
            ->setTitre('Evenement test')
            ->setSlug('evenement-test')
            ->setDateDebut(new \DateTimeImmutable('2026-11-10 18:00'))
            ->setCapaciteMaximale(100)
            ->setDateOuvertureReservations(new \DateTimeImmutable('2026-11-01 00:00'))
            ->setDateClotureReservations(new \DateTimeImmutable('2026-11-10 17:00'));

        self::assertTrue($event->isReservationOuverte(new \DateTimeImmutable('2026-11-05 12:00')));
        self::assertFalse($event->isReservationOuverte(new \DateTimeImmutable('2026-11-10 18:01')));
        self::assertSame(100, $event->getCapaciteMaximale());
    }

    public function testReservationWindowUsesTheConfiguredMinutePrecisely(): void
    {
        $event = (new Evenement())
            ->setTitre('Evenement test')
            ->setSlug('evenement-test-minute')
            ->setDateDebut(new \DateTimeImmutable('2026-11-10 18:00:00'))
            ->setCapaciteMaximale(100)
            ->setDateOuvertureReservations(new \DateTimeImmutable('2026-11-10 10:11:00'))
            ->setDateClotureReservations(new \DateTimeImmutable('2026-11-10 17:11:00'));

        self::assertFalse($event->isReservationOuverte(new \DateTimeImmutable('2026-11-10 10:10:59')));
        self::assertTrue($event->isReservationOuverte(new \DateTimeImmutable('2026-11-10 10:11:00')));
        self::assertTrue($event->isReservationOuverte(new \DateTimeImmutable('2026-11-10 10:11:59')));
        self::assertTrue($event->isReservationOuverte(new \DateTimeImmutable('2026-11-10 17:11:59')));
        self::assertFalse($event->isReservationOuverte(new \DateTimeImmutable('2026-11-10 17:12:00')));
    }

    public function testTicketQrCodeContainsOnlyTheOpaqueToken(): void
    {
        $ticket = (new Billet())->setReference('TKT-TEST')->setJeton(str_repeat('a', 64));
        $dataUri = (new TicketQrCodeGenerator())->generateDataUri($ticket);

        self::assertStringStartsWith('data:image/svg+xml;base64,', $dataUri);
        self::assertStringNotContainsString('test@example.com', $dataUri);
    }
}
