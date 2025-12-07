<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20251206110001 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE file_upload ADD input_format VARCHAR(255) NOT NULL');
        $this->addSql('ALTER TABLE file_upload ADD requested_output_format VARCHAR(255) NOT NULL');
        $this->addSql('ALTER TABLE file_upload ADD links JSON DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE file_upload DROP input_format');
        $this->addSql('ALTER TABLE file_upload DROP requested_output_format');
        $this->addSql('ALTER TABLE file_upload DROP links');
    }
}
