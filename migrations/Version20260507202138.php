<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260507202138 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE branches (id INT AUTO_INCREMENT NOT NULL, tenant_id INT NOT NULL, name VARCHAR(191) NOT NULL, code VARCHAR(191) NOT NULL, description LONGTEXT DEFAULT NULL, address VARCHAR(191) DEFAULT NULL, phone VARCHAR(191) DEFAULT NULL, email VARCHAR(191) DEFAULT NULL, is_active TINYINT DEFAULT 1 NOT NULL, created_at DATETIME DEFAULT NULL, updated_at DATETIME DEFAULT NULL, UNIQUE INDEX UNIQ_D760D16F77153098 (code), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE departments (id INT AUTO_INCREMENT NOT NULL, tenant_id INT NOT NULL, code VARCHAR(32) NOT NULL, name VARCHAR(191) NOT NULL, description VARCHAR(191) DEFAULT NULL, is_active TINYINT DEFAULT 1 NOT NULL, sort_order SMALLINT UNSIGNED DEFAULT 0 NOT NULL, created_at DATETIME DEFAULT NULL, updated_at DATETIME DEFAULT NULL, UNIQUE INDEX UNIQ_16AEB8D477153098 (code), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE documents (id INT AUTO_INCREMENT NOT NULL, tenant_id INT NOT NULL, lead_id INT NOT NULL, name VARCHAR(191) NOT NULL, original_name VARCHAR(191) NOT NULL, file_path VARCHAR(191) NOT NULL, file_type VARCHAR(191) NOT NULL, file_size BIGINT UNSIGNED NOT NULL, document_type VARCHAR(191) DEFAULT NULL, description LONGTEXT DEFAULT NULL, uploaded_by INT DEFAULT NULL, created_at DATETIME DEFAULT NULL, updated_at DATETIME DEFAULT NULL, INDEX idx_documents_lead_id (lead_id), INDEX idx_documents_type (document_type), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE leads (id INT AUTO_INCREMENT NOT NULL, tenant_id INT NOT NULL, source_lead_id INT DEFAULT NULL, full_name VARCHAR(191) DEFAULT NULL, email VARCHAR(191) DEFAULT NULL, phone VARCHAR(191) NOT NULL, alternate_phone VARCHAR(191) DEFAULT NULL, date_of_birth VARCHAR(191) DEFAULT NULL, place_of_birth VARCHAR(191) DEFAULT NULL, gender VARCHAR(191) DEFAULT NULL, nationality VARCHAR(191) DEFAULT NULL, current_country VARCHAR(191) DEFAULT NULL, marital_status VARCHAR(191) DEFAULT NULL, current_address LONGTEXT DEFAULT NULL, city VARCHAR(191) DEFAULT NULL, state VARCHAR(191) DEFAULT NULL, postal_code VARCHAR(191) DEFAULT NULL, country VARCHAR(191) DEFAULT NULL, service_type VARCHAR(191) DEFAULT NULL, number_of_tour_persons INT DEFAULT NULL, destination_country VARCHAR(191) DEFAULT NULL, secondary_destination_country VARCHAR(191) DEFAULT NULL, budget_range VARCHAR(191) DEFAULT NULL, funding_source VARCHAR(191) DEFAULT NULL, has_bank_statement TINYINT DEFAULT 0 NOT NULL, has_dependents TINYINT DEFAULT 0 NOT NULL, number_of_dependents INT DEFAULT 0 NOT NULL, dependents_information LONGTEXT DEFAULT NULL, has_cv TINYINT DEFAULT 0 NOT NULL, has_offer_letter TINYINT DEFAULT 0 NOT NULL, offer_letter_path VARCHAR(191) DEFAULT NULL, cv_path VARCHAR(191) DEFAULT NULL, notes LONGTEXT DEFAULT NULL, internal_notes LONGTEXT DEFAULT NULL, special_requirements LONGTEXT DEFAULT NULL, how_did_you_find_us VARCHAR(191) DEFAULT NULL, lead_source VARCHAR(191) DEFAULT NULL, lead_source_link VARCHAR(191) DEFAULT NULL, referral_source_details LONGTEXT DEFAULT NULL, status VARCHAR(191) DEFAULT \'new\' NOT NULL, sub_status VARCHAR(191) DEFAULT NULL, priority VARCHAR(191) DEFAULT \'normal\' NOT NULL, assigned_to INT DEFAULT NULL, transferred_to_department VARCHAR(191) DEFAULT NULL, department_manager_id INT DEFAULT NULL, is_locked_for_editing TINYINT DEFAULT 0 NOT NULL, transferred_to_department_at DATETIME DEFAULT NULL, selected_service_id INT DEFAULT NULL, selected_package_id INT DEFAULT NULL, service_selection_type VARCHAR(50) DEFAULT NULL, branch_id INT DEFAULT NULL, vendor_id INT DEFAULT NULL, created_by INT DEFAULT NULL, updated_by INT DEFAULT NULL, created_at DATETIME DEFAULT NULL, updated_at DATETIME DEFAULT NULL, UNIQUE INDEX UNIQ_17904552E7927C74 (email), UNIQUE INDEX UNIQ_17904552444F97DD (phone), INDEX idx_leads_tenant_status (tenant_id, status, created_at), INDEX idx_leads_tenant_assigned (tenant_id, assigned_to, status), INDEX idx_leads_full_name (full_name), INDEX idx_leads_branch_status (branch_id, status), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE permissions (id INT AUTO_INCREMENT NOT NULL, tenant_id INT NOT NULL, name VARCHAR(191) NOT NULL, slug VARCHAR(191) NOT NULL, description LONGTEXT DEFAULT NULL, `group` VARCHAR(191) DEFAULT NULL, is_active TINYINT DEFAULT 1 NOT NULL, created_at DATETIME DEFAULT NULL, updated_at DATETIME DEFAULT NULL, UNIQUE INDEX UNIQ_2DEDCC6F5E237E06 (name), UNIQUE INDEX UNIQ_2DEDCC6F989D9B62 (slug), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE roles (id INT AUTO_INCREMENT NOT NULL, tenant_id INT NOT NULL, name VARCHAR(191) NOT NULL, slug VARCHAR(191) NOT NULL, description LONGTEXT DEFAULT NULL, is_active TINYINT DEFAULT 1 NOT NULL, created_at DATETIME DEFAULT NULL, updated_at DATETIME DEFAULT NULL, UNIQUE INDEX UNIQ_B63E2EC75E237E06 (name), UNIQUE INDEX UNIQ_B63E2EC7989D9B62 (slug), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE permission_role (role_id INT NOT NULL, permission_id INT NOT NULL, INDEX IDX_6A711CAD60322AC (role_id), INDEX IDX_6A711CAFED90CCA (permission_id), PRIMARY KEY (role_id, permission_id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE tenants (id INT AUTO_INCREMENT NOT NULL, slug VARCHAR(100) NOT NULL, name VARCHAR(255) NOT NULL, custom_domain VARCHAR(255) DEFAULT NULL, timezone VARCHAR(100) DEFAULT NULL, locale VARCHAR(10) DEFAULT NULL, is_active TINYINT NOT NULL, created_at DATETIME NOT NULL, UNIQUE INDEX UNIQ_B8FC96BB989D9B62 (slug), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE users (id INT AUTO_INCREMENT NOT NULL, tenant_id INT NOT NULL, name VARCHAR(191) NOT NULL, email VARCHAR(191) NOT NULL, avatar VARCHAR(191) DEFAULT NULL, phone VARCHAR(191) DEFAULT NULL, timezone VARCHAR(191) DEFAULT \'UTC\' NOT NULL, language VARCHAR(10) DEFAULT \'en\' NOT NULL, settings JSON DEFAULT NULL, email_verified_at DATETIME DEFAULT NULL, password VARCHAR(191) NOT NULL, remember_token VARCHAR(100) DEFAULT NULL, branch_id INT DEFAULT NULL, created_at DATETIME DEFAULT NULL, updated_at DATETIME DEFAULT NULL, UNIQUE INDEX UNIQ_1483A5E9E7927C74 (email), INDEX idx_users_tenant_id (tenant_id), INDEX idx_users_branch_id (branch_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE role_user (user_id INT NOT NULL, role_id INT NOT NULL, INDEX IDX_332CA4DDA76ED395 (user_id), INDEX IDX_332CA4DDD60322AC (role_id), PRIMARY KEY (user_id, role_id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('ALTER TABLE permission_role ADD CONSTRAINT FK_6A711CAD60322AC FOREIGN KEY (role_id) REFERENCES roles (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE permission_role ADD CONSTRAINT FK_6A711CAFED90CCA FOREIGN KEY (permission_id) REFERENCES permissions (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE role_user ADD CONSTRAINT FK_332CA4DDA76ED395 FOREIGN KEY (user_id) REFERENCES users (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE role_user ADD CONSTRAINT FK_332CA4DDD60322AC FOREIGN KEY (role_id) REFERENCES roles (id) ON DELETE CASCADE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE permission_role DROP FOREIGN KEY FK_6A711CAD60322AC');
        $this->addSql('ALTER TABLE permission_role DROP FOREIGN KEY FK_6A711CAFED90CCA');
        $this->addSql('ALTER TABLE role_user DROP FOREIGN KEY FK_332CA4DDA76ED395');
        $this->addSql('ALTER TABLE role_user DROP FOREIGN KEY FK_332CA4DDD60322AC');
        $this->addSql('DROP TABLE branches');
        $this->addSql('DROP TABLE departments');
        $this->addSql('DROP TABLE documents');
        $this->addSql('DROP TABLE leads');
        $this->addSql('DROP TABLE permissions');
        $this->addSql('DROP TABLE roles');
        $this->addSql('DROP TABLE permission_role');
        $this->addSql('DROP TABLE tenants');
        $this->addSql('DROP TABLE users');
        $this->addSql('DROP TABLE role_user');
    }
}
