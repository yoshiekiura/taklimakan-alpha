<?php declare(strict_types = 1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20180328103314 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE comments (id INT AUTO_INCREMENT NOT NULL, content_type VARCHAR(32) NOT NULL, content_id INT UNSIGNED NOT NULL, user_id INT UNSIGNED NOT NULL, text LONGTEXT NOT NULL, active TINYINT(1) DEFAULT \'1\' NOT NULL, date DATETIME DEFAULT \'0\' NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('DROP TABLE comment');
    }

    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE comment (id INT AUTO_INCREMENT NOT NULL, content_type VARCHAR(32) NOT NULL COLLATE utf8_unicode_ci, content_id INT UNSIGNED NOT NULL, user_id INT UNSIGNED NOT NULL, text LONGTEXT NOT NULL COLLATE utf8_unicode_ci, active TINYINT(1) DEFAULT \'1\' NOT NULL, date DATETIME DEFAULT \'0000-00-00 00:00:00\' NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('DROP TABLE comments');
    }
}
