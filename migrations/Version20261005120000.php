<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20261005120000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Ajout des actualités éditoriales administrables.';
    }

    public function up(Schema $schema): void
    {
        $this->addSql("CREATE TABLE actualite (id INT AUTO_INCREMENT NOT NULL, titre VARCHAR(200) NOT NULL, slug VARCHAR(220) NOT NULL, categorie VARCHAR(100) DEFAULT NULL, chapeau VARCHAR(255) DEFAULT NULL, contenu LONGTEXT DEFAULT NULL, date_publication DATETIME NOT NULL, est_actif TINYINT NOT NULL, est_mis_en_avant TINYINT NOT NULL, date_creation DATETIME NOT NULL, date_modification DATETIME NOT NULL, image_media_id INT DEFAULT NULL, UNIQUE INDEX UNIQ_8B7D3B1A989D9B62 (slug), INDEX IDX_8B7D3B1A65F53DD (image_media_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB");
        $this->addSql('ALTER TABLE actualite ADD CONSTRAINT FK_8B7D3B1A65F53DD FOREIGN KEY (image_media_id) REFERENCES media (id) ON DELETE SET NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE actualite DROP FOREIGN KEY FK_8B7D3B1A65F53DD');
        $this->addSql('DROP TABLE actualite');
    }
}
