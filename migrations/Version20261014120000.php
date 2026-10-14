<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20261014120000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Met à jour les informations du centre selon la charte officielle Olympia';
    }

    public function up(Schema $schema): void
    {
        $this->addSql(
            'UPDATE information_pratique SET contenu = ? WHERE type = ?',
            ["Lundi au Jeudi : 9h - 20h\nVendredi et Samedi : 9h - 21h\nDimanche : 9h - 15h", 'horaires'],
        );
        $this->addSql(
            'UPDATE information_pratique SET contenu = ? WHERE type = ?',
            ["Centre commercial OLYMPIA\nTanjombato, Antananarivo 102, Madagascar\n(en face du pont Tanjombato)", 'adresse'],
        );
    }

    public function down(Schema $schema): void
    {
        // Les informations précédentes ne sont pas restaurées car elles n'étaient pas officielles.
    }
}
