<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20261107110000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Permet de retenter l’envoi de bienvenue des abonnés newsletter tant qu’il a échoué.';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE abonnement_newsletter ADD welcome_email_sent_at DATETIME DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE abonnement_newsletter DROP welcome_email_sent_at');
    }
}
