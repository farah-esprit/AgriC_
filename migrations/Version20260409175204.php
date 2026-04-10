<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260409175204 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE messenger_messages (id BIGINT AUTO_INCREMENT NOT NULL, body LONGTEXT NOT NULL, headers LONGTEXT NOT NULL, queue_name VARCHAR(190) NOT NULL, created_at DATETIME NOT NULL, available_at DATETIME NOT NULL, delivered_at DATETIME DEFAULT NULL, INDEX IDX_75EA56E0FB7336F0E3BD61CE16BA31DBBF396750 (queue_name, available_at, delivered_at, id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('ALTER TABLE `admin` ADD photo VARCHAR(255) DEFAULT NULL, CHANGE telephone telephone VARCHAR(20) DEFAULT NULL, CHANGE date_creation date_creation DATETIME DEFAULT NULL');
        $this->addSql('ALTER TABLE commande CHANGE date_commande date_commande VARCHAR(255) DEFAULT NULL, CHANGE statut statut VARCHAR(255) DEFAULT NULL, CHANGE prix_total prix_total DOUBLE PRECISION DEFAULT NULL');
        $this->addSql('ALTER TABLE culture CHANGE nom nom VARCHAR(255) DEFAULT NULL, CHANGE type type VARCHAR(255) DEFAULT NULL, CHANGE superficie superficie DOUBLE PRECISION DEFAULT NULL, CHANGE localisation localisation VARCHAR(255) DEFAULT NULL, CHANGE image image VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE diagnostic DROP FOREIGN KEY `fk_culture`');
        $this->addSql('ALTER TABLE diagnostic DROP FOREIGN KEY `fk_culture_diag`');
        $this->addSql('DROP INDEX fk_culture_diag ON diagnostic');
        $this->addSql('ALTER TABLE diagnostic DROP etat, DROP recommandations, DROP disease_detected, DROP confidence, DROP rapport_ia, CHANGE dateDiagnostic dateDiagnostic VARCHAR(255) DEFAULT NULL, CHANGE symptomes symptomes VARCHAR(255) DEFAULT NULL, CHANGE informationsComplementaires informationsComplementaires VARCHAR(255) DEFAULT NULL, CHANGE idCulture idCulture INT DEFAULT NULL');
        $this->addSql('ALTER TABLE evenement CHANGE titre titre VARCHAR(255) DEFAULT NULL, CHANGE description description VARCHAR(255) DEFAULT NULL, CHANGE date_debut date_debut VARCHAR(255) DEFAULT NULL, CHANGE date_fin date_fin VARCHAR(255) DEFAULT NULL, CHANGE lieu lieu VARCHAR(255) DEFAULT NULL, CHANGE organisateur_id organisateur_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE evenement ADD CONSTRAINT FK_B26681ED936B2FA FOREIGN KEY (organisateur_id) REFERENCES user (user_id)');
        $this->addSql('CREATE INDEX IDX_B26681ED936B2FA ON evenement (organisateur_id)');
        $this->addSql('ALTER TABLE produit CHANGE nom nom VARCHAR(255) DEFAULT NULL, CHANGE description description VARCHAR(255) DEFAULT NULL, CHANGE prix prix DOUBLE PRECISION DEFAULT NULL, CHANGE categorie categorie VARCHAR(255) DEFAULT NULL, CHANGE image_path image_path VARCHAR(255) DEFAULT NULL, CHANGE taux_promo taux_promo DOUBLE PRECISION DEFAULT NULL');
        $this->addSql('DROP INDEX user_id ON profil');
        $this->addSql('ALTER TABLE profil DROP nom, DROP prenom, CHANGE bio bio VARCHAR(255) DEFAULT NULL, CHANGE telephone telephone VARCHAR(255) DEFAULT NULL, CHANGE image image VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE reclamation ADD user_id INT DEFAULT NULL, DROP id_utilisateur, CHANGE objet objet VARCHAR(255) DEFAULT NULL, CHANGE description description VARCHAR(255) DEFAULT NULL, CHANGE date_creation date_creation VARCHAR(255) DEFAULT NULL, CHANGE statut statut VARCHAR(255) DEFAULT NULL, CHANGE priorite priorite VARCHAR(255) DEFAULT NULL, CHANGE type type VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE reclamation ADD CONSTRAINT FK_CE606404A76ED395 FOREIGN KEY (user_id) REFERENCES user (user_id)');
        $this->addSql('CREATE INDEX IDX_CE606404A76ED395 ON reclamation (user_id)');
        $this->addSql('ALTER TABLE response CHANGE content content LONGTEXT NOT NULL, CHANGE created_at created_at DATETIME NOT NULL, CHANGE like_count like_count INT NOT NULL');
        $this->addSql('ALTER TABLE response ADD CONSTRAINT FK_3E7B0BFBE2904019 FOREIGN KEY (thread_id) REFERENCES thread (id)');
        $this->addSql('ALTER TABLE response ADD CONSTRAINT FK_3E7B0BFBA76ED395 FOREIGN KEY (user_id) REFERENCES user (user_id)');
        $this->addSql('ALTER TABLE response ADD CONSTRAINT FK_3E7B0BFB90DF3A30 FOREIGN KEY (parent_response_id) REFERENCES response (id)');
        $this->addSql('CREATE INDEX IDX_3E7B0BFBE2904019 ON response (thread_id)');
        $this->addSql('CREATE INDEX IDX_3E7B0BFBA76ED395 ON response (user_id)');
        $this->addSql('CREATE INDEX IDX_3E7B0BFB90DF3A30 ON response (parent_response_id)');
        $this->addSql('ALTER TABLE thread CHANGE title title VARCHAR(255) NOT NULL, CHANGE content content LONGTEXT NOT NULL, CHANGE created_at created_at DATETIME NOT NULL, CHANGE status status VARCHAR(20) NOT NULL, CHANGE category category VARCHAR(100) DEFAULT NULL, CHANGE tags tags VARCHAR(255) DEFAULT NULL, CHANGE attachments attachments VARCHAR(255) DEFAULT NULL, CHANGE like_count like_count INT NOT NULL');
        $this->addSql('ALTER TABLE thread ADD CONSTRAINT FK_31204C83A76ED395 FOREIGN KEY (user_id) REFERENCES user (user_id)');
        $this->addSql('CREATE INDEX IDX_31204C83A76ED395 ON thread (user_id)');
        $this->addSql('DROP INDEX email ON user');
        $this->addSql('ALTER TABLE user DROP two_factor_secret, DROP two_factor_enabled, DROP verification_code, DROP code_expiration, CHANGE nom nom VARCHAR(255) NOT NULL, CHANGE email email VARCHAR(255) NOT NULL, CHANGE role role VARCHAR(50) DEFAULT NULL, CHANGE etatCompte etatCompte VARCHAR(50) DEFAULT NULL, CHANGE date_creation date_creation DATETIME DEFAULT NULL, CHANGE telephone telephone VARCHAR(20) NOT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP TABLE messenger_messages');
        $this->addSql('ALTER TABLE `admin` DROP photo, CHANGE telephone telephone VARCHAR(20) DEFAULT \'NULL\', CHANGE date_creation date_creation DATETIME DEFAULT \'NULL\'');
        $this->addSql('ALTER TABLE commande CHANGE date_commande date_commande VARCHAR(255) DEFAULT \'NULL\', CHANGE statut statut VARCHAR(255) DEFAULT \'NULL\', CHANGE prix_total prix_total FLOAT DEFAULT \'NULL\'');
        $this->addSql('ALTER TABLE culture CHANGE nom nom VARCHAR(255) NOT NULL, CHANGE type type VARCHAR(100) DEFAULT \'NULL\', CHANGE superficie superficie NUMERIC(10, 2) DEFAULT \'NULL\', CHANGE localisation localisation VARCHAR(255) DEFAULT \'NULL\', CHANGE image image VARCHAR(255) DEFAULT \'NULL\'');
        $this->addSql('ALTER TABLE diagnostic ADD etat VARCHAR(50) DEFAULT \'NULL\', ADD recommandations TEXT DEFAULT NULL, ADD disease_detected VARCHAR(100) DEFAULT \'NULL\', ADD confidence DOUBLE PRECISION DEFAULT \'NULL\', ADD rapport_ia TEXT DEFAULT NULL, CHANGE dateDiagnostic dateDiagnostic DATE NOT NULL, CHANGE symptomes symptomes TEXT DEFAULT NULL, CHANGE informationsComplementaires informationsComplementaires TEXT DEFAULT NULL, CHANGE idCulture idCulture INT NOT NULL');
        $this->addSql('ALTER TABLE diagnostic ADD CONSTRAINT `fk_culture` FOREIGN KEY (idCulture) REFERENCES culture (idCulture) ON UPDATE CASCADE ON DELETE CASCADE');
        $this->addSql('ALTER TABLE diagnostic ADD CONSTRAINT `fk_culture_diag` FOREIGN KEY (idCulture) REFERENCES culture (idCulture) ON DELETE CASCADE');
        $this->addSql('CREATE INDEX fk_culture_diag ON diagnostic (idCulture)');
        $this->addSql('ALTER TABLE evenement DROP FOREIGN KEY FK_B26681ED936B2FA');
        $this->addSql('DROP INDEX IDX_B26681ED936B2FA ON evenement');
        $this->addSql('ALTER TABLE evenement CHANGE titre titre VARCHAR(100) NOT NULL, CHANGE description description TEXT NOT NULL, CHANGE date_debut date_debut DATETIME NOT NULL, CHANGE date_fin date_fin DATETIME NOT NULL, CHANGE lieu lieu VARCHAR(150) DEFAULT \'NULL\', CHANGE organisateur_id organisateur_id INT NOT NULL');
        $this->addSql('ALTER TABLE produit CHANGE nom nom VARCHAR(255) DEFAULT \'NULL\', CHANGE description description VARCHAR(255) DEFAULT \'NULL\', CHANGE prix prix DOUBLE PRECISION DEFAULT \'NULL\', CHANGE categorie categorie VARCHAR(255) DEFAULT \'NULL\', CHANGE image_path image_path VARCHAR(255) DEFAULT \'NULL\', CHANGE taux_promo taux_promo DOUBLE PRECISION DEFAULT \'NULL\'');
        $this->addSql('ALTER TABLE profil ADD nom VARCHAR(100) NOT NULL, ADD prenom VARCHAR(100) NOT NULL, CHANGE bio bio TEXT DEFAULT NULL, CHANGE telephone telephone VARCHAR(20) DEFAULT \'NULL\', CHANGE image image VARCHAR(255) DEFAULT \'NULL\'');
        $this->addSql('CREATE UNIQUE INDEX user_id ON profil (user_id)');
        $this->addSql('ALTER TABLE reclamation DROP FOREIGN KEY FK_CE606404A76ED395');
        $this->addSql('DROP INDEX IDX_CE606404A76ED395 ON reclamation');
        $this->addSql('ALTER TABLE reclamation ADD id_utilisateur INT NOT NULL, DROP user_id, CHANGE objet objet VARCHAR(100) NOT NULL, CHANGE description description TEXT NOT NULL, CHANGE date_creation date_creation DATE NOT NULL, CHANGE statut statut VARCHAR(30) NOT NULL, CHANGE priorite priorite VARCHAR(20) NOT NULL, CHANGE type type VARCHAR(30) NOT NULL');
        $this->addSql('ALTER TABLE response DROP FOREIGN KEY FK_3E7B0BFBE2904019');
        $this->addSql('ALTER TABLE response DROP FOREIGN KEY FK_3E7B0BFBA76ED395');
        $this->addSql('ALTER TABLE response DROP FOREIGN KEY FK_3E7B0BFB90DF3A30');
        $this->addSql('DROP INDEX IDX_3E7B0BFBE2904019 ON response');
        $this->addSql('DROP INDEX IDX_3E7B0BFBA76ED395 ON response');
        $this->addSql('DROP INDEX IDX_3E7B0BFB90DF3A30 ON response');
        $this->addSql('ALTER TABLE response CHANGE content content TEXT NOT NULL, CHANGE created_at created_at DATETIME DEFAULT \'current_timestamp()\', CHANGE like_count like_count INT DEFAULT 0');
        $this->addSql('ALTER TABLE thread DROP FOREIGN KEY FK_31204C83A76ED395');
        $this->addSql('DROP INDEX IDX_31204C83A76ED395 ON thread');
        $this->addSql('ALTER TABLE thread CHANGE title title VARCHAR(150) NOT NULL, CHANGE content content TEXT NOT NULL, CHANGE created_at created_at DATETIME DEFAULT \'current_timestamp()\', CHANGE status status ENUM(\'OPEN\', \'CLOSED\') DEFAULT \'\'\'OPEN\'\'\', CHANGE category category VARCHAR(50) DEFAULT \'NULL\', CHANGE tags tags VARCHAR(100) DEFAULT \'NULL\', CHANGE attachments attachments TEXT DEFAULT NULL, CHANGE like_count like_count INT DEFAULT 0');
        $this->addSql('ALTER TABLE user ADD two_factor_secret VARCHAR(255) DEFAULT \'NULL\', ADD two_factor_enabled TINYINT DEFAULT 0, ADD verification_code VARCHAR(10) DEFAULT \'NULL\', ADD code_expiration DATETIME DEFAULT \'NULL\', CHANGE nom nom VARCHAR(100) NOT NULL, CHANGE email email VARCHAR(100) NOT NULL, CHANGE telephone telephone VARCHAR(20) DEFAULT \'NULL\', CHANGE role role ENUM(\'AGRICULTEUR\', \'EXPERT\', \'FOURNISSEUR\', \'ADMIN\') NOT NULL, CHANGE etatCompte etatCompte ENUM(\'ACTIF\', \'INACTIF\', \'BLOQUE\') DEFAULT \'\'\'ACTIF\'\'\', CHANGE date_creation date_creation DATETIME DEFAULT \'current_timestamp()\' NOT NULL');
        $this->addSql('CREATE UNIQUE INDEX email ON user (email)');
    }
}
