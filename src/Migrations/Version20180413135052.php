<?php declare(strict_types = 1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20180413135052 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE lectures ADD provider_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE lectures ADD CONSTRAINT FK_63C861D0A53A8AA FOREIGN KEY (provider_id) REFERENCES providers (id)');
        $this->addSql('CREATE INDEX IDX_63C861D0A53A8AA ON lectures (provider_id)');
    }

    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE lectures DROP FOREIGN KEY FK_63C861D0A53A8AA');
        $this->addSql('DROP INDEX IDX_63C861D0A53A8AA ON lectures');
        $this->addSql('ALTER TABLE lectures DROP provider_id');
    }
}
