<?php declare(strict_types = 1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20180423110026 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

//        $this->addSql('DROP TABLE course');
  //      $this->addSql('DROP TABLE lecture');
    //    $this->addSql('DROP INDEX IDX_EAC2E68812469DE2 ON analytics');
//        $this->addSql('ALTER TABLE analytics DROP category_id');
  //      $this->addSql('ALTER TABLE courses DROP FOREIGN KEY FK_A9A55A4C12469DE2');
    //    $this->addSql('DROP INDEX IDX_A9A55A4C12469DE2 ON courses');
//        $this->addSql('ALTER TABLE courses DROP category_id');
        $this->addSql('CREATE UNIQUE INDEX source_idx ON news (source)');
    }

    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE course (id INT AUTO_INCREMENT NOT NULL, category_id INT DEFAULT NULL, title VARCHAR(255) NOT NULL COLLATE utf8_unicode_ci, lead LONGTEXT NOT NULL COLLATE utf8_unicode_ci, text LONGTEXT NOT NULL COLLATE utf8_unicode_ci, source VARCHAR(255) DEFAULT NULL COLLATE utf8_unicode_ci, image VARCHAR(255) DEFAULT NULL COLLATE utf8_unicode_ci, date DATETIME NOT NULL, tags VARCHAR(255) DEFAULT NULL COLLATE utf8_unicode_ci, active TINYINT(1) DEFAULT \'0\' NOT NULL, level INT DEFAULT 0 NOT NULL, price NUMERIC(8, 2) NOT NULL, INDEX IDX_169E6FB912469DE2 (category_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE lecture (id INT AUTO_INCREMENT NOT NULL, title VARCHAR(255) NOT NULL COLLATE utf8_unicode_ci, lead LONGTEXT NOT NULL COLLATE utf8_unicode_ci, text LONGTEXT NOT NULL COLLATE utf8_unicode_ci, source VARCHAR(255) DEFAULT NULL COLLATE utf8_unicode_ci, image VARCHAR(255) DEFAULT NULL COLLATE utf8_unicode_ci, date DATETIME NOT NULL, tags VARCHAR(255) DEFAULT NULL COLLATE utf8_unicode_ci, active TINYINT(1) DEFAULT \'0\' NOT NULL, type VARCHAR(32) NOT NULL COLLATE utf8_unicode_ci, price NUMERIC(8, 2) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE analytics ADD category_id INT DEFAULT NULL');
        $this->addSql('CREATE INDEX IDX_EAC2E68812469DE2 ON analytics (category_id)');
        $this->addSql('ALTER TABLE courses ADD category_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE courses ADD CONSTRAINT FK_A9A55A4C12469DE2 FOREIGN KEY (category_id) REFERENCES categories (id)');
        $this->addSql('CREATE INDEX IDX_A9A55A4C12469DE2 ON courses (category_id)');
        $this->addSql('DROP INDEX source_idx ON news');
    }
}
