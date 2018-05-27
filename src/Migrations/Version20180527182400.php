<?php declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20180527182400 extends AbstractMigration
{
    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE ratings (id INT AUTO_INCREMENT NOT NULL, user_id INT UNSIGNED NOT NULL, content_type VARCHAR(32) DEFAULT NULL, content_id INT UNSIGNED NOT NULL, rating INT UNSIGNED DEFAULT NULL, date DATETIME DEFAULT CURRENT_TIMESTAMP, UNIQUE INDEX unique_like (content_type, content_id, user_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE courses ADD video VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE lectures ADD video VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE likes CHANGE status status TINYINT(1) DEFAULT NULL');
        $this->addSql('ALTER TABLE users CHANGE email email VARCHAR(255)  NOT NULL COLLATE utf8_unicode_ci');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('DROP TABLE ratings');
        $this->addSql('ALTER TABLE courses DROP video');
        $this->addSql('ALTER TABLE lectures DROP video');
        $this->addSql('ALTER TABLE likes CHANGE status status TINYINT(1) DEFAULT NULL');
        $this->addSql('ALTER TABLE users CHANGE email email VARCHAR(255) NOT NULL COLLATE utf8_unicode_ci');
    }
}
