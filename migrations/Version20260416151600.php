<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Migration pour ajouter la colonne resultat à la table diagnostic
 */
final class Version20260416151600 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add resultat column to diagnostic table for storing AI analysis results';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE diagnostic ADD resultat LONGTEXT DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE diagnostic DROP resultat');
    }
}

