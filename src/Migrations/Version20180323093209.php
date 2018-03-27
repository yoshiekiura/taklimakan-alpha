<?php declare(strict_types = 1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20180323093209 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        // $this->addSql('DROP TABLE rates');
        $this->addSql('ALTER TABLE news ADD active TINYINT(1) NOT NULL');
    }

    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        // $this->addSql('CREATE TABLE rates (exchange VARCHAR(20) NOT NULL COLLATE utf8_general_ci, source VARCHAR(20) NOT NULL COLLATE utf8_general_ci, base VARCHAR(10) NOT NULL COLLATE utf8_general_ci, quote VARCHAR(10) NOT NULL COLLATE utf8_general_ci, date DATETIME NOT NULL, period VARCHAR(10) NOT NULL COLLATE utf8_general_ci, price DOUBLE PRECISION NOT NULL, open DOUBLE PRECISION NOT NULL, high DOUBLE PRECISION NOT NULL, low DOUBLE PRECISION NOT NULL, close DOUBLE PRECISION NOT NULL, quantity DOUBLE PRECISION DEFAULT NULL, volume DOUBLE PRECISION DEFAULT NULL, trades BIGINT DEFAULT NULL, UNIQUE INDEX uni (source, exchange, base, quote, period, date), INDEX ix (source, exchange, base, quote, period, date)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE news DROP active');
    }
}
