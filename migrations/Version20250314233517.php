<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250314233517 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE tenant (id SERIAL NOT NULL, apartment_id INT DEFAULT NULL, first_name VARCHAR(255) NOT NULL, last_name VARCHAR(255) NOT NULL, email VARCHAR(255) NOT NULL, phone VARCHAR(20) DEFAULT NULL, birth_date DATE DEFAULT NULL, address TEXT DEFAULT NULL, guarantor_name VARCHAR(255) DEFAULT NULL, guarantor_email VARCHAR(255) DEFAULT NULL, guarantor_phone VARCHAR(20) DEFAULT NULL, guarantor_address TEXT DEFAULT NULL, id_card_filename VARCHAR(255) DEFAULT NULL, lease_contract_filename VARCHAR(255) DEFAULT NULL, guarantor_document_filename VARCHAR(255) DEFAULT NULL, notes TEXT DEFAULT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, updated_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_4E59C462176DFE85 ON tenant (apartment_id)');
        $this->addSql('ALTER TABLE tenant ADD CONSTRAINT FK_4E59C462176DFE85 FOREIGN KEY (apartment_id) REFERENCES apartment (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('ALTER TABLE tenant DROP CONSTRAINT FK_4E59C462176DFE85');
        $this->addSql('DROP TABLE tenant');
    }
}
