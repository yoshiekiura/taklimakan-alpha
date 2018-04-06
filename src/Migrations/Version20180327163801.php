<?php declare(strict_types = 1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20180327163801 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE likes (id INT AUTO_INCREMENT NOT NULL, content_type VARCHAR(32) NOT NULL, user_id INT UNSIGNED NOT NULL, content_id INT UNSIGNED NOT NULL, count SMALLINT UNSIGNED DEFAULT 0 NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE news ADD source VARCHAR(255) NOT NULL');
        $this->addSql('ALTER TABLE tags CHANGE tag tag VARCHAR(32) NOT NULL');
    }

    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('DROP TABLE likes');
        $this->addSql('ALTER TABLE news DROP source');
        $this->addSql('ALTER TABLE tags CHANGE tag tag VARCHAR(255) NOT NULL COLLATE utf8_unicode_ci');
    }
}
