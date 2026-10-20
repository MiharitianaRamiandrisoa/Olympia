<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20261014150000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Ajoute la billetterie Olympia : reservations, billets et controles d entree.';
    }

    public function up(Schema $schema): void
    {
        $this->addSql("ALTER TABLE evenement ADD capacite_maximale INT DEFAULT NULL, ADD date_ouverture_reservations DATETIME DEFAULT NULL, ADD date_cloture_reservations DATETIME DEFAULT NULL, ADD type_billetterie VARCHAR(20) NOT NULL DEFAULT 'gratuit', ADD tarif NUMERIC(10, 2) DEFAULT NULL");
        $this->addSql("CREATE TABLE reservation (id INT AUTO_INCREMENT NOT NULL, evenement_id INT NOT NULL, reference VARCHAR(32) NOT NULL, nom VARCHAR(160) NOT NULL, email VARCHAR(180) NOT NULL, telephone VARCHAR(40) DEFAULT NULL, nombre_places INT NOT NULL, statut VARCHAR(32) NOT NULL, statut_paiement VARCHAR(32) NOT NULL, jeton_acces_hash VARCHAR(64) NOT NULL, date_creation DATETIME NOT NULL, date_confirmation DATETIME DEFAULT NULL, UNIQUE INDEX UNIQ_42C84955AEA34913 (reference), UNIQUE INDEX UNIQ_42C84955BBBDAEB3 (jeton_acces_hash), INDEX idx_reservation_event_status (evenement_id, statut), INDEX IDX_42C84955FD02F13 (evenement_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB");
        $this->addSql("CREATE TABLE billet (id INT AUTO_INCREMENT NOT NULL, reservation_id INT NOT NULL, reference VARCHAR(28) NOT NULL, jeton VARCHAR(64) NOT NULL, statut VARCHAR(20) NOT NULL, date_emission DATETIME NOT NULL, date_utilisation DATETIME DEFAULT NULL, version INT DEFAULT 1 NOT NULL, UNIQUE INDEX UNIQ_1F034AF6AEA34913 (reference), UNIQUE INDEX UNIQ_1F034AF62CF647B (jeton), INDEX idx_billet_statut (statut), INDEX IDX_1F034AF6B83297E7 (reservation_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB");
        $this->addSql("CREATE TABLE controle_entree (id INT AUTO_INCREMENT NOT NULL, billet_id INT DEFAULT NULL, utilisateur_id INT DEFAULT NULL, resultat VARCHAR(24) NOT NULL, cle_succes VARCHAR(64) DEFAULT NULL, date_controle DATETIME NOT NULL, UNIQUE INDEX uniq_controle_entree_succes (cle_succes), INDEX idx_controle_date (date_controle), INDEX IDX_14A25C3C44973C78 (billet_id), INDEX IDX_14A25C3CFB88E14F (utilisateur_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB");
        $this->addSql('ALTER TABLE reservation ADD CONSTRAINT FK_RESERVATION_EVENT FOREIGN KEY (evenement_id) REFERENCES evenement (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE billet ADD CONSTRAINT FK_BILLET_RESERVATION FOREIGN KEY (reservation_id) REFERENCES reservation (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE controle_entree ADD CONSTRAINT FK_CONTROLE_BILLET FOREIGN KEY (billet_id) REFERENCES billet (id) ON DELETE SET NULL');
        $this->addSql('ALTER TABLE controle_entree ADD CONSTRAINT FK_CONTROLE_USER FOREIGN KEY (utilisateur_id) REFERENCES utilisateur (id) ON DELETE SET NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE controle_entree DROP FOREIGN KEY FK_CONTROLE_BILLET');
        $this->addSql('ALTER TABLE controle_entree DROP FOREIGN KEY FK_CONTROLE_USER');
        $this->addSql('ALTER TABLE billet DROP FOREIGN KEY FK_BILLET_RESERVATION');
        $this->addSql('ALTER TABLE reservation DROP FOREIGN KEY FK_RESERVATION_EVENT');
        $this->addSql('DROP TABLE controle_entree');
        $this->addSql('DROP TABLE billet');
        $this->addSql('DROP TABLE reservation');
        $this->addSql('ALTER TABLE evenement DROP capacite_maximale, DROP date_ouverture_reservations, DROP date_cloture_reservations, DROP type_billetterie, DROP tarif');
    }
}
