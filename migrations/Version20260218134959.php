<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260218134959 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add missing foreign key constraints.';
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
        // Add foreign key constraints only if they don't exist
        if (!$this->constraintExists('alerte', 'FK_3AE753AA76ED395')) {
            $this->addSql('ALTER TABLE alerte ADD CONSTRAINT FK_3AE753AA76ED395 FOREIGN KEY (user_id) REFERENCES `user` (id)');
        }
        if (!$this->constraintExists('alerte', 'FK_3AE753AAA317541')) {
            $this->addSql('ALTER TABLE alerte ADD CONSTRAINT FK_3AE753AAA317541 FOREIGN KEY (type_energie_id) REFERENCES type_energie (id)');
        }
        if (!$this->constraintExists('consommation', 'FK_F993F0A2A76ED395')) {
            $this->addSql('ALTER TABLE consommation ADD CONSTRAINT FK_F993F0A2A76ED395 FOREIGN KEY (user_id) REFERENCES `user` (id)');
        }
        if (!$this->constraintExists('consommation', 'FK_F993F0A2AA317541')) {
            $this->addSql('ALTER TABLE consommation ADD CONSTRAINT FK_F993F0A2AA317541 FOREIGN KEY (type_energie_id) REFERENCES type_energie (id)');
        }
        if (!$this->constraintExists('logement', 'FK_F0FD4457A76ED395')) {
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
    }
}
