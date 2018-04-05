<?php declare(strict_types = 1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20180322122127 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE news_tags (news_id INT NOT NULL, tags_id INT NOT NULL, INDEX IDX_BA6162ADB5A459A0 (news_id), INDEX IDX_BA6162AD8D7B4FB4 (tags_id), PRIMARY KEY(news_id, tags_id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE tags (id INT AUTO_INCREMENT NOT NULL, tag VARCHAR(255) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE news_tags ADD CONSTRAINT FK_BA6162ADB5A459A0 FOREIGN KEY (news_id) REFERENCES news (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE news_tags ADD CONSTRAINT FK_BA6162AD8D7B4FB4 FOREIGN KEY (tags_id) REFERENCES tags (id) ON DELETE CASCADE');
        // $this->addSql('DROP TABLE rates');
        $this->addSql('ALTER TABLE news ADD date DATETIME NOT NULL');
    }

    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE news_tags DROP FOREIGN KEY FK_BA6162AD8D7B4FB4');
        // $this->addSql('CREATE TABLE rates (exchange VARCHAR(20) NOT NULL COLLATE utf8_general_ci, source VARCHAR(20) NOT NULL COLLATE utf8_general_ci, base VARCHAR(10) NOT NULL COLLATE utf8_general_ci, quote VARCHAR(10) NOT NULL COLLATE utf8_general_ci, date DATETIME NOT NULL, period VARCHAR(10) NOT NULL COLLATE utf8_general_ci, price DOUBLE PRECISION NOT NULL, open DOUBLE PRECISION NOT NULL, high DOUBLE PRECISION NOT NULL, low DOUBLE PRECISION NOT NULL, close DOUBLE PRECISION NOT NULL, quantity DOUBLE PRECISION DEFAULT NULL, volume DOUBLE PRECISION DEFAULT NULL, trades BIGINT DEFAULT NULL, UNIQUE INDEX uni (source, exchange, base, quote, period, date), INDEX ix (source, exchange, base, quote, period, date)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('DROP TABLE news_tags');
        $this->addSql('DROP TABLE tags');
        $this->addSql('ALTER TABLE news DROP date');
    }
}
