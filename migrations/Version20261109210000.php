<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20261109210000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Ajout de la galerie d images des actualites.';
    }

    public function up(Schema $schema): void
    {
        $this->addSql("CREATE TABLE actualite_image (id INT AUTO_INCREMENT NOT NULL, actualite_id INT NOT NULL, media_id INT NOT NULL, url_externe VARCHAR(1000) NOT NULL, ordre INT NOT NULL, INDEX IDX_ACTUALITE_IMAGE_ACTUALITE (actualite_id), INDEX IDX_ACTUALITE_IMAGE_ORDRE (actualite_id, ordre), INDEX IDX_ACTUALITE_IMAGE_MEDIA (media_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB");
        $this->addSql('ALTER TABLE actualite_image ADD CONSTRAINT FK_ACTUALITE_IMAGE_ACTUALITE FOREIGN KEY (actualite_id) REFERENCES actualite (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE actualite_image ADD CONSTRAINT FK_ACTUALITE_IMAGE_MEDIA FOREIGN KEY (media_id) REFERENCES media (id) ON DELETE CASCADE');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE actualite_image DROP FOREIGN KEY FK_ACTUALITE_IMAGE_ACTUALITE');
        $this->addSql('ALTER TABLE actualite_image DROP FOREIGN KEY FK_ACTUALITE_IMAGE_MEDIA');
        $this->addSql('DROP TABLE actualite_image');
    }
}
