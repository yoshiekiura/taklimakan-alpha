<?php declare(strict_types = 1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20180328123001 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE analytics (id INT AUTO_INCREMENT NOT NULL, category_id INT DEFAULT NULL, title VARCHAR(255) NOT NULL, lead LONGTEXT NOT NULL, text LONGTEXT NOT NULL, source VARCHAR(255) NOT NULL, image VARCHAR(255) NOT NULL, date DATETIME NOT NULL, active TINYINT(1) DEFAULT \'0\' NOT NULL, INDEX IDX_EAC2E68812469DE2 (category_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE analytics_tags (analytics_id INT NOT NULL, tags_id INT NOT NULL, INDEX IDX_76A158C2F4297814 (analytics_id), INDEX IDX_76A158C28D7B4FB4 (tags_id), PRIMARY KEY(analytics_id, tags_id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE analytics ADD CONSTRAINT FK_EAC2E68812469DE2 FOREIGN KEY (category_id) REFERENCES category (id)');
        $this->addSql('ALTER TABLE analytics_tags ADD CONSTRAINT FK_76A158C2F4297814 FOREIGN KEY (analytics_id) REFERENCES analytics (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE analytics_tags ADD CONSTRAINT FK_76A158C28D7B4FB4 FOREIGN KEY (tags_id) REFERENCES tags (id) ON DELETE CASCADE');
    }

    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE analytics_tags DROP FOREIGN KEY FK_76A158C2F4297814');
        $this->addSql('DROP TABLE analytics');
        $this->addSql('DROP TABLE analytics_tags');
    }
}
