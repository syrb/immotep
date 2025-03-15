<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250314231301 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE apartment (id SERIAL NOT NULL, name VARCHAR(255) NOT NULL, address VARCHAR(255) NOT NULL, purchase_date DATE NOT NULL, purchase_price DOUBLE PRECISION NOT NULL, rental_price DOUBLE PRECISION NOT NULL, charges DOUBLE PRECISION NOT NULL, size INT NOT NULL, status VARCHAR(255) DEFAULT NULL, tenant_name VARCHAR(255) DEFAULT NULL, description TEXT DEFAULT NULL, bedrooms INT DEFAULT NULL, bathrooms INT DEFAULT NULL, image_filename VARCHAR(255) DEFAULT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, updated_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, PRIMARY KEY(id))');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('DROP TABLE apartment');
    }
}
