<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20230218144715 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE chapter ADD delete_timestamp INT(11) DEFAULT NULL');
        $this->addSql('UPDATE chapter SET delete_timestamp = UNIX_TIMESTAMP() WHERE is_deleted = 1');
        $this->addSql('ALTER TABLE chapter DROP is_deleted');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE chapter ADD is_deleted TINYINT(1) NOT NULL');
        $this->addSql('UPDATE chapter SET is_deleted = 1 WHERE delete_timestamp IS NOT NULL');
        $this->addSql('ALTER TABLE chapter DROP delete_timestamp');
    }
}
