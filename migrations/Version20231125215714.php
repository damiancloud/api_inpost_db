<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20231125215714 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE SEQUENCE resources_type_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE SEQUENCE items_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE SEQUENCE resources_id_seq INCREMENT BY 1 MINVALUE 1 START 1');

        $this->addSql('CREATE TABLE resources_type (id INT NOT NULL, name VARCHAR(255) NOT NULL, PRIMARY KEY(id))');
        $this->addSql('INSERT INTO resources_type (id, name) VALUES (1, \'points\')');

        $this->addSql('CREATE TABLE items (id INT NOT NULL, name VARCHAR(255) NOT NULL, address VARCHAR(255) NOT NULL, resource INT NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE TABLE resources (id INT NOT NULL, count INT NOT NULL, page INT NOT NULL, total_pages INT NOT NULL, type INT NOT NULL, query_value VARCHAR(255) NOT NULL, PRIMARY KEY(id))');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP SEQUENCE items_id_seq CASCADE');
        $this->addSql('DROP SEQUENCE resources_id_seq CASCADE');
        $this->addSql('DROP SEQUENCE resources_type_id_seq CASCADE');
        $this->addSql('DROP TABLE items');
        $this->addSql('DROP TABLE resources');
        $this->addSql('DROP TABLE resources_type');
    }
}
