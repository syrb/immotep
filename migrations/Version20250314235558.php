<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250314235558 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE lease (id SERIAL NOT NULL, apartment_id INT NOT NULL, start_date DATE NOT NULL, end_date DATE NOT NULL, rental_amount DOUBLE PRECISION NOT NULL, charges_amount DOUBLE PRECISION DEFAULT NULL, deposit_amount DOUBLE PRECISION DEFAULT NULL, status VARCHAR(255) DEFAULT NULL, notes TEXT DEFAULT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, updated_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_E6C77495176DFE85 ON lease (apartment_id)');
        $this->addSql('CREATE TABLE lease_tenant (lease_id INT NOT NULL, tenant_id INT NOT NULL, PRIMARY KEY(lease_id, tenant_id))');
        $this->addSql('CREATE INDEX IDX_32081F82D3CA542C ON lease_tenant (lease_id)');
        $this->addSql('CREATE INDEX IDX_32081F829033212A ON lease_tenant (tenant_id)');
        $this->addSql('CREATE TABLE payment (id SERIAL NOT NULL, lease_id INT NOT NULL, due_date DATE NOT NULL, amount DOUBLE PRECISION NOT NULL, status VARCHAR(50) NOT NULL, payment_date DATE DEFAULT NULL, payment_method VARCHAR(255) DEFAULT NULL, notes TEXT DEFAULT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, updated_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_6D28840DD3CA542C ON payment (lease_id)');
        $this->addSql('ALTER TABLE lease ADD CONSTRAINT FK_E6C77495176DFE85 FOREIGN KEY (apartment_id) REFERENCES apartment (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE lease_tenant ADD CONSTRAINT FK_32081F82D3CA542C FOREIGN KEY (lease_id) REFERENCES lease (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE lease_tenant ADD CONSTRAINT FK_32081F829033212A FOREIGN KEY (tenant_id) REFERENCES tenant (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE payment ADD CONSTRAINT FK_6D28840DD3CA542C FOREIGN KEY (lease_id) REFERENCES lease (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('ALTER TABLE lease DROP CONSTRAINT FK_E6C77495176DFE85');
        $this->addSql('ALTER TABLE lease_tenant DROP CONSTRAINT FK_32081F82D3CA542C');
        $this->addSql('ALTER TABLE lease_tenant DROP CONSTRAINT FK_32081F829033212A');
        $this->addSql('ALTER TABLE payment DROP CONSTRAINT FK_6D28840DD3CA542C');
        $this->addSql('DROP TABLE lease');
        $this->addSql('DROP TABLE lease_tenant');
        $this->addSql('DROP TABLE payment');
    }
}
