<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260218134852 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Create missing tables (type_energie, user) and add foreign key constraints.';
    }

    private function tableExists(string $tableName): bool
    {
        try {
            $escapedName = strtolower($tableName);
            $result = $this->connection->executeQuery(
                'SELECT 1 FROM information_schema.TABLES WHERE TABLE_SCHEMA = DATABASE() AND LOWER(TABLE_NAME) = ?',
                [$escapedName]
            );
            return $result->rowCount() > 0;
        } catch (\Exception $e) {
            return false;
        }
    }

    private function constraintExists(string $tableName, string $constraintName): bool
    {
        try {
            $result = $this->connection->executeQuery(
                'SELECT 1 FROM information_schema.TABLE_CONSTRAINTS WHERE CONSTRAINT_SCHEMA = DATABASE() AND TABLE_NAME = ? AND CONSTRAINT_NAME = ?',
                [$tableName, $constraintName]
            );
            return $result->rowCount() > 0;
        } catch (\Exception $e) {
            return false;
        }
    }

    public function up(Schema $schema): void
    {
        // Create type_energie table if it doesn't exist
        if (!$this->tableExists('type_energie')) {
            $this->addSql('CREATE TABLE type_energie (id INT AUTO_INCREMENT NOT NULL, nom VARCHAR(100) NOT NULL, unite VARCHAR(50) DEFAULT NULL, couleur VARCHAR(7) DEFAULT NULL, icone VARCHAR(50) DEFAULT NULL, PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        }

        // Create user table if it doesn't exist
        if (!$this->tableExists('user')) {
            $this->addSql('CREATE TABLE `user` (id INT AUTO_INCREMENT NOT NULL, email VARCHAR(180) NOT NULL, roles JSON NOT NULL, password VARCHAR(255) NOT NULL, first_name VARCHAR(255) DEFAULT NULL, last_name VARCHAR(255) DEFAULT NULL, created_at DATETIME NOT NULL, UNIQUE INDEX UNIQ_IDENTIFIER_EMAIL (email), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        }

        // Add foreign key constraints only if tables exist and constraints don't exist
        if ($this->tableExists('alerte') && $this->tableExists('user') && !$this->constraintExists('alerte', 'FK_3AE753AA76ED395')) {
            $this->addSql('ALTER TABLE alerte ADD CONSTRAINT FK_3AE753AA76ED395 FOREIGN KEY (user_id) REFERENCES `user` (id)');
        }

        if ($this->tableExists('alerte') && $this->tableExists('type_energie') && !$this->constraintExists('alerte', 'FK_3AE753AAA317541')) {
            $this->addSql('ALTER TABLE alerte ADD CONSTRAINT FK_3AE753AAA317541 FOREIGN KEY (type_energie_id) REFERENCES type_energie (id)');
        }

        if ($this->tableExists('consommation') && $this->tableExists('user') && !$this->constraintExists('consommation', 'FK_F993F0A2A76ED395')) {
            $this->addSql('ALTER TABLE consommation ADD CONSTRAINT FK_F993F0A2A76ED395 FOREIGN KEY (user_id) REFERENCES `user` (id)');
        }

        if ($this->tableExists('consommation') && $this->tableExists('type_energie') && !$this->constraintExists('consommation', 'FK_F993F0A2AA317541')) {
            $this->addSql('ALTER TABLE consommation ADD CONSTRAINT FK_F993F0A2AA317541 FOREIGN KEY (type_energie_id) REFERENCES type_energie (id)');
        }

        if ($this->tableExists('logement') && $this->tableExists('user') && !$this->constraintExists('logement', 'FK_F0FD4457A76ED395')) {
            $this->addSql('ALTER TABLE logement ADD CONSTRAINT FK_F0FD4457A76ED395 FOREIGN KEY (user_id) REFERENCES `user` (id)');
        }
    }

    public function down(Schema $schema): void
    {
        // Drop foreign key constraints if they exist
        if ($this->constraintExists('logement', 'FK_F0FD4457A76ED395')) {
            $this->addSql('ALTER TABLE logement DROP FOREIGN KEY FK_F0FD4457A76ED395');
        }
        if ($this->constraintExists('consommation', 'FK_F993F0A2AA317541')) {
            $this->addSql('ALTER TABLE consommation DROP FOREIGN KEY FK_F993F0A2AA317541');
        }
        if ($this->constraintExists('consommation', 'FK_F993F0A2A76ED395')) {
            $this->addSql('ALTER TABLE consommation DROP FOREIGN KEY FK_F993F0A2A76ED395');
        }
        if ($this->constraintExists('alerte', 'FK_3AE753AAA317541')) {
            $this->addSql('ALTER TABLE alerte DROP FOREIGN KEY FK_3AE753AAA317541');
        }
        if ($this->constraintExists('alerte', 'FK_3AE753AA76ED395')) {
            $this->addSql('ALTER TABLE alerte DROP FOREIGN KEY FK_3AE753AA76ED395');
        }

        // Drop tables if they exist
        if ($this->tableExists('user')) {
            $this->addSql('DROP TABLE `user`');
        }
        if ($this->tableExists('type_energie')) {
            $this->addSql('DROP TABLE type_energie');
        }
    }
}
