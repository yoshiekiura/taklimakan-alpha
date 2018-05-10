<?php declare(strict_types = 1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20180510074435 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE journal (id INT AUTO_INCREMENT NOT NULL, admin_id INT DEFAULT NULL, user_id INT NOT NULL, created_at DATETIME NOT NULL, action SMALLINT NOT NULL, data LONGTEXT NOT NULL COMMENT \'(DC2Type:array)\', INDEX IDX_C1A7E74D642B8210 (admin_id), INDEX IDX_C1A7E74DA76ED395 (user_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE journal ADD CONSTRAINT FK_C1A7E74D642B8210 FOREIGN KEY (admin_id) REFERENCES users (id)');
        $this->addSql('ALTER TABLE journal ADD CONSTRAINT FK_C1A7E74DA76ED395 FOREIGN KEY (user_id) REFERENCES users (id)');
    }

    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('DROP TABLE journal');
    }
}
