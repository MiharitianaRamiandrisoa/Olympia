<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20261117130000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Ajoute le suivi de l’envoi des newsletters liées aux actualités.';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE actualite ADD newsletter_envoyee_le DATETIME DEFAULT NULL');
        // Les actualités existantes ne doivent pas être envoyées rétroactivement.
        $this->addSql('UPDATE actualite SET newsletter_envoyee_le = date_modification WHERE newsletter_envoyee_le IS NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE actualite DROP newsletter_envoyee_le');
    }
}
