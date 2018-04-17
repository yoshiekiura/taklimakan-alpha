<?php declare(strict_types = 1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20180417102639 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE courses CHANGE lead lead LONGTEXT DEFAULT NULL, CHANGE text text LONGTEXT DEFAULT NULL');
        $this->addSql('ALTER TABLE lectures CHANGE lead lead LONGTEXT DEFAULT NULL, CHANGE text text LONGTEXT DEFAULT NULL');
        $this->addSql('ALTER TABLE news CHANGE lead lead LONGTEXT DEFAULT NULL, CHANGE text text LONGTEXT DEFAULT NULL');
    }

    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE courses CHANGE lead lead LONGTEXT NOT NULL COLLATE utf8mb4_unicode_ci, CHANGE text text LONGTEXT NOT NULL COLLATE utf8mb4_unicode_ci');
        $this->addSql('ALTER TABLE lectures CHANGE lead lead LONGTEXT NOT NULL COLLATE utf8mb4_unicode_ci, CHANGE text text LONGTEXT NOT NULL COLLATE utf8mb4_unicode_ci');
        $this->addSql('ALTER TABLE news CHANGE lead lead LONGTEXT NOT NULL COLLATE utf8mb4_unicode_ci, CHANGE text text LONGTEXT NOT NULL COLLATE utf8mb4_unicode_ci');
    }
}
