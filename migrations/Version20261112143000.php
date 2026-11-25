<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20261112143000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Ajout des descriptions aux photos des boutiques et restaurants.';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE boutique_photo ADD description LONGTEXT DEFAULT NULL');
        $this->addSql('ALTER TABLE restaurant_photo ADD description LONGTEXT DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE boutique_photo DROP description');
        $this->addSql('ALTER TABLE restaurant_photo DROP description');
    }
}
