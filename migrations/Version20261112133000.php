<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20261112133000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Ajout de la galerie de photos des boutiques.';
    }

    public function up(Schema $schema): void
    {
        $this->addSql("CREATE TABLE boutique_photo (id INT AUTO_INCREMENT NOT NULL, boutique_id INT NOT NULL, image_media_id INT NOT NULL, legende VARCHAR(255) DEFAULT NULL, ordre INT NOT NULL, est_actif TINYINT(1) NOT NULL, INDEX IDX_BOUTIQUE_PHOTO_BOUTIQUE (boutique_id), INDEX IDX_BOUTIQUE_PHOTO_ORDRE (boutique_id, ordre), INDEX IDX_BOUTIQUE_PHOTO_MEDIA (image_media_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB");
        $this->addSql('ALTER TABLE boutique_photo ADD CONSTRAINT FK_BOUTIQUE_PHOTO_BOUTIQUE FOREIGN KEY (boutique_id) REFERENCES boutique (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE boutique_photo ADD CONSTRAINT FK_BOUTIQUE_PHOTO_MEDIA FOREIGN KEY (image_media_id) REFERENCES media (id) ON DELETE CASCADE');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE boutique_photo');
    }
}
