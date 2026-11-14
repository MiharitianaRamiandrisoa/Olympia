<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20261114090000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Ajout des identifiants externes pour les imports Make des evenements et promotions.';
    }

    public function up(Schema $schema): void
    {
        $this->addSql("ALTER TABLE evenement ADD origine VARCHAR(30) NOT NULL DEFAULT 'manuelle', ADD identifiant_externe VARCHAR(255) DEFAULT NULL, ADD UNIQUE INDEX UNIQ_EVENEMENT_ORIGINE_EXTERNE (origine, identifiant_externe)");
        $this->addSql("ALTER TABLE promotion ADD origine VARCHAR(30) NOT NULL DEFAULT 'manuelle', ADD identifiant_externe VARCHAR(255) DEFAULT NULL, ADD UNIQUE INDEX UNIQ_PROMOTION_ORIGINE_EXTERNE (origine, identifiant_externe)");
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE evenement DROP INDEX UNIQ_EVENEMENT_ORIGINE_EXTERNE, DROP origine, DROP identifiant_externe');
        $this->addSql('ALTER TABLE promotion DROP INDEX UNIQ_PROMOTION_ORIGINE_EXTERNE, DROP origine, DROP identifiant_externe');
    }
}
