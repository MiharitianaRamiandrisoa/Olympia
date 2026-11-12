<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20261112120000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Ajout des galeries et des plats des restaurants.';
    }

    public function up(Schema $schema): void
    {
        $this->addSql("CREATE TABLE restaurant_photo (id INT AUTO_INCREMENT NOT NULL, restaurant_id INT NOT NULL, image_media_id INT NOT NULL, legende VARCHAR(255) DEFAULT NULL, ordre INT NOT NULL, est_actif TINYINT(1) NOT NULL, INDEX IDX_RESTAURANT_PHOTO_RESTAURANT (restaurant_id), INDEX IDX_RESTAURANT_PHOTO_ORDRE (restaurant_id, ordre), INDEX IDX_RESTAURANT_PHOTO_MEDIA (image_media_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB");
        $this->addSql('ALTER TABLE restaurant_photo ADD CONSTRAINT FK_RESTAURANT_PHOTO_RESTAURANT FOREIGN KEY (restaurant_id) REFERENCES restaurant (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE restaurant_photo ADD CONSTRAINT FK_RESTAURANT_PHOTO_MEDIA FOREIGN KEY (image_media_id) REFERENCES media (id) ON DELETE CASCADE');

        $this->addSql("CREATE TABLE restaurant_plat (id INT AUTO_INCREMENT NOT NULL, restaurant_id INT NOT NULL, image_media_id INT DEFAULT NULL, nom VARCHAR(255) NOT NULL, description LONGTEXT DEFAULT NULL, prix NUMERIC(10, 2) DEFAULT NULL, ordre INT NOT NULL, est_actif TINYINT(1) NOT NULL, INDEX IDX_RESTAURANT_PLAT_RESTAURANT (restaurant_id), INDEX IDX_RESTAURANT_PLAT_ORDRE (restaurant_id, ordre), INDEX IDX_RESTAURANT_PLAT_MEDIA (image_media_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB");
        $this->addSql('ALTER TABLE restaurant_plat ADD CONSTRAINT FK_RESTAURANT_PLAT_RESTAURANT FOREIGN KEY (restaurant_id) REFERENCES restaurant (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE restaurant_plat ADD CONSTRAINT FK_RESTAURANT_PLAT_MEDIA FOREIGN KEY (image_media_id) REFERENCES media (id) ON DELETE SET NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE restaurant_photo');
        $this->addSql('DROP TABLE restaurant_plat');
    }
}
