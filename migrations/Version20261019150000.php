<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20261019150000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Ajoute l expiration des reservations payantes et du paiement simule.';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE reservation ADD date_expiration_paiement DATETIME DEFAULT NULL');
        $this->addSql("UPDATE reservation SET date_expiration_paiement = DATE_ADD(date_creation, INTERVAL 15 MINUTE) WHERE statut = 'en_attente_paiement' AND date_expiration_paiement IS NULL");
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE reservation DROP date_expiration_paiement');
    }
}
