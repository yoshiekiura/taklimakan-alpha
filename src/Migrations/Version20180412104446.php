<?php declare(strict_types = 1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20180412104446 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('DROP TABLE analytics_tags');
        $this->addSql('DROP TABLE news_tags');
        $this->addSql('ALTER TABLE analytics ADD tags VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE news DROP FOREIGN KEY FK_1DD3995012469DE2');
        $this->addSql('DROP INDEX IDX_1DD3995012469DE2 ON news');
        $this->addSql('ALTER TABLE news ADD tags VARCHAR(255) DEFAULT NULL, DROP category_id');
    }

    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE analytics_tags (analytics_id INT NOT NULL, tags_id INT NOT NULL, INDEX IDX_76A158C2F4297814 (analytics_id), INDEX IDX_76A158C28D7B4FB4 (tags_id), PRIMARY KEY(analytics_id, tags_id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE news_tags (news_id INT NOT NULL, tags_id INT NOT NULL, INDEX IDX_BA6162ADB5A459A0 (news_id), INDEX IDX_BA6162AD8D7B4FB4 (tags_id), PRIMARY KEY(news_id, tags_id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE analytics_tags ADD CONSTRAINT FK_76A158C28D7B4FB4 FOREIGN KEY (tags_id) REFERENCES tags (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE analytics_tags ADD CONSTRAINT FK_76A158C2F4297814 FOREIGN KEY (analytics_id) REFERENCES analytics (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE news_tags ADD CONSTRAINT FK_BA6162AD8D7B4FB4 FOREIGN KEY (tags_id) REFERENCES tags (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE news_tags ADD CONSTRAINT FK_BA6162ADB5A459A0 FOREIGN KEY (news_id) REFERENCES news (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE analytics DROP tags');
        $this->addSql('ALTER TABLE news ADD category_id INT DEFAULT NULL, DROP tags');
        $this->addSql('ALTER TABLE news ADD CONSTRAINT FK_1DD3995012469DE2 FOREIGN KEY (category_id) REFERENCES categories (id)');
        $this->addSql('CREATE INDEX IDX_1DD3995012469DE2 ON news (category_id)');
    }
}
