<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240330210040 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE padel_game ADD mode VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE padel_game ADD winner VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE padel_game DROP points');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('ALTER TABLE padel_game ADD points INT DEFAULT NULL');
        $this->addSql('ALTER TABLE padel_game DROP mode');
        $this->addSql('ALTER TABLE padel_game DROP winner');
    }
}
