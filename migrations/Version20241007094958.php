<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20241007094958 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE booking DROP CONSTRAINT fk_e00cedde54177093');
        $this->addSql('DROP INDEX uniq_e00cedde54177093');
        $this->addSql('ALTER TABLE booking ADD bed_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE booking ADD user_email VARCHAR(255) NOT NULL');
        $this->addSql('ALTER TABLE booking DROP room_id');
        $this->addSql('ALTER TABLE booking DROP date_start');
        $this->addSql('ALTER TABLE booking DROP date_end');
        $this->addSql('ALTER TABLE booking ADD CONSTRAINT FK_E00CEDDE88688BB9 FOREIGN KEY (bed_id) REFERENCES bed (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('CREATE INDEX IDX_E00CEDDE88688BB9 ON booking (bed_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('ALTER TABLE booking DROP CONSTRAINT FK_E00CEDDE88688BB9');
        $this->addSql('DROP INDEX IDX_E00CEDDE88688BB9');
        $this->addSql('ALTER TABLE booking ADD room_id INT NOT NULL');
        $this->addSql('ALTER TABLE booking ADD date_start DATE NOT NULL');
        $this->addSql('ALTER TABLE booking ADD date_end DATE NOT NULL');
        $this->addSql('ALTER TABLE booking DROP bed_id');
        $this->addSql('ALTER TABLE booking DROP user_email');
        $this->addSql('ALTER TABLE booking ADD CONSTRAINT fk_e00cedde54177093 FOREIGN KEY (room_id) REFERENCES room (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('CREATE UNIQUE INDEX uniq_e00cedde54177093 ON booking (room_id)');
    }
}
