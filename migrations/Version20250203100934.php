<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250203100934 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE app_bank (id INT AUTO_INCREMENT NOT NULL, bank_name VARCHAR(255) NOT NULL, bank_code VARCHAR(255) NOT NULL, enable TINYINT(1) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE app_enterprise (id INT AUTO_INCREMENT NOT NULL, enterprise_name VARCHAR(255) NOT NULL, num_contribuable VARCHAR(255) NOT NULL, enable TINYINT(1) NOT NULL, enterprise_token VARCHAR(255) NOT NULL, create_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', om_number VARCHAR(255) DEFAULT NULL, momo_number VARCHAR(255) DEFAULT NULL, account_number VARCHAR(255) DEFAULT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE app_transaction (id INT AUTO_INCREMENT NOT NULL, enterprise_id INT DEFAULT NULL, bank_id INT DEFAULT NULL, amount DOUBLE PRECISION DEFAULT NULL, status VARCHAR(255) NOT NULL, creat_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', update_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', sender VARCHAR(255) DEFAULT NULL, receiver VARCHAR(255) DEFAULT NULL, type VARCHAR(255) DEFAULT NULL, app_transaction_ref VARCHAR(255) DEFAULT NULL, transaction_reason VARCHAR(255) DEFAULT NULL, transaction_currency VARCHAR(255) DEFAULT NULL, customer_name VARCHAR(255) DEFAULT NULL, solde_entree_net DOUBLE PRECISION DEFAULT NULL, solde_sortie_net DOUBLE PRECISION DEFAULT NULL, fees DOUBLE PRECISION DEFAULT NULL, INDEX IDX_2BD2236DA97D1AC3 (enterprise_id), INDEX IDX_2BD2236D11C8FB41 (bank_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE app_users (id INT AUTO_INCREMENT NOT NULL, user_enterprise_id INT DEFAULT NULL, email VARCHAR(180) NOT NULL, roles JSON NOT NULL, password VARCHAR(255) NOT NULL, user_name VARCHAR(255) NOT NULL, sur_name VARCHAR(255) NOT NULL, enable TINYINT(1) NOT NULL, first_name VARCHAR(255) NOT NULL, phone VARCHAR(255) NOT NULL, UNIQUE INDEX UNIQ_C250282447F3B793 (user_enterprise_id), UNIQUE INDEX UNIQ_IDENTIFIER_EMAIL (email), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE solde (id INT AUTO_INCREMENT NOT NULL, entree_brute DOUBLE PRECISION NOT NULL, sortie_brute DOUBLE PRECISION NOT NULL, entree_net DOUBLE PRECISION NOT NULL, sortie_net DOUBLE PRECISION NOT NULL, solde_brute DOUBLE PRECISION NOT NULL, solde_net DOUBLE PRECISION NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE app_transaction ADD CONSTRAINT FK_2BD2236DA97D1AC3 FOREIGN KEY (enterprise_id) REFERENCES app_enterprise (id)');
        $this->addSql('ALTER TABLE app_transaction ADD CONSTRAINT FK_2BD2236D11C8FB41 FOREIGN KEY (bank_id) REFERENCES app_bank (id)');
        $this->addSql('ALTER TABLE app_users ADD CONSTRAINT FK_C250282447F3B793 FOREIGN KEY (user_enterprise_id) REFERENCES app_enterprise (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE app_transaction DROP FOREIGN KEY FK_2BD2236DA97D1AC3');
        $this->addSql('ALTER TABLE app_transaction DROP FOREIGN KEY FK_2BD2236D11C8FB41');
        $this->addSql('ALTER TABLE app_users DROP FOREIGN KEY FK_C250282447F3B793');
        $this->addSql('DROP TABLE app_bank');
        $this->addSql('DROP TABLE app_enterprise');
        $this->addSql('DROP TABLE app_transaction');
        $this->addSql('DROP TABLE app_users');
        $this->addSql('DROP TABLE solde');
    }
}
