<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250315114611 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE payment DROP CONSTRAINT FK_6D28840DD3CA542C');
        $this->addSql('ALTER TABLE payment ADD CONSTRAINT FK_6D28840DD3CA542C FOREIGN KEY (lease_id) REFERENCES lease (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('ALTER TABLE payment DROP CONSTRAINT fk_6d28840dd3ca542c');
        $this->addSql('ALTER TABLE payment ADD CONSTRAINT fk_6d28840dd3ca542c FOREIGN KEY (lease_id) REFERENCES lease (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
    }
}
