<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260406185124 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE culture CHANGE idCulture idCulture INT AUTO_INCREMENT NOT NULL, CHANGE nom nom VARCHAR(255) DEFAULT NULL, CHANGE type type VARCHAR(255) DEFAULT NULL, CHANGE superficie superficie DOUBLE PRECISION DEFAULT NULL');
        $this->addSql('ALTER TABLE diagnostic DROP FOREIGN KEY fk_culture_diag');
        $this->addSql('ALTER TABLE diagnostic DROP FOREIGN KEY fk_culture');
        $this->addSql('DROP INDEX fk_culture_diag ON diagnostic');
        $this->addSql('ALTER TABLE diagnostic DROP etat, DROP recommandations, DROP disease_detected, DROP confidence, DROP rapport_ia, CHANGE dateDiagnostic dateDiagnostic VARCHAR(255) DEFAULT NULL, CHANGE symptomes symptomes VARCHAR(255) DEFAULT NULL, CHANGE informationsComplementaires informationsComplementaires VARCHAR(255) DEFAULT NULL, CHANGE idCulture idCulture INT DEFAULT NULL');
        $this->addSql('ALTER TABLE evenement DROP end, CHANGE titre titre VARCHAR(255) DEFAULT NULL, CHANGE description description VARCHAR(255) DEFAULT NULL, CHANGE date_debut date_debut VARCHAR(255) DEFAULT NULL, CHANGE date_fin date_fin VARCHAR(255) DEFAULT NULL, CHANGE lieu lieu VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE evenement ADD CONSTRAINT FK_B26681ED936B2FA FOREIGN KEY (organisateur_id) REFERENCES user (user_id)');
        $this->addSql('CREATE INDEX IDX_B26681ED936B2FA ON evenement (organisateur_id)');
        $this->addSql('DROP INDEX user_id ON profil');
        $this->addSql('ALTER TABLE profil DROP nom, DROP prenom, CHANGE bio bio VARCHAR(255) DEFAULT NULL, CHANGE telephone telephone VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE reclamation DROP utilisateur_id, CHANGE objet objet VARCHAR(255) DEFAULT NULL, CHANGE description description VARCHAR(255) DEFAULT NULL, CHANGE date_creation date_creation VARCHAR(255) DEFAULT NULL, CHANGE statut statut VARCHAR(255) DEFAULT NULL, CHANGE priorite priorite VARCHAR(255) DEFAULT NULL, CHANGE type type VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE reclamation ADD CONSTRAINT FK_CE606404A76ED395 FOREIGN KEY (user_id) REFERENCES user (user_id)');
        $this->addSql('CREATE INDEX IDX_CE606404A76ED395 ON reclamation (user_id)');
        $this->addSql('ALTER TABLE response CHANGE content content VARCHAR(255) DEFAULT NULL, CHANGE created_at created_at VARCHAR(255) DEFAULT NULL, CHANGE thread_id thread_id INT DEFAULT NULL, CHANGE user_id user_id INT DEFAULT NULL, CHANGE like_count like_count INT DEFAULT NULL');
        $this->addSql('ALTER TABLE thread CHANGE title title VARCHAR(255) DEFAULT NULL, CHANGE content content VARCHAR(255) DEFAULT NULL, CHANGE created_at created_at VARCHAR(255) DEFAULT NULL, CHANGE status status VARCHAR(255) DEFAULT NULL, CHANGE user_id user_id INT DEFAULT NULL, CHANGE category category VARCHAR(255) DEFAULT NULL, CHANGE tags tags VARCHAR(255) DEFAULT NULL, CHANGE attachments attachments VARCHAR(255) DEFAULT NULL, CHANGE like_count like_count INT DEFAULT NULL');
        $this->addSql('ALTER TABLE user DROP two_factor_secret, DROP two_factor_enabled, DROP verification_code, DROP code_expiration, CHANGE nom nom VARCHAR(255) NOT NULL, CHANGE email email VARCHAR(255) NOT NULL, CHANGE role role VARCHAR(50) DEFAULT NULL, CHANGE etatCompte etatCompte VARCHAR(50) DEFAULT NULL, CHANGE date_creation date_creation DATETIME DEFAULT NULL, CHANGE telephone telephone VARCHAR(20) NOT NULL');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_8D93D649450FF010 ON user (telephone)');
        $this->addSql('DROP INDEX email ON user');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_8D93D649E7927C74 ON user (email)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE culture CHANGE idCulture idCulture INT NOT NULL, CHANGE nom nom VARCHAR(255) NOT NULL, CHANGE type type VARCHAR(100) DEFAULT NULL, CHANGE superficie superficie NUMERIC(10, 2) DEFAULT NULL');
        $this->addSql('ALTER TABLE diagnostic ADD etat VARCHAR(50) DEFAULT NULL, ADD recommandations TEXT DEFAULT NULL, ADD disease_detected VARCHAR(100) DEFAULT NULL, ADD confidence DOUBLE PRECISION DEFAULT NULL, ADD rapport_ia TEXT DEFAULT NULL, CHANGE dateDiagnostic dateDiagnostic DATE NOT NULL, CHANGE symptomes symptomes TEXT DEFAULT NULL, CHANGE informationsComplementaires informationsComplementaires TEXT DEFAULT NULL, CHANGE idCulture idCulture INT NOT NULL');
        $this->addSql('ALTER TABLE diagnostic ADD CONSTRAINT fk_culture_diag FOREIGN KEY (idCulture) REFERENCES culture (idCulture) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE diagnostic ADD CONSTRAINT fk_culture FOREIGN KEY (idCulture) REFERENCES culture (idCulture) ON UPDATE CASCADE ON DELETE CASCADE');
        $this->addSql('CREATE INDEX fk_culture_diag ON diagnostic (idCulture)');
        $this->addSql('ALTER TABLE evenement DROP FOREIGN KEY FK_B26681ED936B2FA');
        $this->addSql('DROP INDEX IDX_B26681ED936B2FA ON evenement');
        $this->addSql('ALTER TABLE evenement ADD end VARCHAR(255) DEFAULT NULL, CHANGE titre titre VARCHAR(100) NOT NULL, CHANGE description description TEXT NOT NULL, CHANGE date_debut date_debut DATETIME NOT NULL, CHANGE date_fin date_fin DATETIME NOT NULL, CHANGE lieu lieu VARCHAR(150) DEFAULT NULL');
        $this->addSql('ALTER TABLE profil ADD nom VARCHAR(100) NOT NULL, ADD prenom VARCHAR(100) NOT NULL, CHANGE bio bio TEXT DEFAULT NULL, CHANGE telephone telephone VARCHAR(20) DEFAULT NULL');
        $this->addSql('CREATE UNIQUE INDEX user_id ON profil (user_id)');
        $this->addSql('ALTER TABLE reclamation DROP FOREIGN KEY FK_CE606404A76ED395');
        $this->addSql('DROP INDEX IDX_CE606404A76ED395 ON reclamation');
        $this->addSql('ALTER TABLE reclamation ADD utilisateur_id INT NOT NULL, CHANGE objet objet VARCHAR(100) NOT NULL, CHANGE description description TEXT NOT NULL, CHANGE date_creation date_creation DATE NOT NULL, CHANGE statut statut VARCHAR(30) NOT NULL, CHANGE priorite priorite VARCHAR(20) NOT NULL, CHANGE type type VARCHAR(30) NOT NULL');
        $this->addSql('ALTER TABLE response CHANGE content content TEXT NOT NULL, CHANGE created_at created_at DATETIME DEFAULT CURRENT_TIMESTAMP, CHANGE thread_id thread_id INT NOT NULL, CHANGE user_id user_id INT NOT NULL, CHANGE like_count like_count INT DEFAULT 0');
        $this->addSql('ALTER TABLE thread CHANGE title title VARCHAR(150) NOT NULL, CHANGE content content TEXT NOT NULL, CHANGE created_at created_at DATETIME DEFAULT CURRENT_TIMESTAMP, CHANGE status status VARCHAR(255) DEFAULT \'OPEN\', CHANGE user_id user_id INT NOT NULL, CHANGE category category VARCHAR(50) DEFAULT NULL, CHANGE tags tags VARCHAR(100) DEFAULT NULL, CHANGE attachments attachments TEXT DEFAULT NULL, CHANGE like_count like_count INT DEFAULT 0');
        $this->addSql('DROP INDEX UNIQ_8D93D649450FF010 ON user');
        $this->addSql('ALTER TABLE user ADD two_factor_secret VARCHAR(255) DEFAULT NULL, ADD two_factor_enabled TINYINT(1) DEFAULT 0, ADD verification_code VARCHAR(10) DEFAULT NULL, ADD code_expiration DATETIME DEFAULT NULL, CHANGE nom nom VARCHAR(100) NOT NULL, CHANGE email email VARCHAR(100) NOT NULL, CHANGE telephone telephone VARCHAR(20) DEFAULT NULL, CHANGE role role VARCHAR(255) NOT NULL, CHANGE etatCompte etatCompte VARCHAR(255) DEFAULT \'ACTIF\', CHANGE date_creation date_creation DATETIME DEFAULT CURRENT_TIMESTAMP NOT NULL');
        $this->addSql('DROP INDEX uniq_8d93d649e7927c74 ON user');
        $this->addSql('CREATE UNIQUE INDEX email ON user (email)');
    }
}
