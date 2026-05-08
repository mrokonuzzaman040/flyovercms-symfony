<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260508111003 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE lead_call_histories (id INT AUTO_INCREMENT NOT NULL, lead_id INT NOT NULL, called_by INT DEFAULT NULL, phone_number VARCHAR(50) DEFAULT NULL, call_type VARCHAR(20) DEFAULT NULL, status VARCHAR(20) DEFAULT NULL, duration INT DEFAULT NULL, notes LONGTEXT DEFAULT NULL, call_date DATETIME DEFAULT NULL, created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE lead_follow_ups (id INT AUTO_INCREMENT NOT NULL, lead_id INT NOT NULL, created_by INT DEFAULT NULL, assigned_to INT DEFAULT NULL, follow_up_date DATETIME DEFAULT NULL, follow_up_type VARCHAR(50) DEFAULT NULL, category VARCHAR(50) DEFAULT NULL, priority VARCHAR(20) DEFAULT NULL, status VARCHAR(20) DEFAULT NULL, notes LONGTEXT DEFAULT NULL, is_completed TINYINT NOT NULL, completed_at DATETIME DEFAULT NULL, completed_by INT DEFAULT NULL, outcome VARCHAR(100) DEFAULT NULL, outcome_notes LONGTEXT DEFAULT NULL, duration_minutes INT DEFAULT NULL, created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE lead_notes (id INT AUTO_INCREMENT NOT NULL, lead_id INT NOT NULL, created_by INT DEFAULT NULL, note LONGTEXT NOT NULL, created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP TABLE lead_call_histories');
        $this->addSql('DROP TABLE lead_follow_ups');
        $this->addSql('DROP TABLE lead_notes');
    }
}
