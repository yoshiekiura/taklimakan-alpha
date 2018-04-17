<?php declare(strict_types = 1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20180416132226 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE joiner CHANGE from_type from_type VARCHAR(32) DEFAULT NULL, CHANGE to_type to_type VARCHAR(32) DEFAULT NULL');
        $this->addSql('ALTER TABLE lectures CHANGE type type VARCHAR(32) DEFAULT NULL');
    }

    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE joiner CHANGE from_type from_type VARCHAR(16) NOT NULL COLLATE utf8mb4_unicode_ci, CHANGE to_type to_type VARCHAR(16) NOT NULL COLLATE utf8mb4_unicode_ci');
        $this->addSql('ALTER TABLE lectures CHANGE type type VARCHAR(32) NOT NULL COLLATE utf8mb4_unicode_ci');
    }
}
