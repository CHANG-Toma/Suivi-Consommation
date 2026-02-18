<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260217103000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add logement table and link consommation/alerte to logement.';
    }

    private function tableExists(string $tableName): bool
    {
        try {
            // Check with case-insensitive comparison for MySQL
            // Use backticks for reserved words like 'user'
            $escapedName = strtolower($tableName);
            $result = $this->connection->executeQuery(
                'SELECT 1 FROM information_schema.TABLES WHERE TABLE_SCHEMA = DATABASE() AND LOWER(TABLE_NAME) = ?',
                [$escapedName]
            );
            return $result->rowCount() > 0;
        } catch (\Exception $e) {
            // If query fails, assume table doesn't exist
            return false;
        }
    }

    public function up(Schema $schema): void
    {
        $userTableExists = $this->tableExists('user');
        $typeEnergieTableExists = $this->tableExists('type_energie');
        
        // Create logement table if it doesn't exist
        $this->addSql('CREATE TABLE IF NOT EXISTS logement (id INT AUTO_INCREMENT NOT NULL, nom VARCHAR(255) NOT NULL, adresse VARCHAR(255) NOT NULL, user_id INT NOT NULL, INDEX IDX_F0FD4457A76ED395 (user_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        
        // Add foreign key on logement if it doesn't exist and user table exists
        if ($userTableExists) {
            $fkExists = $this->connection->executeQuery(
                'SELECT 1 FROM information_schema.TABLE_CONSTRAINTS WHERE CONSTRAINT_SCHEMA = DATABASE() AND TABLE_NAME = ? AND CONSTRAINT_NAME = ?',
                ['logement', 'FK_F0FD4457A76ED395']
            )->rowCount() > 0;
            if (!$fkExists) {
                $this->addSql('ALTER TABLE logement ADD CONSTRAINT FK_F0FD4457A76ED395 FOREIGN KEY (user_id) REFERENCES `user` (id)');
            }
        }

        // Alerte: create table if missing, otherwise alter
        if (!$this->tableExists('alerte')) {
            $this->addSql('CREATE TABLE alerte (id INT AUTO_INCREMENT NOT NULL, user_id INT NOT NULL, type_energie_id INT DEFAULT NULL, logement_id INT NOT NULL, titre VARCHAR(255) NOT NULL, message LONGTEXT DEFAULT NULL, seuil NUMERIC(10, 2) DEFAULT NULL, lu TINYINT(1) NOT NULL, created_at DATETIME NOT NULL, INDEX IDX_3AE753AA76ED395 (user_id), INDEX IDX_3AE753AAA317541 (type_energie_id), INDEX IDX_3AE753A58ABF955 (logement_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci');
            if ($userTableExists) {
                $this->addSql('ALTER TABLE alerte ADD CONSTRAINT FK_3AE753AA76ED395 FOREIGN KEY (user_id) REFERENCES `user` (id)');
            }
            if ($typeEnergieTableExists) {
                $this->addSql('ALTER TABLE alerte ADD CONSTRAINT FK_3AE753AAA317541 FOREIGN KEY (type_energie_id) REFERENCES type_energie (id)');
            }
            $this->addSql('ALTER TABLE alerte ADD CONSTRAINT FK_3AE753A58ABF955 FOREIGN KEY (logement_id) REFERENCES logement (id)');
        } else {
            $this->addSql('ALTER TABLE alerte DROP FOREIGN KEY `alerte_ibfk_1`');
            $this->addSql('ALTER TABLE alerte DROP FOREIGN KEY `alerte_ibfk_2`');
            $this->addSql('ALTER TABLE alerte ADD logement_id INT NOT NULL, CHANGE message message LONGTEXT DEFAULT NULL, CHANGE lu lu TINYINT NOT NULL');
            if ($userTableExists) {
                $this->addSql('ALTER TABLE alerte ADD CONSTRAINT FK_3AE753AA76ED395 FOREIGN KEY (user_id) REFERENCES `user` (id)');
            }
            if ($typeEnergieTableExists) {
                $this->addSql('ALTER TABLE alerte ADD CONSTRAINT FK_3AE753AAA317541 FOREIGN KEY (type_energie_id) REFERENCES type_energie (id)');
            }
            $this->addSql('ALTER TABLE alerte ADD CONSTRAINT FK_3AE753A58ABF955 FOREIGN KEY (logement_id) REFERENCES logement (id)');
            $this->addSql('CREATE INDEX IDX_3AE753A58ABF955 ON alerte (logement_id)');
            $this->addSql('ALTER TABLE alerte RENAME INDEX idx_user TO IDX_3AE753AA76ED395');
            $this->addSql('ALTER TABLE alerte RENAME INDEX idx_type_energie TO IDX_3AE753AAA317541');
        }

        // Consommation: create table if missing, otherwise alter
        if (!$this->tableExists('consommation')) {
            $this->addSql('CREATE TABLE consommation (id INT AUTO_INCREMENT NOT NULL, user_id INT NOT NULL, type_energie_id INT NOT NULL, logement_id INT NOT NULL, valeur NUMERIC(10, 2) NOT NULL, date_releve DATE NOT NULL, created_at DATETIME NOT NULL, INDEX IDX_F993F0A2A76ED395 (user_id), INDEX IDX_F993F0A2AA317541 (type_energie_id), INDEX IDX_F993F0A258ABF955 (logement_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci');
            if ($userTableExists) {
                $this->addSql('ALTER TABLE consommation ADD CONSTRAINT FK_F993F0A2A76ED395 FOREIGN KEY (user_id) REFERENCES `user` (id)');
            }
            if ($typeEnergieTableExists) {
                $this->addSql('ALTER TABLE consommation ADD CONSTRAINT FK_F993F0A2AA317541 FOREIGN KEY (type_energie_id) REFERENCES type_energie (id)');
            }
            $this->addSql('ALTER TABLE consommation ADD CONSTRAINT FK_F993F0A258ABF955 FOREIGN KEY (logement_id) REFERENCES logement (id)');
        } else {
            $this->addSql('ALTER TABLE consommation DROP FOREIGN KEY `consommation_ibfk_1`');
            $this->addSql('ALTER TABLE consommation DROP FOREIGN KEY `consommation_ibfk_2`');
            $this->addSql('ALTER TABLE consommation ADD logement_id INT NOT NULL');
            if ($userTableExists) {
                $this->addSql('ALTER TABLE consommation ADD CONSTRAINT FK_F993F0A2A76ED395 FOREIGN KEY (user_id) REFERENCES `user` (id)');
            }
            if ($typeEnergieTableExists) {
                $this->addSql('ALTER TABLE consommation ADD CONSTRAINT FK_F993F0A2AA317541 FOREIGN KEY (type_energie_id) REFERENCES type_energie (id)');
            }
            $this->addSql('ALTER TABLE consommation ADD CONSTRAINT FK_F993F0A258ABF955 FOREIGN KEY (logement_id) REFERENCES logement (id)');
            $this->addSql('CREATE INDEX IDX_F993F0A258ABF955 ON consommation (logement_id)');
            $this->addSql('ALTER TABLE consommation RENAME INDEX idx_user TO IDX_F993F0A2A76ED395');
            $this->addSql('ALTER TABLE consommation RENAME INDEX idx_type_energie TO IDX_F993F0A2AA317541');
        }

        // User table: ensure unique index on email is named UNIQ_IDENTIFIER_EMAIL
        if ($this->tableExists('user')) {
            $hasEmailIndex = $this->connection->executeQuery(
                "SELECT 1 FROM information_schema.STATISTICS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'user' AND INDEX_NAME = 'email'"
            )->rowCount() > 0;
            if ($hasEmailIndex) {
                $this->addSql('ALTER TABLE user RENAME INDEX email TO UNIQ_IDENTIFIER_EMAIL');
            }
        }
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE alerte DROP FOREIGN KEY FK_3AE753A58ABF955');
        $this->addSql('DROP INDEX IDX_3AE753A58ABF955 ON alerte');
        $this->addSql('ALTER TABLE alerte DROP FOREIGN KEY FK_3AE753AA76ED395');
        $this->addSql('ALTER TABLE alerte DROP FOREIGN KEY FK_3AE753AAA317541');
        $this->addSql('ALTER TABLE alerte RENAME INDEX IDX_3AE753AA76ED395 TO idx_user');
        $this->addSql('ALTER TABLE alerte RENAME INDEX IDX_3AE753AAA317541 TO idx_type_energie');
        $this->addSql('ALTER TABLE alerte DROP COLUMN logement_id');
        $this->addSql('ALTER TABLE alerte ADD CONSTRAINT alerte_ibfk_1 FOREIGN KEY (user_id) REFERENCES `user` (id)');
        $this->addSql('ALTER TABLE alerte ADD CONSTRAINT alerte_ibfk_2 FOREIGN KEY (type_energie_id) REFERENCES type_energie (id)');

        $this->addSql('ALTER TABLE consommation DROP FOREIGN KEY FK_F993F0A258ABF955');
        $this->addSql('DROP INDEX IDX_F993F0A258ABF955 ON consommation');
        $this->addSql('ALTER TABLE consommation DROP FOREIGN KEY FK_F993F0A2A76ED395');
        $this->addSql('ALTER TABLE consommation DROP FOREIGN KEY FK_F993F0A2AA317541');
        $this->addSql('ALTER TABLE consommation RENAME INDEX IDX_F993F0A2A76ED395 TO idx_user');
        $this->addSql('ALTER TABLE consommation RENAME INDEX IDX_F993F0A2AA317541 TO idx_type_energie');
        $this->addSql('ALTER TABLE consommation DROP COLUMN logement_id');
        $this->addSql('ALTER TABLE consommation ADD CONSTRAINT consommation_ibfk_1 FOREIGN KEY (user_id) REFERENCES `user` (id)');
        $this->addSql('ALTER TABLE consommation ADD CONSTRAINT consommation_ibfk_2 FOREIGN KEY (type_energie_id) REFERENCES type_energie (id)');

        $this->addSql('ALTER TABLE logement DROP FOREIGN KEY FK_F0FD4457A76ED395');
        $this->addSql('DROP TABLE logement');
    }
}
