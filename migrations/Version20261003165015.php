<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20261003165015 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Création du schéma initial Olympia pour le site public et le backoffice.';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE abonnement_newsletter (id INT AUTO_INCREMENT NOT NULL, email VARCHAR(180) NOT NULL, date_creation DATETIME NOT NULL, UNIQUE INDEX UNIQ_EC4FFF1DE7927C74 (email), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE boutique (id INT AUTO_INCREMENT NOT NULL, description LONGTEXT DEFAULT NULL, horaires JSON DEFAULT NULL, est_actif TINYINT NOT NULL, est_mis_en_avant TINYINT NOT NULL, date_creation DATETIME NOT NULL, date_modification DATETIME NOT NULL, enseigne_id INT NOT NULL, categorie_id INT DEFAULT NULL, photo_media_id INT DEFAULT NULL, INDEX IDX_A1223C546C2A0A71 (enseigne_id), INDEX IDX_A1223C54BCF5E72D (categorie_id), INDEX IDX_A1223C5479A3C2FF (photo_media_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE categorie_boutique (id INT AUTO_INCREMENT NOT NULL, nom VARCHAR(150) NOT NULL, slug VARCHAR(180) NOT NULL, est_actif TINYINT NOT NULL, UNIQUE INDEX UNIQ_99A57D3E989D9B62 (slug), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE categorie_evenement (id INT AUTO_INCREMENT NOT NULL, nom VARCHAR(150) NOT NULL, slug VARCHAR(180) NOT NULL, UNIQUE INDEX UNIQ_A6796719989D9B62 (slug), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE categorie_promotion (id INT AUTO_INCREMENT NOT NULL, nom VARCHAR(150) NOT NULL, slug VARCHAR(180) NOT NULL, est_actif TINYINT NOT NULL, UNIQUE INDEX UNIQ_6C4272D6989D9B62 (slug), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE categorie_restaurant (id INT AUTO_INCREMENT NOT NULL, nom VARCHAR(150) NOT NULL, slug VARCHAR(180) NOT NULL, est_actif TINYINT NOT NULL, UNIQUE INDEX UNIQ_755CD893989D9B62 (slug), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE enseigne (id INT AUTO_INCREMENT NOT NULL, nom VARCHAR(150) NOT NULL, slug VARCHAR(180) NOT NULL, description LONGTEXT DEFAULT NULL, telephone VARCHAR(50) DEFAULT NULL, email VARCHAR(180) DEFAULT NULL, site_web VARCHAR(255) DEFAULT NULL, facebook VARCHAR(255) DEFAULT NULL, instagram VARCHAR(255) DEFAULT NULL, est_actif TINYINT NOT NULL, date_creation DATETIME NOT NULL, date_modification DATETIME NOT NULL, logo_media_id INT DEFAULT NULL, UNIQUE INDEX UNIQ_37D4778E989D9B62 (slug), INDEX IDX_37D4778EBAAE86A3 (logo_media_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE evenement (id INT AUTO_INCREMENT NOT NULL, titre VARCHAR(200) NOT NULL, slug VARCHAR(220) NOT NULL, description LONGTEXT DEFAULT NULL, informations_complementaires LONGTEXT DEFAULT NULL, date_debut DATETIME NOT NULL, date_fin DATETIME DEFAULT NULL, lieu VARCHAR(255) DEFAULT NULL, est_actif TINYINT NOT NULL, est_mis_en_avant TINYINT NOT NULL, categorie_id INT DEFAULT NULL, enseigne_id INT DEFAULT NULL, image_media_id INT DEFAULT NULL, UNIQUE INDEX UNIQ_B26681E989D9B62 (slug), INDEX IDX_B26681EBCF5E72D (categorie_id), INDEX IDX_B26681E6C2A0A71 (enseigne_id), INDEX IDX_B26681E656F53DD (image_media_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE information_pratique (id INT AUTO_INCREMENT NOT NULL, type VARCHAR(100) NOT NULL, titre VARCHAR(200) NOT NULL, contenu LONGTEXT NOT NULL, ordre_affichage INT NOT NULL, est_actif TINYINT NOT NULL, date_creation DATETIME NOT NULL, date_modification DATETIME NOT NULL, PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE media (id INT AUTO_INCREMENT NOT NULL, nom_original VARCHAR(255) DEFAULT NULL, nom_fichier VARCHAR(255) NOT NULL, chemin VARCHAR(255) NOT NULL, type VARCHAR(255) NOT NULL, type_mime VARCHAR(255) NOT NULL, taille VARCHAR(255) DEFAULT NULL, texte_alternatif VARCHAR(255) DEFAULT NULL, est_actif TINYINT NOT NULL, date_creation DATETIME NOT NULL, date_modification DATETIME NOT NULL, PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE message_contact (id INT AUTO_INCREMENT NOT NULL, nom VARCHAR(150) NOT NULL, email VARCHAR(180) NOT NULL, telephone VARCHAR(50) DEFAULT NULL, sujet VARCHAR(200) DEFAULT NULL, message LONGTEXT NOT NULL, est_lu TINYINT NOT NULL, date_creation DATETIME NOT NULL, PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE promotion (id INT AUTO_INCREMENT NOT NULL, titre VARCHAR(200) NOT NULL, slug VARCHAR(220) NOT NULL, description LONGTEXT DEFAULT NULL, conditions LONGTEXT DEFAULT NULL, date_debut DATETIME NOT NULL, date_fin DATETIME DEFAULT NULL, est_actif TINYINT NOT NULL, est_mis_en_avant TINYINT NOT NULL, enseigne_id INT NOT NULL, categorie_id INT DEFAULT NULL, image_media_id INT DEFAULT NULL, UNIQUE INDEX UNIQ_C11D7DD1989D9B62 (slug), INDEX IDX_C11D7DD16C2A0A71 (enseigne_id), INDEX IDX_C11D7DD1BCF5E72D (categorie_id), INDEX IDX_C11D7DD1656F53DD (image_media_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE restaurant (id INT AUTO_INCREMENT NOT NULL, description LONGTEXT DEFAULT NULL, horaires JSON DEFAULT NULL, est_actif TINYINT NOT NULL, est_mis_en_avant TINYINT NOT NULL, enseigne_id INT NOT NULL, categorie_id INT DEFAULT NULL, photo_media_id INT DEFAULT NULL, INDEX IDX_EB95123F6C2A0A71 (enseigne_id), INDEX IDX_EB95123FBCF5E72D (categorie_id), INDEX IDX_EB95123F79A3C2FF (photo_media_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE service (id INT AUTO_INCREMENT NOT NULL, nom VARCHAR(150) NOT NULL, slug VARCHAR(180) NOT NULL, description LONGTEXT DEFAULT NULL, telephone VARCHAR(50) DEFAULT NULL, email VARCHAR(180) DEFAULT NULL, horaires VARCHAR(255) DEFAULT NULL, est_actif TINYINT NOT NULL, est_mis_en_avant TINYINT NOT NULL, date_creation DATETIME NOT NULL, date_modification DATETIME NOT NULL, photo_media_id INT DEFAULT NULL, UNIQUE INDEX UNIQ_E19D9AD2989D9B62 (slug), INDEX IDX_E19D9AD279A3C2FF (photo_media_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE utilisateur (id INT AUTO_INCREMENT NOT NULL, prenom VARCHAR(100) DEFAULT NULL, nom VARCHAR(100) NOT NULL, email VARCHAR(180) NOT NULL, mot_de_passe VARCHAR(255) NOT NULL, role VARCHAR(30) NOT NULL, est_actif TINYINT NOT NULL, date_creation DATETIME NOT NULL, date_modification DATETIME NOT NULL, UNIQUE INDEX UNIQ_1D1C63B3E7927C74 (email), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE messenger_messages (id BIGINT AUTO_INCREMENT NOT NULL, body LONGTEXT NOT NULL, headers LONGTEXT NOT NULL, queue_name VARCHAR(190) NOT NULL, created_at DATETIME NOT NULL, available_at DATETIME NOT NULL, delivered_at DATETIME DEFAULT NULL, INDEX IDX_75EA56E0FB7336F0E3BD61CE16BA31DBBF396750 (queue_name, available_at, delivered_at, id), PRIMARY KEY (id))');
        $this->addSql('ALTER TABLE boutique ADD CONSTRAINT FK_A1223C546C2A0A71 FOREIGN KEY (enseigne_id) REFERENCES enseigne (id) ON DELETE RESTRICT');
        $this->addSql('ALTER TABLE boutique ADD CONSTRAINT FK_A1223C54BCF5E72D FOREIGN KEY (categorie_id) REFERENCES categorie_boutique (id) ON DELETE SET NULL');
        $this->addSql('ALTER TABLE boutique ADD CONSTRAINT FK_A1223C5479A3C2FF FOREIGN KEY (photo_media_id) REFERENCES media (id) ON DELETE SET NULL');
        $this->addSql('ALTER TABLE enseigne ADD CONSTRAINT FK_37D4778EBAAE86A3 FOREIGN KEY (logo_media_id) REFERENCES media (id) ON DELETE SET NULL');
        $this->addSql('ALTER TABLE evenement ADD CONSTRAINT FK_B26681EBCF5E72D FOREIGN KEY (categorie_id) REFERENCES categorie_evenement (id) ON DELETE SET NULL');
        $this->addSql('ALTER TABLE evenement ADD CONSTRAINT FK_B26681E6C2A0A71 FOREIGN KEY (enseigne_id) REFERENCES enseigne (id) ON DELETE SET NULL');
        $this->addSql('ALTER TABLE evenement ADD CONSTRAINT FK_B26681E656F53DD FOREIGN KEY (image_media_id) REFERENCES media (id) ON DELETE SET NULL');
        $this->addSql('ALTER TABLE promotion ADD CONSTRAINT FK_C11D7DD16C2A0A71 FOREIGN KEY (enseigne_id) REFERENCES enseigne (id) ON DELETE RESTRICT');
        $this->addSql('ALTER TABLE promotion ADD CONSTRAINT FK_C11D7DD1BCF5E72D FOREIGN KEY (categorie_id) REFERENCES categorie_promotion (id) ON DELETE SET NULL');
        $this->addSql('ALTER TABLE promotion ADD CONSTRAINT FK_C11D7DD1656F53DD FOREIGN KEY (image_media_id) REFERENCES media (id) ON DELETE SET NULL');
        $this->addSql('ALTER TABLE restaurant ADD CONSTRAINT FK_EB95123F6C2A0A71 FOREIGN KEY (enseigne_id) REFERENCES enseigne (id) ON DELETE RESTRICT');
        $this->addSql('ALTER TABLE restaurant ADD CONSTRAINT FK_EB95123FBCF5E72D FOREIGN KEY (categorie_id) REFERENCES categorie_restaurant (id) ON DELETE SET NULL');
        $this->addSql('ALTER TABLE restaurant ADD CONSTRAINT FK_EB95123F79A3C2FF FOREIGN KEY (photo_media_id) REFERENCES media (id) ON DELETE SET NULL');
        $this->addSql('ALTER TABLE service ADD CONSTRAINT FK_E19D9AD279A3C2FF FOREIGN KEY (photo_media_id) REFERENCES media (id) ON DELETE SET NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE boutique DROP FOREIGN KEY FK_A1223C546C2A0A71');
        $this->addSql('ALTER TABLE boutique DROP FOREIGN KEY FK_A1223C54BCF5E72D');
        $this->addSql('ALTER TABLE boutique DROP FOREIGN KEY FK_A1223C5479A3C2FF');
        $this->addSql('ALTER TABLE enseigne DROP FOREIGN KEY FK_37D4778EBAAE86A3');
        $this->addSql('ALTER TABLE evenement DROP FOREIGN KEY FK_B26681EBCF5E72D');
        $this->addSql('ALTER TABLE evenement DROP FOREIGN KEY FK_B26681E6C2A0A71');
        $this->addSql('ALTER TABLE evenement DROP FOREIGN KEY FK_B26681E656F53DD');
        $this->addSql('ALTER TABLE promotion DROP FOREIGN KEY FK_C11D7DD16C2A0A71');
        $this->addSql('ALTER TABLE promotion DROP FOREIGN KEY FK_C11D7DD1BCF5E72D');
        $this->addSql('ALTER TABLE promotion DROP FOREIGN KEY FK_C11D7DD1656F53DD');
        $this->addSql('ALTER TABLE restaurant DROP FOREIGN KEY FK_EB95123F6C2A0A71');
        $this->addSql('ALTER TABLE restaurant DROP FOREIGN KEY FK_EB95123FBCF5E72D');
        $this->addSql('ALTER TABLE restaurant DROP FOREIGN KEY FK_EB95123F79A3C2FF');
        $this->addSql('ALTER TABLE service DROP FOREIGN KEY FK_E19D9AD279A3C2FF');
        $this->addSql('DROP TABLE abonnement_newsletter');
        $this->addSql('DROP TABLE boutique');
        $this->addSql('DROP TABLE categorie_boutique');
        $this->addSql('DROP TABLE categorie_evenement');
        $this->addSql('DROP TABLE categorie_promotion');
        $this->addSql('DROP TABLE categorie_restaurant');
        $this->addSql('DROP TABLE enseigne');
        $this->addSql('DROP TABLE evenement');
        $this->addSql('DROP TABLE information_pratique');
        $this->addSql('DROP TABLE media');
        $this->addSql('DROP TABLE message_contact');
        $this->addSql('DROP TABLE promotion');
        $this->addSql('DROP TABLE restaurant');
        $this->addSql('DROP TABLE service');
        $this->addSql('DROP TABLE utilisateur');
        $this->addSql('DROP TABLE messenger_messages');
    }
}
