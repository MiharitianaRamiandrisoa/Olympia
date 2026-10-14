<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\ParameterType;
use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20261014113000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Remplace les images distantes de la galerie par les fichiers locaux';
    }

    public function up(Schema $schema): void
    {
        foreach ([
            ['aube-sur-tanjombato.jpg', 'aube-sur-tanjombato.jpg'],
            ['nuit-des-baobabs.jpg', 'nuit-des-baobabs.jpg'],
            ['vents-d-imerina.jpg', 'vents-d-imerina.jpg'],
            ['rizi-eres-matin.jpg', 'rizieres-matin.jpg'],
            ['colline-verte.jpg', 'colline-verte.jpg'],
            ['lune-d-antsirabe.jpg', 'lune-d-antsirabe.jpg'],
        ] as [$legacyFilename, $filename]) {
            $this->addSql(
                'UPDATE media SET chemin = ? WHERE nom_fichier = ?',
                ['images/galerie/'.$filename, $legacyFilename],
                [ParameterType::STRING, ParameterType::STRING],
            );
        }
    }

    public function down(Schema $schema): void
    {
        // Les chemins précédents pointaient vers des ressources en ligne et ne sont pas restaurés.
    }
}
