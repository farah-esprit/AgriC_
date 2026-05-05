<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260420231453 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE ext_log_entries (id INT AUTO_INCREMENT NOT NULL, action VARCHAR(8) NOT NULL, logged_at DATETIME NOT NULL, object_id VARCHAR(64) DEFAULT NULL, object_class VARCHAR(191) NOT NULL, version INT NOT NULL, data JSON DEFAULT NULL, username VARCHAR(191) DEFAULT NULL, INDEX log_class_lookup_idx (object_class), INDEX log_date_lookup_idx (logged_at), INDEX log_user_lookup_idx (username), INDEX log_version_lookup_idx (object_id, object_class, version), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4 ROW_FORMAT = DYNAMIC');
        $this->addSql('ALTER TABLE chat_message DROP FOREIGN KEY `FK_FAB3FC16E92F8F78`');
        $this->addSql('ALTER TABLE chat_message DROP FOREIGN KEY `FK_FAB3FC16F624B39D`');
        $this->addSql('ALTER TABLE friendship DROP FOREIGN KEY `FK_7234A45F6A5458E8`');
        $this->addSql('ALTER TABLE friendship DROP FOREIGN KEY `FK_7234A45FA76ED395`');
        $this->addSql('ALTER TABLE notification DROP FOREIGN KEY `FK_BF5476CAE92F8F78`');
        $this->addSql('ALTER TABLE notification DROP FOREIGN KEY `FK_BF5476CAF624B39D`');
        $this->addSql('ALTER TABLE response_like DROP FOREIGN KEY `FK_C24A29A76ED395`');
        $this->addSql('ALTER TABLE response_like DROP FOREIGN KEY `FK_C24A29FBF32840`');
        $this->addSql('ALTER TABLE story DROP FOREIGN KEY `FK_EB560438A76ED395`');
        $this->addSql('ALTER TABLE story_comment DROP FOREIGN KEY `FK_C788C2EEA76ED395`');
        $this->addSql('ALTER TABLE story_comment DROP FOREIGN KEY `FK_C788C2EEAA5D4036`');
        $this->addSql('ALTER TABLE story_like DROP FOREIGN KEY `FK_3ACE2C9DA76ED395`');
        $this->addSql('ALTER TABLE story_like DROP FOREIGN KEY `FK_3ACE2C9DAA5D4036`');
        $this->addSql('ALTER TABLE story_view DROP FOREIGN KEY `FK_6850C7A0A76ED395`');
        $this->addSql('ALTER TABLE story_view DROP FOREIGN KEY `FK_6850C7A0AA5D4036`');
        $this->addSql('ALTER TABLE thread_like DROP FOREIGN KEY `FK_28D25C3DA76ED395`');
        $this->addSql('ALTER TABLE thread_like DROP FOREIGN KEY `FK_28D25C3DE2904019`');
        $this->addSql('ALTER TABLE user_shared_threads DROP FOREIGN KEY `FK_25DCEE33A76ED395`');
        $this->addSql('ALTER TABLE user_shared_threads DROP FOREIGN KEY `FK_25DCEE33E2904019`');
        $this->addSql('DROP TABLE chat_message');
        $this->addSql('DROP TABLE doctrine_migration_versions');
        $this->addSql('DROP TABLE friendship');
        $this->addSql('DROP TABLE notification');
        $this->addSql('DROP TABLE response_like');
        $this->addSql('DROP TABLE story');
        $this->addSql('DROP TABLE story_comment');
        $this->addSql('DROP TABLE story_like');
        $this->addSql('DROP TABLE story_view');
        $this->addSql('DROP TABLE thread_like');
        $this->addSql('DROP TABLE user_shared_threads');
        $this->addSql('ALTER TABLE activity CHANGE end_at end_at DATETIME DEFAULT NULL');
        $this->addSql('ALTER TABLE `admin` CHANGE telephone telephone VARCHAR(20) DEFAULT NULL, CHANGE photo photo VARCHAR(255) DEFAULT NULL, CHANGE date_creation date_creation DATETIME DEFAULT NULL, CHANGE reset_token reset_token VARCHAR(255) DEFAULT NULL, CHANGE reset_token_requested_at reset_token_requested_at DATETIME DEFAULT NULL');
        $this->addSql('ALTER TABLE commande CHANGE date_commande date_commande VARCHAR(255) DEFAULT NULL, CHANGE statut statut VARCHAR(255) DEFAULT NULL, CHANGE prix_total prix_total DOUBLE PRECISION DEFAULT NULL');
        $this->addSql('ALTER TABLE culture CHANGE nom nom VARCHAR(255) DEFAULT NULL, CHANGE type type VARCHAR(255) DEFAULT NULL, CHANGE superficie superficie DOUBLE PRECISION DEFAULT NULL, CHANGE localisation localisation VARCHAR(255) DEFAULT NULL, CHANGE image image VARCHAR(255) DEFAULT NULL, CHANGE dateSemis dateSemis DATETIME DEFAULT NULL');
        $this->addSql('ALTER TABLE diagnostic CHANGE dateDiagnostic dateDiagnostic VARCHAR(255) DEFAULT NULL, CHANGE symptomes symptomes VARCHAR(255) DEFAULT NULL, CHANGE informationsComplementaires informationsComplementaires VARCHAR(255) DEFAULT NULL, CHANGE image image VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE evenement CHANGE titre titre VARCHAR(255) DEFAULT NULL, CHANGE description description VARCHAR(255) DEFAULT NULL, CHANGE date_debut date_debut VARCHAR(255) DEFAULT NULL, CHANGE date_fin date_fin VARCHAR(255) DEFAULT NULL, CHANGE lieu lieu VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE produit CHANGE nom nom VARCHAR(255) DEFAULT NULL, CHANGE description description VARCHAR(255) DEFAULT NULL, CHANGE prix prix DOUBLE PRECISION DEFAULT NULL, CHANGE categorie categorie VARCHAR(255) DEFAULT NULL, CHANGE image_path image_path VARCHAR(255) DEFAULT NULL, CHANGE taux_promo taux_promo DOUBLE PRECISION DEFAULT NULL');
        $this->addSql('ALTER TABLE profil DROP FOREIGN KEY `FK_E6D6B297A76ED395`');
        $this->addSql('DROP INDEX UNIQ_E6D6B297A76ED395 ON profil');
        $this->addSql('ALTER TABLE profil CHANGE bio bio VARCHAR(255) DEFAULT NULL, CHANGE telephone telephone VARCHAR(255) DEFAULT NULL, CHANGE image image VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE reclamation CHANGE objet objet VARCHAR(255) DEFAULT NULL, CHANGE description description VARCHAR(255) DEFAULT NULL, CHANGE date_creation date_creation VARCHAR(255) DEFAULT NULL, CHANGE statut statut VARCHAR(255) DEFAULT NULL, CHANGE priorite priorite VARCHAR(255) DEFAULT NULL, CHANGE type type VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE thread CHANGE category category VARCHAR(100) DEFAULT NULL, CHANGE tags tags VARCHAR(255) DEFAULT NULL, CHANGE attachments attachments VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE user ADD is_verified TINYINT NOT NULL, ADD is_phone_verified TINYINT NOT NULL, ADD totp_secret VARCHAR(255) DEFAULT NULL, ADD face_image_path VARCHAR(255) DEFAULT NULL, DROP xp, DROP strikes, CHANGE role role VARCHAR(50) DEFAULT NULL, CHANGE etatCompte etatCompte VARCHAR(50) DEFAULT NULL, CHANGE date_creation date_creation DATETIME DEFAULT NULL, CHANGE telephone telephone VARCHAR(20) DEFAULT NULL, CHANGE reset_token reset_token VARCHAR(255) DEFAULT NULL, CHANGE reset_token_requested_at reset_token_requested_at DATETIME DEFAULT NULL');
        $this->addSql('ALTER TABLE messenger_messages CHANGE delivered_at delivered_at DATETIME DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE chat_message (id INT AUTO_INCREMENT NOT NULL, content LONGTEXT CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_general_ci`, is_read TINYINT NOT NULL, created_at DATETIME NOT NULL, sender_id INT NOT NULL, recipient_id INT NOT NULL, INDEX IDX_FAB3FC16F624B39D (sender_id), INDEX IDX_FAB3FC16E92F8F78 (recipient_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_general_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('CREATE TABLE doctrine_migration_versions (version VARCHAR(191) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`, executed_at DATETIME DEFAULT \'NULL\', execution_time INT DEFAULT NULL, PRIMARY KEY (version)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('CREATE TABLE friendship (id INT AUTO_INCREMENT NOT NULL, status VARCHAR(20) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_general_ci`, created_at DATETIME NOT NULL, user_id INT NOT NULL, friend_id INT NOT NULL, INDEX IDX_7234A45F6A5458E8 (friend_id), INDEX IDX_7234A45FA76ED395 (user_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_general_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('CREATE TABLE notification (id INT AUTO_INCREMENT NOT NULL, type VARCHAR(50) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_general_ci`, message VARCHAR(255) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_general_ci`, url VARCHAR(255) CHARACTER SET utf8mb4 DEFAULT \'NULL\' COLLATE `utf8mb4_general_ci`, is_read TINYINT NOT NULL, created_at DATETIME NOT NULL, recipient_id INT NOT NULL, sender_id INT DEFAULT NULL, INDEX IDX_BF5476CAE92F8F78 (recipient_id), INDEX IDX_BF5476CAF624B39D (sender_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_general_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('CREATE TABLE response_like (id INT AUTO_INCREMENT NOT NULL, created_at DATETIME NOT NULL, user_id INT NOT NULL, response_id INT NOT NULL, INDEX IDX_C24A29A76ED395 (user_id), INDEX IDX_C24A29FBF32840 (response_id), UNIQUE INDEX unique_response_like (response_id, user_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_general_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('CREATE TABLE story (id INT AUTO_INCREMENT NOT NULL, media_url VARCHAR(255) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_general_ci`, created_at DATETIME NOT NULL, user_id INT NOT NULL, INDEX IDX_EB560438A76ED395 (user_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_general_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('CREATE TABLE story_comment (id INT AUTO_INCREMENT NOT NULL, content LONGTEXT CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_general_ci`, created_at DATETIME NOT NULL, user_id INT NOT NULL, story_id INT NOT NULL, INDEX IDX_C788C2EEAA5D4036 (story_id), INDEX IDX_C788C2EEA76ED395 (user_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_general_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('CREATE TABLE story_like (id INT AUTO_INCREMENT NOT NULL, created_at DATETIME NOT NULL, user_id INT NOT NULL, story_id INT NOT NULL, INDEX IDX_3ACE2C9DA76ED395 (user_id), INDEX IDX_3ACE2C9DAA5D4036 (story_id), UNIQUE INDEX unique_story_like (story_id, user_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_general_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('CREATE TABLE story_view (id INT AUTO_INCREMENT NOT NULL, created_at DATETIME NOT NULL, story_id INT NOT NULL, user_id INT NOT NULL, INDEX IDX_6850C7A0A76ED395 (user_id), UNIQUE INDEX unique_story_view (story_id, user_id), INDEX IDX_6850C7A0AA5D4036 (story_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_general_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('CREATE TABLE thread_like (id INT AUTO_INCREMENT NOT NULL, created_at DATETIME NOT NULL, user_id INT NOT NULL, thread_id INT NOT NULL, INDEX IDX_28D25C3DA76ED395 (user_id), INDEX IDX_28D25C3DE2904019 (thread_id), UNIQUE INDEX unique_thread_like (thread_id, user_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_general_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('CREATE TABLE user_shared_threads (user_id INT NOT NULL, thread_id INT NOT NULL, INDEX IDX_25DCEE33A76ED395 (user_id), INDEX IDX_25DCEE33E2904019 (thread_id), PRIMARY KEY (user_id, thread_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_general_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('ALTER TABLE chat_message ADD CONSTRAINT `FK_FAB3FC16E92F8F78` FOREIGN KEY (recipient_id) REFERENCES user (user_id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE chat_message ADD CONSTRAINT `FK_FAB3FC16F624B39D` FOREIGN KEY (sender_id) REFERENCES user (user_id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE friendship ADD CONSTRAINT `FK_7234A45F6A5458E8` FOREIGN KEY (friend_id) REFERENCES user (user_id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE friendship ADD CONSTRAINT `FK_7234A45FA76ED395` FOREIGN KEY (user_id) REFERENCES user (user_id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE notification ADD CONSTRAINT `FK_BF5476CAE92F8F78` FOREIGN KEY (recipient_id) REFERENCES user (user_id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE notification ADD CONSTRAINT `FK_BF5476CAF624B39D` FOREIGN KEY (sender_id) REFERENCES user (user_id) ON DELETE SET NULL');
        $this->addSql('ALTER TABLE response_like ADD CONSTRAINT `FK_C24A29A76ED395` FOREIGN KEY (user_id) REFERENCES user (user_id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE response_like ADD CONSTRAINT `FK_C24A29FBF32840` FOREIGN KEY (response_id) REFERENCES response (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE story ADD CONSTRAINT `FK_EB560438A76ED395` FOREIGN KEY (user_id) REFERENCES user (user_id)');
        $this->addSql('ALTER TABLE story_comment ADD CONSTRAINT `FK_C788C2EEA76ED395` FOREIGN KEY (user_id) REFERENCES user (user_id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE story_comment ADD CONSTRAINT `FK_C788C2EEAA5D4036` FOREIGN KEY (story_id) REFERENCES story (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE story_like ADD CONSTRAINT `FK_3ACE2C9DA76ED395` FOREIGN KEY (user_id) REFERENCES user (user_id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE story_like ADD CONSTRAINT `FK_3ACE2C9DAA5D4036` FOREIGN KEY (story_id) REFERENCES story (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE story_view ADD CONSTRAINT `FK_6850C7A0A76ED395` FOREIGN KEY (user_id) REFERENCES user (user_id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE story_view ADD CONSTRAINT `FK_6850C7A0AA5D4036` FOREIGN KEY (story_id) REFERENCES story (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE thread_like ADD CONSTRAINT `FK_28D25C3DA76ED395` FOREIGN KEY (user_id) REFERENCES user (user_id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE thread_like ADD CONSTRAINT `FK_28D25C3DE2904019` FOREIGN KEY (thread_id) REFERENCES thread (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE user_shared_threads ADD CONSTRAINT `FK_25DCEE33A76ED395` FOREIGN KEY (user_id) REFERENCES user (user_id)');
        $this->addSql('ALTER TABLE user_shared_threads ADD CONSTRAINT `FK_25DCEE33E2904019` FOREIGN KEY (thread_id) REFERENCES thread (id)');
        $this->addSql('DROP TABLE ext_log_entries');
        $this->addSql('ALTER TABLE activity CHANGE end_at end_at DATETIME DEFAULT \'NULL\'');
        $this->addSql('ALTER TABLE `admin` CHANGE telephone telephone VARCHAR(20) DEFAULT \'NULL\', CHANGE date_creation date_creation DATETIME DEFAULT \'NULL\', CHANGE reset_token reset_token VARCHAR(255) DEFAULT \'NULL\', CHANGE reset_token_requested_at reset_token_requested_at DATETIME DEFAULT \'NULL\', CHANGE photo photo VARCHAR(255) DEFAULT \'NULL\'');
        $this->addSql('ALTER TABLE commande CHANGE date_commande date_commande VARCHAR(255) DEFAULT \'NULL\', CHANGE statut statut VARCHAR(255) DEFAULT \'NULL\', CHANGE prix_total prix_total DOUBLE PRECISION DEFAULT \'NULL\'');
        $this->addSql('ALTER TABLE culture CHANGE nom nom VARCHAR(255) DEFAULT \'NULL\', CHANGE type type VARCHAR(255) DEFAULT \'NULL\', CHANGE superficie superficie DOUBLE PRECISION DEFAULT \'NULL\', CHANGE localisation localisation VARCHAR(255) DEFAULT \'NULL\', CHANGE image image VARCHAR(255) DEFAULT \'NULL\', CHANGE dateSemis dateSemis DATETIME DEFAULT \'NULL\'');
        $this->addSql('ALTER TABLE diagnostic CHANGE image image VARCHAR(255) DEFAULT \'NULL\', CHANGE dateDiagnostic dateDiagnostic VARCHAR(255) DEFAULT \'NULL\', CHANGE symptomes symptomes VARCHAR(255) DEFAULT \'NULL\', CHANGE informationsComplementaires informationsComplementaires VARCHAR(255) DEFAULT \'NULL\'');
        $this->addSql('ALTER TABLE evenement CHANGE titre titre VARCHAR(255) DEFAULT \'NULL\', CHANGE description description VARCHAR(255) DEFAULT \'NULL\', CHANGE date_debut date_debut VARCHAR(255) DEFAULT \'NULL\', CHANGE date_fin date_fin VARCHAR(255) DEFAULT \'NULL\', CHANGE lieu lieu VARCHAR(255) DEFAULT \'NULL\'');
        $this->addSql('ALTER TABLE messenger_messages CHANGE delivered_at delivered_at DATETIME DEFAULT \'NULL\'');
        $this->addSql('ALTER TABLE produit CHANGE nom nom VARCHAR(255) DEFAULT \'NULL\', CHANGE description description VARCHAR(255) DEFAULT \'NULL\', CHANGE prix prix DOUBLE PRECISION DEFAULT \'NULL\', CHANGE categorie categorie VARCHAR(255) DEFAULT \'NULL\', CHANGE image_path image_path VARCHAR(255) DEFAULT \'NULL\', CHANGE taux_promo taux_promo DOUBLE PRECISION DEFAULT \'NULL\'');
        $this->addSql('ALTER TABLE profil CHANGE bio bio VARCHAR(255) DEFAULT \'NULL\', CHANGE telephone telephone VARCHAR(255) DEFAULT \'NULL\', CHANGE image image VARCHAR(255) DEFAULT \'NULL\'');
        $this->addSql('ALTER TABLE profil ADD CONSTRAINT `FK_E6D6B297A76ED395` FOREIGN KEY (user_id) REFERENCES user (user_id) ON DELETE CASCADE');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_E6D6B297A76ED395 ON profil (user_id)');
        $this->addSql('ALTER TABLE reclamation CHANGE objet objet VARCHAR(255) DEFAULT \'NULL\', CHANGE description description VARCHAR(255) DEFAULT \'NULL\', CHANGE date_creation date_creation VARCHAR(255) DEFAULT \'NULL\', CHANGE statut statut VARCHAR(255) DEFAULT \'NULL\', CHANGE priorite priorite VARCHAR(255) DEFAULT \'NULL\', CHANGE type type VARCHAR(255) DEFAULT \'NULL\'');
        $this->addSql('ALTER TABLE thread CHANGE category category VARCHAR(100) DEFAULT \'NULL\', CHANGE tags tags VARCHAR(255) DEFAULT \'NULL\', CHANGE attachments attachments VARCHAR(255) DEFAULT \'NULL\'');
        $this->addSql('ALTER TABLE user ADD xp INT DEFAULT 0 NOT NULL, ADD strikes INT DEFAULT 0 NOT NULL, DROP is_verified, DROP is_phone_verified, DROP totp_secret, DROP face_image_path, CHANGE telephone telephone VARCHAR(20) NOT NULL, CHANGE reset_token reset_token VARCHAR(255) DEFAULT \'NULL\', CHANGE reset_token_requested_at reset_token_requested_at DATETIME DEFAULT \'NULL\', CHANGE role role VARCHAR(50) DEFAULT \'NULL\', CHANGE etatCompte etatCompte VARCHAR(50) DEFAULT \'NULL\', CHANGE date_creation date_creation DATETIME DEFAULT \'NULL\'');
    }
}
