<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20261010100000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Ajoute la structure de gestion de la galerie Olympia';
    }

    public function up(Schema $schema): void
    {
        $this->addSql("CREATE TABLE artiste (id INT AUTO_INCREMENT NOT NULL, nom VARCHAR(150) NOT NULL, biographie LONGTEXT DEFAULT NULL, specialite VARCHAR(150) DEFAULT NULL, email VARCHAR(180) DEFAULT NULL, site_web VARCHAR(255) DEFAULT NULL, facebook VARCHAR(255) DEFAULT NULL, instagram VARCHAR(255) DEFAULT NULL, est_actif TINYINT(1) NOT NULL, date_creation DATETIME NOT NULL, date_modification DATETIME NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB");
        $this->addSql("CREATE TABLE categorie_oeuvre (id INT AUTO_INCREMENT NOT NULL, nom VARCHAR(150) NOT NULL, slug VARCHAR(180) NOT NULL, est_actif TINYINT(1) NOT NULL, UNIQUE INDEX UNIQ_CATEGORIE_OEUVRE_SLUG (slug), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB");
        $this->addSql("CREATE TABLE exposition (id INT AUTO_INCREMENT NOT NULL, image_media_id BIGINT UNSIGNED DEFAULT NULL, titre VARCHAR(180) NOT NULL, slug VARCHAR(180) NOT NULL, description LONGTEXT DEFAULT NULL, lieu VARCHAR(180) DEFAULT NULL, date_debut DATE NOT NULL, date_fin DATE DEFAULT NULL, est_active TINYINT(1) NOT NULL, est_mise_en_avant TINYINT(1) NOT NULL, UNIQUE INDEX UNIQ_EXPOSITION_SLUG (slug), INDEX IDX_EXPOSITION_IMAGE_MEDIA (image_media_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB");
        $this->addSql("CREATE TABLE oeuvre (id INT AUTO_INCREMENT NOT NULL, artiste_id INT DEFAULT NULL, categorie_id INT DEFAULT NULL, exposition_id INT DEFAULT NULL, image_media_id BIGINT UNSIGNED DEFAULT NULL, titre VARCHAR(180) NOT NULL, slug VARCHAR(180) NOT NULL, description LONGTEXT DEFAULT NULL, technique VARCHAR(180) DEFAULT NULL, dimensions VARCHAR(100) DEFAULT NULL, annee INT DEFAULT NULL, ordre INT NOT NULL, est_active TINYINT(1) NOT NULL, est_mise_en_avant TINYINT(1) NOT NULL, date_creation DATETIME NOT NULL, date_modification DATETIME NOT NULL, UNIQUE INDEX UNIQ_OEUVRE_SLUG (slug), INDEX IDX_OEUVRE_ARTISTE (artiste_id), INDEX IDX_OEUVRE_CATEGORIE (categorie_id), INDEX IDX_OEUVRE_EXPOSITION (exposition_id), INDEX IDX_OEUVRE_IMAGE_MEDIA (image_media_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB");
        $this->addSql('ALTER TABLE exposition ADD CONSTRAINT FK_EXPOSITION_IMAGE_MEDIA FOREIGN KEY (image_media_id) REFERENCES media (id) ON DELETE SET NULL');
        $this->addSql('ALTER TABLE oeuvre ADD CONSTRAINT FK_OEUVRE_ARTISTE FOREIGN KEY (artiste_id) REFERENCES artiste (id) ON DELETE SET NULL');
        $this->addSql('ALTER TABLE oeuvre ADD CONSTRAINT FK_OEUVRE_CATEGORIE FOREIGN KEY (categorie_id) REFERENCES categorie_oeuvre (id) ON DELETE SET NULL');
        $this->addSql('ALTER TABLE oeuvre ADD CONSTRAINT FK_OEUVRE_EXPOSITION FOREIGN KEY (exposition_id) REFERENCES exposition (id) ON DELETE SET NULL');
        $this->addSql('ALTER TABLE oeuvre ADD CONSTRAINT FK_OEUVRE_IMAGE_MEDIA FOREIGN KEY (image_media_id) REFERENCES media (id) ON DELETE SET NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE oeuvre DROP FOREIGN KEY FK_OEUVRE_ARTISTE');
        $this->addSql('ALTER TABLE oeuvre DROP FOREIGN KEY FK_OEUVRE_CATEGORIE');
        $this->addSql('ALTER TABLE oeuvre DROP FOREIGN KEY FK_OEUVRE_EXPOSITION');
        $this->addSql('ALTER TABLE oeuvre DROP FOREIGN KEY FK_OEUVRE_IMAGE_MEDIA');
        $this->addSql('ALTER TABLE exposition DROP FOREIGN KEY FK_EXPOSITION_IMAGE_MEDIA');
        $this->addSql('DROP TABLE oeuvre');
        $this->addSql('DROP TABLE exposition');
        $this->addSql('DROP TABLE categorie_oeuvre');
        $this->addSql('DROP TABLE artiste');
    }
}
