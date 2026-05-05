<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260428001427 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP TABLE `admin`');
        $this->addSql('ALTER TABLE activity CHANGE end_at end_at DATETIME DEFAULT NULL');
        $this->addSql('ALTER TABLE commande DROP FOREIGN KEY `FK_6EEAA67DA76ED395`');
        $this->addSql('ALTER TABLE commande DROP FOREIGN KEY `FK_6EEAA67DF7384557`');
        $this->addSql('ALTER TABLE commande CHANGE date_commande date_commande VARCHAR(255) DEFAULT NULL, CHANGE statut statut VARCHAR(255) DEFAULT NULL, CHANGE prix_total prix_total DOUBLE PRECISION DEFAULT NULL');
        $this->addSql('ALTER TABLE commande ADD CONSTRAINT FK_6EEAA67DA76ED395 FOREIGN KEY (user_id) REFERENCES user (user_id)');
        $this->addSql('ALTER TABLE commande ADD CONSTRAINT FK_6EEAA67DF7384557 FOREIGN KEY (id_produit) REFERENCES produit (id_produit)');
        $this->addSql('ALTER TABLE culture DROP FOREIGN KEY `FK_B6A99CEBFE6E88D7`');
        $this->addSql('ALTER TABLE culture CHANGE nom nom VARCHAR(255) DEFAULT NULL, CHANGE type type VARCHAR(255) DEFAULT NULL, CHANGE superficie superficie DOUBLE PRECISION DEFAULT NULL, CHANGE localisation localisation VARCHAR(255) DEFAULT NULL, CHANGE image image VARCHAR(255) DEFAULT NULL, CHANGE dateSemis dateSemis DATETIME DEFAULT NULL');
        $this->addSql('ALTER TABLE culture ADD CONSTRAINT FK_B6A99CEBFE6E88D7 FOREIGN KEY (idUser) REFERENCES user (user_id)');
        $this->addSql('ALTER TABLE diagnostic DROP FOREIGN KEY `FK_FA7C8889A610F719`');
        $this->addSql('ALTER TABLE diagnostic DROP FOREIGN KEY `FK_FA7C8889FE6E88D7`');
        $this->addSql('ALTER TABLE diagnostic CHANGE dateDiagnostic dateDiagnostic VARCHAR(255) DEFAULT NULL, CHANGE symptomes symptomes VARCHAR(255) DEFAULT NULL, CHANGE informationsComplementaires informationsComplementaires VARCHAR(255) DEFAULT NULL, CHANGE image image VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE diagnostic ADD CONSTRAINT FK_FA7C8889A610F719 FOREIGN KEY (idCulture) REFERENCES culture (idCulture)');
        $this->addSql('ALTER TABLE diagnostic ADD CONSTRAINT FK_FA7C8889FE6E88D7 FOREIGN KEY (idUser) REFERENCES user (user_id)');
        $this->addSql('ALTER TABLE evenement DROP FOREIGN KEY `FK_B26681ED936B2FA`');
        $this->addSql('ALTER TABLE evenement CHANGE titre titre VARCHAR(150) DEFAULT NULL, CHANGE date_debut date_debut DATETIME DEFAULT NULL, CHANGE date_fin date_fin DATETIME DEFAULT NULL, CHANGE lieu lieu VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE evenement ADD CONSTRAINT FK_B26681ED936B2FA FOREIGN KEY (organisateur_id) REFERENCES user (user_id)');
        $this->addSql('ALTER TABLE ext_log_entries CHANGE object_id object_id VARCHAR(64) DEFAULT NULL, CHANGE data data JSON DEFAULT NULL, CHANGE username username VARCHAR(191) DEFAULT NULL');
        $this->addSql('ALTER TABLE produit CHANGE nom nom VARCHAR(255) DEFAULT NULL, CHANGE description description VARCHAR(255) DEFAULT NULL, CHANGE prix prix DOUBLE PRECISION DEFAULT NULL, CHANGE categorie categorie VARCHAR(255) DEFAULT NULL, CHANGE image_path image_path VARCHAR(255) DEFAULT NULL, CHANGE taux_promo taux_promo DOUBLE PRECISION DEFAULT NULL');
        $this->addSql('ALTER TABLE profil CHANGE bio bio VARCHAR(255) DEFAULT NULL, CHANGE telephone telephone VARCHAR(255) DEFAULT NULL, CHANGE image image VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE reclamation DROP FOREIGN KEY `FK_CE60640450EAE44`');
        $this->addSql('ALTER TABLE reclamation CHANGE objet objet VARCHAR(100) DEFAULT NULL, CHANGE date_creation date_creation DATETIME DEFAULT NULL, CHANGE statut statut VARCHAR(20) DEFAULT NULL, CHANGE priorite priorite VARCHAR(20) DEFAULT NULL, CHANGE type type VARCHAR(50) DEFAULT NULL');
        $this->addSql('ALTER TABLE reclamation ADD CONSTRAINT FK_CE60640450EAE44 FOREIGN KEY (id_utilisateur) REFERENCES user (user_id)');
        $this->addSql('ALTER TABLE response DROP FOREIGN KEY `FK_3E7B0BFB90DF3A30`');
        $this->addSql('ALTER TABLE response DROP FOREIGN KEY `FK_3E7B0BFBA76ED395`');
        $this->addSql('ALTER TABLE response DROP FOREIGN KEY `FK_3E7B0BFBE2904019`');
        $this->addSql('ALTER TABLE response ADD CONSTRAINT FK_3E7B0BFB90DF3A30 FOREIGN KEY (parent_response_id) REFERENCES response (id)');
        $this->addSql('ALTER TABLE response ADD CONSTRAINT FK_3E7B0BFBA76ED395 FOREIGN KEY (user_id) REFERENCES user (user_id)');
        $this->addSql('ALTER TABLE response ADD CONSTRAINT FK_3E7B0BFBE2904019 FOREIGN KEY (thread_id) REFERENCES thread (id)');
        $this->addSql('ALTER TABLE stock DROP FOREIGN KEY `FK_4B365660F7384557`');
        $this->addSql('ALTER TABLE stock ADD CONSTRAINT FK_4B365660F7384557 FOREIGN KEY (id_produit) REFERENCES produit (id_produit)');
        $this->addSql('ALTER TABLE thread DROP FOREIGN KEY `FK_31204C83A76ED395`');
        $this->addSql('ALTER TABLE thread CHANGE category category VARCHAR(100) DEFAULT NULL, CHANGE tags tags VARCHAR(255) DEFAULT NULL, CHANGE attachments attachments VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE thread ADD CONSTRAINT FK_31204C83A76ED395 FOREIGN KEY (user_id) REFERENCES user (user_id)');
        $this->addSql('ALTER TABLE user CHANGE role role VARCHAR(50) DEFAULT NULL, CHANGE etatCompte etatCompte VARCHAR(50) DEFAULT NULL, CHANGE date_creation date_creation DATETIME DEFAULT NULL, CHANGE telephone telephone VARCHAR(20) DEFAULT NULL, CHANGE reset_token reset_token VARCHAR(255) DEFAULT NULL, CHANGE reset_token_requested_at reset_token_requested_at DATETIME DEFAULT NULL, CHANGE totp_secret totp_secret VARCHAR(255) DEFAULT NULL, CHANGE face_image_path face_image_path VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE messenger_messages CHANGE delivered_at delivered_at DATETIME DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE `admin` (id INT AUTO_INCREMENT NOT NULL, nom VARCHAR(255) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`, prenom VARCHAR(255) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`, email VARCHAR(255) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`, password VARCHAR(255) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`, telephone VARCHAR(20) CHARACTER SET utf8mb4 DEFAULT \'NULL\' COLLATE `utf8mb4_unicode_ci`, photo VARCHAR(255) CHARACTER SET utf8mb4 DEFAULT \'NULL\' COLLATE `utf8mb4_unicode_ci`, date_creation DATETIME DEFAULT \'NULL\', reset_token VARCHAR(255) CHARACTER SET utf8mb4 DEFAULT \'NULL\' COLLATE `utf8mb4_unicode_ci`, reset_token_requested_at DATETIME DEFAULT \'NULL\', UNIQUE INDEX UNIQ_880E0D76E7927C74 (email), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('ALTER TABLE activity CHANGE end_at end_at DATETIME DEFAULT \'NULL\'');
        $this->addSql('ALTER TABLE commande DROP FOREIGN KEY FK_6EEAA67DF7384557');
        $this->addSql('ALTER TABLE commande DROP FOREIGN KEY FK_6EEAA67DA76ED395');
        $this->addSql('ALTER TABLE commande CHANGE date_commande date_commande VARCHAR(255) DEFAULT \'NULL\', CHANGE statut statut VARCHAR(255) DEFAULT \'NULL\', CHANGE prix_total prix_total DOUBLE PRECISION DEFAULT \'NULL\'');
        $this->addSql('ALTER TABLE commande ADD CONSTRAINT `FK_6EEAA67DF7384557` FOREIGN KEY (id_produit) REFERENCES produit (id_produit) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE commande ADD CONSTRAINT `FK_6EEAA67DA76ED395` FOREIGN KEY (user_id) REFERENCES user (user_id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE culture DROP FOREIGN KEY FK_B6A99CEBFE6E88D7');
        $this->addSql('ALTER TABLE culture CHANGE nom nom VARCHAR(255) DEFAULT \'NULL\', CHANGE type type VARCHAR(255) DEFAULT \'NULL\', CHANGE superficie superficie DOUBLE PRECISION DEFAULT \'NULL\', CHANGE localisation localisation VARCHAR(255) DEFAULT \'NULL\', CHANGE image image VARCHAR(255) DEFAULT \'NULL\', CHANGE dateSemis dateSemis DATETIME DEFAULT \'NULL\'');
        $this->addSql('ALTER TABLE culture ADD CONSTRAINT `FK_B6A99CEBFE6E88D7` FOREIGN KEY (idUser) REFERENCES user (user_id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE diagnostic DROP FOREIGN KEY FK_FA7C8889A610F719');
        $this->addSql('ALTER TABLE diagnostic DROP FOREIGN KEY FK_FA7C8889FE6E88D7');
        $this->addSql('ALTER TABLE diagnostic CHANGE image image VARCHAR(255) DEFAULT \'NULL\', CHANGE dateDiagnostic dateDiagnostic VARCHAR(255) DEFAULT \'NULL\', CHANGE symptomes symptomes VARCHAR(255) DEFAULT \'NULL\', CHANGE informationsComplementaires informationsComplementaires VARCHAR(255) DEFAULT \'NULL\'');
        $this->addSql('ALTER TABLE diagnostic ADD CONSTRAINT `FK_FA7C8889A610F719` FOREIGN KEY (idCulture) REFERENCES culture (idCulture) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE diagnostic ADD CONSTRAINT `FK_FA7C8889FE6E88D7` FOREIGN KEY (idUser) REFERENCES user (user_id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE evenement DROP FOREIGN KEY FK_B26681ED936B2FA');
        $this->addSql('ALTER TABLE evenement CHANGE titre titre VARCHAR(150) DEFAULT \'NULL\', CHANGE date_debut date_debut DATETIME DEFAULT \'NULL\', CHANGE date_fin date_fin DATETIME DEFAULT \'NULL\', CHANGE lieu lieu VARCHAR(255) DEFAULT \'NULL\'');
        $this->addSql('ALTER TABLE evenement ADD CONSTRAINT `FK_B26681ED936B2FA` FOREIGN KEY (organisateur_id) REFERENCES user (user_id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE ext_log_entries CHANGE object_id object_id VARCHAR(64) DEFAULT \'NULL\', CHANGE data data LONGTEXT DEFAULT NULL COLLATE `utf8mb4_bin`, CHANGE username username VARCHAR(191) DEFAULT \'NULL\'');
        $this->addSql('ALTER TABLE messenger_messages CHANGE delivered_at delivered_at DATETIME DEFAULT \'NULL\'');
        $this->addSql('ALTER TABLE produit CHANGE nom nom VARCHAR(255) DEFAULT \'NULL\', CHANGE description description VARCHAR(255) DEFAULT \'NULL\', CHANGE prix prix DOUBLE PRECISION DEFAULT \'NULL\', CHANGE categorie categorie VARCHAR(255) DEFAULT \'NULL\', CHANGE image_path image_path VARCHAR(255) DEFAULT \'NULL\', CHANGE taux_promo taux_promo DOUBLE PRECISION DEFAULT \'NULL\'');
        $this->addSql('ALTER TABLE profil CHANGE bio bio VARCHAR(255) DEFAULT \'NULL\', CHANGE telephone telephone VARCHAR(255) DEFAULT \'NULL\', CHANGE image image VARCHAR(255) DEFAULT \'NULL\'');
        $this->addSql('ALTER TABLE reclamation DROP FOREIGN KEY FK_CE60640450EAE44');
        $this->addSql('ALTER TABLE reclamation CHANGE objet objet VARCHAR(100) DEFAULT \'NULL\', CHANGE date_creation date_creation DATETIME DEFAULT \'NULL\', CHANGE statut statut VARCHAR(20) DEFAULT \'NULL\', CHANGE priorite priorite VARCHAR(20) DEFAULT \'NULL\', CHANGE type type VARCHAR(50) DEFAULT \'NULL\'');
        $this->addSql('ALTER TABLE reclamation ADD CONSTRAINT `FK_CE60640450EAE44` FOREIGN KEY (id_utilisateur) REFERENCES user (user_id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE response DROP FOREIGN KEY FK_3E7B0BFBE2904019');
        $this->addSql('ALTER TABLE response DROP FOREIGN KEY FK_3E7B0BFBA76ED395');
        $this->addSql('ALTER TABLE response DROP FOREIGN KEY FK_3E7B0BFB90DF3A30');
        $this->addSql('ALTER TABLE response ADD CONSTRAINT `FK_3E7B0BFBE2904019` FOREIGN KEY (thread_id) REFERENCES thread (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE response ADD CONSTRAINT `FK_3E7B0BFBA76ED395` FOREIGN KEY (user_id) REFERENCES user (user_id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE response ADD CONSTRAINT `FK_3E7B0BFB90DF3A30` FOREIGN KEY (parent_response_id) REFERENCES response (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE stock DROP FOREIGN KEY FK_4B365660F7384557');
        $this->addSql('ALTER TABLE stock ADD CONSTRAINT `FK_4B365660F7384557` FOREIGN KEY (id_produit) REFERENCES produit (id_produit) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE thread DROP FOREIGN KEY FK_31204C83A76ED395');
        $this->addSql('ALTER TABLE thread CHANGE category category VARCHAR(100) DEFAULT \'NULL\', CHANGE tags tags VARCHAR(255) DEFAULT \'NULL\', CHANGE attachments attachments VARCHAR(255) DEFAULT \'NULL\'');
        $this->addSql('ALTER TABLE thread ADD CONSTRAINT `FK_31204C83A76ED395` FOREIGN KEY (user_id) REFERENCES user (user_id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE user CHANGE telephone telephone VARCHAR(20) DEFAULT \'NULL\', CHANGE reset_token reset_token VARCHAR(255) DEFAULT \'NULL\', CHANGE reset_token_requested_at reset_token_requested_at DATETIME DEFAULT \'NULL\', CHANGE role role VARCHAR(50) DEFAULT \'NULL\', CHANGE etatCompte etatCompte VARCHAR(50) DEFAULT \'NULL\', CHANGE date_creation date_creation DATETIME DEFAULT \'NULL\', CHANGE totp_secret totp_secret VARCHAR(255) DEFAULT \'NULL\', CHANGE face_image_path face_image_path VARCHAR(255) DEFAULT \'NULL\'');
    }
}
