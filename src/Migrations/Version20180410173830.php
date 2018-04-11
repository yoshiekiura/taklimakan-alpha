<?php declare(strict_types = 1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20180410173830 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE course CHANGE level level INT DEFAULT 0, CHANGE price price NUMERIC(8, 2) DEFAULT \'0\' NOT NULL');
        $this->addSql('ALTER TABLE lecture CHANGE price price NUMERIC(8, 2) DEFAULT \'0\', CHANGE level level INT DEFAULT 0');
    }

    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE course CHANGE level level INT DEFAULT 0 NOT NULL, CHANGE price price NUMERIC(8, 2) NOT NULL');
        $this->addSql('ALTER TABLE lecture CHANGE price price NUMERIC(8, 2) NOT NULL, CHANGE level level INT DEFAULT 0 NOT NULL');
    }
}
