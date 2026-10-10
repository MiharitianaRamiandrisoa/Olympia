<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20261010120000 extends AbstractMigration
{
    public function getDescription(): string { return 'Enregistre les dimensions des images pour conserver leurs proportions'; }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE media ADD largeur INT DEFAULT NULL, ADD hauteur INT DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE media DROP largeur, DROP hauteur');
    }
}
