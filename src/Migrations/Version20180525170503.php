<?php declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20180525170503 extends AbstractMigration
{
    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE likes CHANGE status status TINYINT(1) DEFAULT NULL');
        $this->addSql('CREATE UNIQUE INDEX unique_like ON likes (content_type, content_id, user_id)');
        $this->addSql('ALTER TABLE users CHANGE email email VARCHAR(255)  NOT NULL COLLATE utf8_unicode_ci');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('DROP INDEX unique_like ON likes');
        $this->addSql('ALTER TABLE likes CHANGE status status TINYINT(1) DEFAULT NULL');
        $this->addSql('ALTER TABLE users CHANGE email email VARCHAR(255) NOT NULL COLLATE utf8_unicode_ci');
    }
}
