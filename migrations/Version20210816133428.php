<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20210816133428 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SEQUENCE auth_token_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE SEQUENCE "email_activation_token_id_seq" INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE SEQUENCE "email_password_reset_token_id_seq" INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE SEQUENCE login_failed_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE SEQUENCE permission_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE SEQUENCE "roles_id_seq" INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE SEQUENCE user_data_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE SEQUENCE "users_id_seq" INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE TABLE auth_token (id INT NOT NULL, holder_id INT NOT NULL, value VARCHAR(255) NOT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, expired_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_9315F04EDEEE62D0 ON auth_token (holder_id)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_9315F04E1D775834 ON auth_token (value)');
        $this->addSql('CREATE TABLE "email_activation_token" (id INT NOT NULL, holder_id INT NOT NULL, token VARCHAR(128) NOT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, expired_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_BF8D6ADC5F37A13B ON "email_activation_token" (token)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_BF8D6ADCDEEE62D0 ON "email_activation_token" (holder_id)');
        $this->addSql('CREATE TABLE "email_password_reset_token" (id INT NOT NULL, holder_id INT NOT NULL, token VARCHAR(128) NOT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, expired_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_A513F3305F37A13B ON "email_password_reset_token" (token)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_A513F330DEEE62D0 ON "email_password_reset_token" (holder_id)');
        $this->addSql('CREATE TABLE login_failed (id INT NOT NULL, target_id INT NOT NULL, ip VARCHAR(15) DEFAULT NULL, client VARCHAR(255) DEFAULT NULL, failed_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_69EF84ED158E0B66 ON login_failed (target_id)');
        $this->addSql('CREATE TABLE permission (id INT NOT NULL, name VARCHAR(64) NOT NULL, description VARCHAR(255) NOT NULL, module_name VARCHAR(255) NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_E04992AA5E237E06 ON permission (name)');
        $this->addSql('CREATE TABLE "roles" (id INT NOT NULL, name VARCHAR(32) NOT NULL, description VARCHAR(255) NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_B63E2EC75E237E06 ON "roles" (name)');
        $this->addSql('CREATE TABLE role_permission (role_id INT NOT NULL, permission_id INT NOT NULL, PRIMARY KEY(role_id, permission_id))');
        $this->addSql('CREATE INDEX IDX_6F7DF886D60322AC ON role_permission (role_id)');
        $this->addSql('CREATE INDEX IDX_6F7DF886FED90CCA ON role_permission (permission_id)');
        $this->addSql('CREATE TABLE user_data (id INT NOT NULL, holder_id INT NOT NULL, nickname VARCHAR(32) DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_D772BFAAA188FE64 ON user_data (nickname)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_D772BFAADEEE62D0 ON user_data (holder_id)');
        $this->addSql('CREATE TABLE "users" (id INT NOT NULL, user_data_id INT DEFAULT NULL, email VARCHAR(180) NOT NULL, password VARCHAR(255) NOT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, updated_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, active INT DEFAULT 0 NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_1483A5E9E7927C74 ON "users" (email)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_1483A5E96FF8BF36 ON "users" (user_data_id)');
        $this->addSql('CREATE INDEX IDX_1483A5E935C246D5 ON "users" (password)');
        $this->addSql('CREATE TABLE user_role (user_id INT NOT NULL, role_id INT NOT NULL, PRIMARY KEY(user_id, role_id))');
        $this->addSql('CREATE INDEX IDX_2DE8C6A3A76ED395 ON user_role (user_id)');
        $this->addSql('CREATE INDEX IDX_2DE8C6A3D60322AC ON user_role (role_id)');
        $this->addSql('CREATE TABLE messenger_messages (id BIGSERIAL NOT NULL, body TEXT NOT NULL, headers TEXT NOT NULL, queue_name VARCHAR(190) NOT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, available_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, delivered_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_75EA56E0FB7336F0 ON messenger_messages (queue_name)');
        $this->addSql('CREATE INDEX IDX_75EA56E0E3BD61CE ON messenger_messages (available_at)');
        $this->addSql('CREATE INDEX IDX_75EA56E016BA31DB ON messenger_messages (delivered_at)');
        $this->addSql('CREATE OR REPLACE FUNCTION notify_messenger_messages() RETURNS TRIGGER AS $$
            BEGIN
                PERFORM pg_notify(\'messenger_messages\', NEW.queue_name::text);
                RETURN NEW;
            END;
        $$ LANGUAGE plpgsql;');
        $this->addSql('DROP TRIGGER IF EXISTS notify_trigger ON messenger_messages;');
        $this->addSql('CREATE TRIGGER notify_trigger AFTER INSERT OR UPDATE ON messenger_messages FOR EACH ROW EXECUTE PROCEDURE notify_messenger_messages();');
        $this->addSql('ALTER TABLE auth_token ADD CONSTRAINT FK_9315F04EDEEE62D0 FOREIGN KEY (holder_id) REFERENCES "users" (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE "email_activation_token" ADD CONSTRAINT FK_BF8D6ADCDEEE62D0 FOREIGN KEY (holder_id) REFERENCES "users" (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE "email_password_reset_token" ADD CONSTRAINT FK_A513F330DEEE62D0 FOREIGN KEY (holder_id) REFERENCES "users" (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE login_failed ADD CONSTRAINT FK_69EF84ED158E0B66 FOREIGN KEY (target_id) REFERENCES "users" (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE role_permission ADD CONSTRAINT FK_6F7DF886D60322AC FOREIGN KEY (role_id) REFERENCES "roles" (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE role_permission ADD CONSTRAINT FK_6F7DF886FED90CCA FOREIGN KEY (permission_id) REFERENCES permission (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE user_data ADD CONSTRAINT FK_D772BFAADEEE62D0 FOREIGN KEY (holder_id) REFERENCES "users" (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE "users" ADD CONSTRAINT FK_1483A5E96FF8BF36 FOREIGN KEY (user_data_id) REFERENCES user_data (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE user_role ADD CONSTRAINT FK_2DE8C6A3A76ED395 FOREIGN KEY (user_id) REFERENCES "users" (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE user_role ADD CONSTRAINT FK_2DE8C6A3D60322AC FOREIGN KEY (role_id) REFERENCES "roles" (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE role_permission DROP CONSTRAINT FK_6F7DF886FED90CCA');
        $this->addSql('ALTER TABLE role_permission DROP CONSTRAINT FK_6F7DF886D60322AC');
        $this->addSql('ALTER TABLE user_role DROP CONSTRAINT FK_2DE8C6A3D60322AC');
        $this->addSql('ALTER TABLE "users" DROP CONSTRAINT FK_1483A5E96FF8BF36');
        $this->addSql('ALTER TABLE auth_token DROP CONSTRAINT FK_9315F04EDEEE62D0');
        $this->addSql('ALTER TABLE "email_activation_token" DROP CONSTRAINT FK_BF8D6ADCDEEE62D0');
        $this->addSql('ALTER TABLE "email_password_reset_token" DROP CONSTRAINT FK_A513F330DEEE62D0');
        $this->addSql('ALTER TABLE login_failed DROP CONSTRAINT FK_69EF84ED158E0B66');
        $this->addSql('ALTER TABLE user_data DROP CONSTRAINT FK_D772BFAADEEE62D0');
        $this->addSql('ALTER TABLE user_role DROP CONSTRAINT FK_2DE8C6A3A76ED395');
        $this->addSql('DROP SEQUENCE auth_token_id_seq CASCADE');
        $this->addSql('DROP SEQUENCE "email_activation_token_id_seq" CASCADE');
        $this->addSql('DROP SEQUENCE "email_password_reset_token_id_seq" CASCADE');
        $this->addSql('DROP SEQUENCE login_failed_id_seq CASCADE');
        $this->addSql('DROP SEQUENCE permission_id_seq CASCADE');
        $this->addSql('DROP SEQUENCE "roles_id_seq" CASCADE');
        $this->addSql('DROP SEQUENCE user_data_id_seq CASCADE');
        $this->addSql('DROP SEQUENCE "users_id_seq" CASCADE');
        $this->addSql('DROP TABLE auth_token');
        $this->addSql('DROP TABLE "email_activation_token"');
        $this->addSql('DROP TABLE "email_password_reset_token"');
        $this->addSql('DROP TABLE login_failed');
        $this->addSql('DROP TABLE permission');
        $this->addSql('DROP TABLE "roles"');
        $this->addSql('DROP TABLE role_permission');
        $this->addSql('DROP TABLE user_data');
        $this->addSql('DROP TABLE "users"');
        $this->addSql('DROP TABLE user_role');
        $this->addSql('DROP TABLE messenger_messages');
    }
}
