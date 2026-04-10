<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260405130055 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE admin (id INT AUTO_INCREMENT NOT NULL, nom VARCHAR(255) NOT NULL, prenom VARCHAR(255) NOT NULL, email VARCHAR(255) NOT NULL, password VARCHAR(255) NOT NULL, telephone VARCHAR(20) DEFAULT NULL, date_creation DATETIME DEFAULT NULL, UNIQUE INDEX UNIQ_880E0D76E7927C74 (email), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE commande ADD CONSTRAINT FK_6EEAA67DF7384557 FOREIGN KEY (id_produit) REFERENCES produit (id_produit)');
        $this->addSql('ALTER TABLE commande ADD CONSTRAINT FK_6EEAA67DA76ED395 FOREIGN KEY (user_id) REFERENCES user (user_id)');
        $this->addSql('DROP INDEX fk_commande_produit ON commande');
        $this->addSql('CREATE INDEX IDX_6EEAA67DF7384557 ON commande (id_produit)');
        $this->addSql('DROP INDEX fk_commande_user ON commande');
        $this->addSql('CREATE INDEX IDX_6EEAA67DA76ED395 ON commande (user_id)');
        $this->addSql('ALTER TABLE culture MODIFY idCulture INT NOT NULL');
        $this->addSql('DROP INDEX `primary` ON culture');
        $this->addSql('ALTER TABLE culture DROP image, CHANGE nom nom VARCHAR(255) DEFAULT NULL, CHANGE type type VARCHAR(255) DEFAULT NULL, CHANGE superficie superficie DOUBLE PRECISION DEFAULT NULL, CHANGE idCulture id_culture INT AUTO_INCREMENT NOT NULL, CHANGE idUser user_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE culture ADD PRIMARY KEY (id_culture)');
        $this->addSql('ALTER TABLE diagnostic MODIFY idDiagnostic INT NOT NULL');
        $this->addSql('ALTER TABLE diagnostic DROP FOREIGN KEY fk_culture_diag');
        $this->addSql('ALTER TABLE diagnostic DROP FOREIGN KEY fk_culture');
        $this->addSql('DROP INDEX fk_culture_diag ON diagnostic');
        $this->addSql('DROP INDEX `primary` ON diagnostic');
        $this->addSql('ALTER TABLE diagnostic ADD date_diagnostic VARCHAR(255) DEFAULT NULL, ADD informations_complementaires VARCHAR(255) DEFAULT NULL, ADD user_id INT DEFAULT NULL, DROP dateDiagnostic, DROP etat, DROP informationsComplementaires, DROP recommandations, DROP idCulture, DROP disease_detected, DROP confidence, DROP rapport_ia, CHANGE symptomes symptomes VARCHAR(255) DEFAULT NULL, CHANGE idDiagnostic id_diagnostic INT AUTO_INCREMENT NOT NULL, CHANGE idUser id_culture INT DEFAULT NULL');
        $this->addSql('ALTER TABLE diagnostic ADD PRIMARY KEY (id_diagnostic)');
        $this->addSql('ALTER TABLE evenement DROP FOREIGN KEY fk_evenement_user');
        $this->addSql('DROP INDEX idx_evenement_organisateur ON evenement');
        $this->addSql('DROP INDEX idx_evenement_statut ON evenement');
        $this->addSql('ALTER TABLE evenement DROP statut, DROP raison_rejet, DROP image_url, DROP created_at, DROP updated_at, CHANGE organisateur_id organisateur_id INT DEFAULT NULL, CHANGE titre titre VARCHAR(255) DEFAULT NULL, CHANGE description description VARCHAR(255) DEFAULT NULL, CHANGE date_debut date_debut VARCHAR(255) DEFAULT NULL, CHANGE date_fin date_fin VARCHAR(255) DEFAULT NULL, CHANGE lieu lieu VARCHAR(255) DEFAULT NULL, CHANGE capacite_max capacite_max INT DEFAULT NULL');
        $this->addSql('ALTER TABLE produit ADD taux_promo DOUBLE PRECISION DEFAULT NULL, CHANGE id_produit id_produit INT AUTO_INCREMENT NOT NULL, CHANGE nom nom VARCHAR(255) DEFAULT NULL, CHANGE description description VARCHAR(255) DEFAULT NULL, CHANGE prix prix DOUBLE PRECISION DEFAULT NULL, CHANGE categorie categorie VARCHAR(255) DEFAULT NULL, CHANGE actif actif TINYINT(1) DEFAULT NULL, CHANGE promo promo TINYINT(1) DEFAULT NULL, CHANGE imagePath image_path VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE profil DROP FOREIGN KEY profil_ibfk_1');
        $this->addSql('DROP INDEX user_id ON profil');
        $this->addSql('ALTER TABLE profil CHANGE nom nom VARCHAR(255) DEFAULT NULL, CHANGE prenom prenom VARCHAR(255) DEFAULT NULL, CHANGE bio bio VARCHAR(255) DEFAULT NULL, CHANGE telephone telephone VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE reclamation DROP FOREIGN KEY fk_reclamation_user');
        $this->addSql('DROP INDEX idx_reclamation_user ON reclamation');
        $this->addSql('DROP INDEX idx_reclamation_statut ON reclamation');
        $this->addSql('DROP INDEX idx_reclamation_type ON reclamation');
        $this->addSql('ALTER TABLE reclamation ADD user_id INT DEFAULT NULL, DROP id_utilisateur, DROP reponse_admin, DROP date_reponse, DROP created_at, DROP updated_at, CHANGE objet objet VARCHAR(255) DEFAULT NULL, CHANGE description description VARCHAR(255) DEFAULT NULL, CHANGE date_creation date_creation VARCHAR(255) DEFAULT NULL, CHANGE statut statut VARCHAR(255) DEFAULT NULL, CHANGE priorite priorite VARCHAR(255) DEFAULT NULL, CHANGE type type VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE response MODIFY response_id INT NOT NULL');
        $this->addSql('ALTER TABLE response DROP FOREIGN KEY response_ibfk_1');
        $this->addSql('ALTER TABLE response DROP FOREIGN KEY response_ibfk_2');
        $this->addSql('DROP INDEX user_id ON response');
        $this->addSql('DROP INDEX thread_id ON response');
        $this->addSql('DROP INDEX `primary` ON response');
        $this->addSql('ALTER TABLE response ADD content VARCHAR(255) DEFAULT NULL, ADD created_at VARCHAR(255) DEFAULT NULL, ADD parent_response_id INT DEFAULT NULL, ADD like_count INT DEFAULT NULL, DROP contenu, DROP dateCreation, DROP likes, DROP liked_by, CHANGE user_id user_id INT DEFAULT NULL, CHANGE thread_id thread_id INT DEFAULT NULL, CHANGE response_id id INT AUTO_INCREMENT NOT NULL');
        $this->addSql('ALTER TABLE response ADD PRIMARY KEY (id)');
        $this->addSql('ALTER TABLE stock MODIFY idStock INT NOT NULL');
        $this->addSql('DROP INDEX `primary` ON stock');
        $this->addSql('ALTER TABLE stock ADD id_produit INT DEFAULT NULL, ADD seuil_alert INT DEFAULT NULL, DROP seuilAlert, DROP idProduit, CHANGE quantite quantite INT DEFAULT NULL, CHANGE disponible disponible INT DEFAULT NULL, CHANGE idStock id_stock INT AUTO_INCREMENT NOT NULL');
        $this->addSql('ALTER TABLE stock ADD CONSTRAINT FK_4B365660F7384557 FOREIGN KEY (id_produit) REFERENCES produit (id_produit)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_4B365660F7384557 ON stock (id_produit)');
        $this->addSql('ALTER TABLE stock ADD PRIMARY KEY (id_stock)');
        $this->addSql('ALTER TABLE thread MODIFY thread_id INT NOT NULL');
        $this->addSql('ALTER TABLE thread DROP FOREIGN KEY thread_ibfk_1');
        $this->addSql('DROP INDEX user_id ON thread');
        $this->addSql('DROP INDEX `primary` ON thread');
        $this->addSql('ALTER TABLE thread ADD title VARCHAR(255) DEFAULT NULL, ADD content VARCHAR(255) DEFAULT NULL, ADD created_at VARCHAR(255) DEFAULT NULL, ADD attachments VARCHAR(255) DEFAULT NULL, ADD like_count INT DEFAULT NULL, DROP titre, DROP contenu, DROP dateCreation, DROP views, DROP likes, DROP liked_by, CHANGE user_id user_id INT DEFAULT NULL, CHANGE status status VARCHAR(255) DEFAULT NULL, CHANGE category category VARCHAR(255) DEFAULT NULL, CHANGE thread_id id INT AUTO_INCREMENT NOT NULL');
        $this->addSql('ALTER TABLE thread ADD PRIMARY KEY (id)');
        $this->addSql('ALTER TABLE user DROP two_factor_secret, DROP two_factor_enabled, DROP verification_code, DROP code_expiration, CHANGE nom nom VARCHAR(255) NOT NULL, CHANGE email email VARCHAR(255) NOT NULL, CHANGE role role VARCHAR(50) DEFAULT NULL, CHANGE etatCompte etatCompte VARCHAR(50) DEFAULT NULL, CHANGE date_creation date_creation DATETIME DEFAULT NULL, CHANGE telephone telephone VARCHAR(20) NOT NULL');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_8D93D649450FF010 ON user (telephone)');
        $this->addSql('DROP INDEX email ON user');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_8D93D649E7927C74 ON user (email)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP TABLE admin');
        $this->addSql('ALTER TABLE commande DROP FOREIGN KEY FK_6EEAA67DF7384557');
        $this->addSql('ALTER TABLE commande DROP FOREIGN KEY FK_6EEAA67DA76ED395');
        $this->addSql('ALTER TABLE commande DROP FOREIGN KEY FK_6EEAA67DF7384557');
        $this->addSql('ALTER TABLE commande DROP FOREIGN KEY FK_6EEAA67DA76ED395');
        $this->addSql('DROP INDEX idx_6eeaa67df7384557 ON commande');
        $this->addSql('CREATE INDEX fk_commande_produit ON commande (id_produit)');
        $this->addSql('DROP INDEX idx_6eeaa67da76ed395 ON commande');
        $this->addSql('CREATE INDEX fk_commande_user ON commande (user_id)');
        $this->addSql('ALTER TABLE commande ADD CONSTRAINT FK_6EEAA67DF7384557 FOREIGN KEY (id_produit) REFERENCES produit (id_produit)');
        $this->addSql('ALTER TABLE commande ADD CONSTRAINT FK_6EEAA67DA76ED395 FOREIGN KEY (user_id) REFERENCES user (user_id)');
        $this->addSql('ALTER TABLE culture MODIFY id_culture INT NOT NULL');
        $this->addSql('DROP INDEX `PRIMARY` ON culture');
        $this->addSql('ALTER TABLE culture ADD image VARCHAR(255) DEFAULT NULL, CHANGE nom nom VARCHAR(255) NOT NULL, CHANGE type type VARCHAR(100) DEFAULT NULL, CHANGE superficie superficie NUMERIC(10, 2) DEFAULT NULL, CHANGE id_culture idCulture INT AUTO_INCREMENT NOT NULL, CHANGE user_id idUser INT DEFAULT NULL');
        $this->addSql('ALTER TABLE culture ADD PRIMARY KEY (idCulture)');
        $this->addSql('ALTER TABLE diagnostic MODIFY id_diagnostic INT NOT NULL');
        $this->addSql('DROP INDEX `PRIMARY` ON diagnostic');
        $this->addSql('ALTER TABLE diagnostic ADD dateDiagnostic DATE NOT NULL, ADD etat VARCHAR(50) DEFAULT NULL, ADD informationsComplementaires TEXT DEFAULT NULL, ADD recommandations TEXT DEFAULT NULL, ADD idCulture INT NOT NULL, ADD idUser INT DEFAULT NULL, ADD disease_detected VARCHAR(100) DEFAULT NULL, ADD confidence DOUBLE PRECISION DEFAULT NULL, ADD rapport_ia TEXT DEFAULT NULL, DROP date_diagnostic, DROP informations_complementaires, DROP id_culture, DROP user_id, CHANGE symptomes symptomes TEXT DEFAULT NULL, CHANGE id_diagnostic idDiagnostic INT AUTO_INCREMENT NOT NULL');
        $this->addSql('ALTER TABLE diagnostic ADD CONSTRAINT fk_culture_diag FOREIGN KEY (idCulture) REFERENCES culture (idCulture) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE diagnostic ADD CONSTRAINT fk_culture FOREIGN KEY (idCulture) REFERENCES culture (idCulture) ON UPDATE CASCADE ON DELETE CASCADE');
        $this->addSql('CREATE INDEX fk_culture_diag ON diagnostic (idCulture)');
        $this->addSql('ALTER TABLE diagnostic ADD PRIMARY KEY (idDiagnostic)');
        $this->addSql('ALTER TABLE evenement ADD statut VARCHAR(30) DEFAULT \'EN_ATTENTE\' NOT NULL, ADD raison_rejet TEXT DEFAULT NULL, ADD image_url VARCHAR(500) DEFAULT NULL, ADD created_at DATETIME DEFAULT CURRENT_TIMESTAMP, ADD updated_at DATETIME DEFAULT CURRENT_TIMESTAMP, CHANGE titre titre VARCHAR(255) NOT NULL, CHANGE description description TEXT DEFAULT NULL, CHANGE date_debut date_debut DATE NOT NULL, CHANGE date_fin date_fin DATE NOT NULL, CHANGE lieu lieu VARCHAR(255) NOT NULL, CHANGE capacite_max capacite_max INT NOT NULL, CHANGE organisateur_id organisateur_id INT NOT NULL');
        $this->addSql('ALTER TABLE evenement ADD CONSTRAINT fk_evenement_user FOREIGN KEY (organisateur_id) REFERENCES user (user_id) ON UPDATE CASCADE ON DELETE CASCADE');
        $this->addSql('CREATE INDEX idx_evenement_organisateur ON evenement (organisateur_id)');
        $this->addSql('CREATE INDEX idx_evenement_statut ON evenement (statut)');
        $this->addSql('ALTER TABLE produit DROP taux_promo, CHANGE id_produit id_produit BIGINT AUTO_INCREMENT NOT NULL, CHANGE nom nom VARCHAR(100) NOT NULL, CHANGE description description TEXT DEFAULT NULL, CHANGE prix prix DOUBLE PRECISION NOT NULL, CHANGE categorie categorie VARCHAR(50) DEFAULT NULL, CHANGE actif actif TINYINT(1) DEFAULT 1, CHANGE promo promo TINYINT(1) DEFAULT 0, CHANGE image_path imagePath VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE profil CHANGE nom nom VARCHAR(100) NOT NULL, CHANGE prenom prenom VARCHAR(100) NOT NULL, CHANGE bio bio TEXT DEFAULT NULL, CHANGE telephone telephone VARCHAR(20) DEFAULT NULL');
        $this->addSql('ALTER TABLE profil ADD CONSTRAINT profil_ibfk_1 FOREIGN KEY (user_id) REFERENCES user (user_id) ON DELETE CASCADE');
        $this->addSql('CREATE UNIQUE INDEX user_id ON profil (user_id)');
        $this->addSql('ALTER TABLE reclamation ADD id_utilisateur INT NOT NULL, ADD reponse_admin TEXT DEFAULT NULL, ADD date_reponse DATE DEFAULT NULL, ADD created_at DATETIME DEFAULT CURRENT_TIMESTAMP, ADD updated_at DATETIME DEFAULT CURRENT_TIMESTAMP, DROP user_id, CHANGE objet objet VARCHAR(255) NOT NULL, CHANGE description description TEXT NOT NULL, CHANGE date_creation date_creation DATE NOT NULL, CHANGE statut statut VARCHAR(30) DEFAULT \'EN_ATTENTE\' NOT NULL, CHANGE priorite priorite VARCHAR(30) DEFAULT NULL, CHANGE type type VARCHAR(100) DEFAULT NULL');
        $this->addSql('ALTER TABLE reclamation ADD CONSTRAINT fk_reclamation_user FOREIGN KEY (id_utilisateur) REFERENCES user (user_id) ON UPDATE CASCADE ON DELETE CASCADE');
        $this->addSql('CREATE INDEX idx_reclamation_user ON reclamation (id_utilisateur)');
        $this->addSql('CREATE INDEX idx_reclamation_statut ON reclamation (statut)');
        $this->addSql('CREATE INDEX idx_reclamation_type ON reclamation (type)');
        $this->addSql('ALTER TABLE response MODIFY id INT NOT NULL');
        $this->addSql('DROP INDEX `PRIMARY` ON response');
        $this->addSql('ALTER TABLE response ADD contenu TEXT NOT NULL, ADD dateCreation DATETIME DEFAULT CURRENT_TIMESTAMP NOT NULL, ADD likes INT DEFAULT 0, ADD liked_by TEXT DEFAULT \'\', DROP content, DROP created_at, DROP parent_response_id, DROP like_count, CHANGE thread_id thread_id INT NOT NULL, CHANGE user_id user_id INT NOT NULL, CHANGE id response_id INT AUTO_INCREMENT NOT NULL');
        $this->addSql('ALTER TABLE response ADD CONSTRAINT response_ibfk_1 FOREIGN KEY (user_id) REFERENCES user (user_id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE response ADD CONSTRAINT response_ibfk_2 FOREIGN KEY (thread_id) REFERENCES thread (thread_id) ON DELETE CASCADE');
        $this->addSql('CREATE INDEX user_id ON response (user_id)');
        $this->addSql('CREATE INDEX thread_id ON response (thread_id)');
        $this->addSql('ALTER TABLE response ADD PRIMARY KEY (response_id)');
        $this->addSql('ALTER TABLE stock MODIFY id_stock INT NOT NULL');
        $this->addSql('ALTER TABLE stock DROP FOREIGN KEY FK_4B365660F7384557');
        $this->addSql('DROP INDEX UNIQ_4B365660F7384557 ON stock');
        $this->addSql('DROP INDEX `PRIMARY` ON stock');
        $this->addSql('ALTER TABLE stock ADD seuilAlert INT NOT NULL, ADD idProduit INT NOT NULL, DROP id_produit, DROP seuil_alert, CHANGE quantite quantite INT NOT NULL, CHANGE disponible disponible INT NOT NULL, CHANGE id_stock idStock INT AUTO_INCREMENT NOT NULL');
        $this->addSql('ALTER TABLE stock ADD PRIMARY KEY (idStock)');
        $this->addSql('ALTER TABLE thread MODIFY id INT NOT NULL');
        $this->addSql('DROP INDEX `PRIMARY` ON thread');
        $this->addSql('ALTER TABLE thread ADD titre VARCHAR(255) NOT NULL, ADD contenu TEXT NOT NULL, ADD dateCreation DATETIME DEFAULT CURRENT_TIMESTAMP NOT NULL, ADD views INT DEFAULT 0, ADD likes INT DEFAULT 0, ADD liked_by TEXT DEFAULT \'\', DROP title, DROP content, DROP created_at, DROP attachments, DROP like_count, CHANGE status status VARCHAR(255) DEFAULT \'OPEN\', CHANGE user_id user_id INT NOT NULL, CHANGE category category VARCHAR(100) DEFAULT NULL, CHANGE id thread_id INT AUTO_INCREMENT NOT NULL');
        $this->addSql('ALTER TABLE thread ADD CONSTRAINT thread_ibfk_1 FOREIGN KEY (user_id) REFERENCES user (user_id) ON DELETE CASCADE');
        $this->addSql('CREATE INDEX user_id ON thread (user_id)');
        $this->addSql('ALTER TABLE thread ADD PRIMARY KEY (thread_id)');
        $this->addSql('DROP INDEX UNIQ_8D93D649450FF010 ON user');
        $this->addSql('ALTER TABLE user ADD two_factor_secret VARCHAR(255) DEFAULT NULL, ADD two_factor_enabled TINYINT(1) DEFAULT 0, ADD verification_code VARCHAR(10) DEFAULT NULL, ADD code_expiration DATETIME DEFAULT NULL, CHANGE nom nom VARCHAR(100) NOT NULL, CHANGE email email VARCHAR(100) NOT NULL, CHANGE telephone telephone VARCHAR(20) DEFAULT NULL, CHANGE role role VARCHAR(255) NOT NULL, CHANGE etatCompte etatCompte VARCHAR(255) DEFAULT \'ACTIF\', CHANGE date_creation date_creation DATETIME DEFAULT CURRENT_TIMESTAMP NOT NULL');
        $this->addSql('DROP INDEX uniq_8d93d649e7927c74 ON user');
        $this->addSql('CREATE UNIQUE INDEX email ON user (email)');
    }
}
