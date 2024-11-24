<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20241123235842 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SEQUENCE markers_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE TABLE markers (id INT NOT NULL, user_id INT DEFAULT NULL, uid UUID NOT NULL, name VARCHAR(256) DEFAULT NULL, type VARCHAR(64) DEFAULT NULL, latitude VARCHAR(32) NOT NULL, longitude VARCHAR(32) NOT NULL, short_description TEXT DEFAULT NULL, description TEXT DEFAULT NULL, image_url VARCHAR(256) DEFAULT NULL, is_active BOOLEAN NOT NULL, is_paper BOOLEAN NOT NULL, is_glass BOOLEAN NOT NULL, is_plastic BOOLEAN NOT NULL, is_cloth BOOLEAN NOT NULL, is_electronic BOOLEAN NOT NULL, is_battery BOOLEAN NOT NULL, is_green_point BOOLEAN NOT NULL, is_multibox BOOLEAN NOT NULL, status VARCHAR(16) NOT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, updated_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_4189DF30539B0606 ON markers (uid)');
        $this->addSql('CREATE INDEX IDX_4189DF30A76ED395 ON markers (user_id)');
        $this->addSql('COMMENT ON COLUMN markers.uid IS \'(DC2Type:ulid)\'');
        $this->addSql('ALTER TABLE markers ADD CONSTRAINT FK_4189DF30A76ED395 FOREIGN KEY (user_id) REFERENCES "users" (id) ON DELETE SET NULL NOT DEFERRABLE INITIALLY IMMEDIATE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP SEQUENCE markers_id_seq CASCADE');
        $this->addSql('ALTER TABLE markers DROP CONSTRAINT FK_4189DF30A76ED395');
        $this->addSql('DROP TABLE markers');
    }
}
