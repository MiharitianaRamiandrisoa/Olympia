<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20261109120000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Ajout de la provenance et des identifiants externes des actualites.';
    }

    public function up(Schema $schema): void
    {
        $this->addSql("ALTER TABLE actualite ADD origine VARCHAR(30) NOT NULL DEFAULT 'manuelle', ADD identifiant_externe VARCHAR(255) DEFAULT NULL, ADD url_externe VARCHAR(500) DEFAULT NULL, ADD url_image_externe VARCHAR(1000) DEFAULT NULL");
        $this->addSql('CREATE UNIQUE INDEX UNIQ_ACTUALITE_ORIGINE_EXTERNE ON actualite (origine, identifiant_externe)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP INDEX UNIQ_ACTUALITE_ORIGINE_EXTERNE ON actualite');
        $this->addSql('ALTER TABLE actualite DROP origine, DROP identifiant_externe, DROP url_externe, DROP url_image_externe');
    }
}
