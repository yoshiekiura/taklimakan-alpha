<?php declare(strict_types = 1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20180410172250 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE course (id INT AUTO_INCREMENT NOT NULL, category_id INT DEFAULT NULL, title VARCHAR(255) NOT NULL, lead LONGTEXT NOT NULL, text LONGTEXT NOT NULL, source VARCHAR(255) DEFAULT NULL, image VARCHAR(255) DEFAULT NULL, date DATETIME NOT NULL, tags VARCHAR(255) DEFAULT NULL, active TINYINT(1) DEFAULT \'0\' NOT NULL, level INT DEFAULT 0 NOT NULL, price NUMERIC(8, 2) NOT NULL, INDEX IDX_169E6FB912469DE2 (category_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE lecture (id INT AUTO_INCREMENT NOT NULL, title VARCHAR(255) NOT NULL, lead LONGTEXT NOT NULL, text LONGTEXT NOT NULL, source VARCHAR(255) DEFAULT NULL, image VARCHAR(255) DEFAULT NULL, date DATETIME NOT NULL, tags VARCHAR(255) DEFAULT NULL, active TINYINT(1) DEFAULT \'0\' NOT NULL, type VARCHAR(32) NOT NULL, price NUMERIC(8, 2) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE course ADD CONSTRAINT FK_169E6FB912469DE2 FOREIGN KEY (category_id) REFERENCES category (id)');
    }

    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('DROP TABLE course');
        $this->addSql('DROP TABLE lecture');
    }
}
