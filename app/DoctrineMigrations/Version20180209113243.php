<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20180209113243 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE IF NOT EXISTS position (position_id INT AUTO_INCREMENT NOT NULL, position_name VARCHAR(150) NOT NULL, PRIMARY KEY(position_id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_general_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE IF NOT EXISTS user (user_id INT AUTO_INCREMENT NOT NULL, user_position_id INT DEFAULT NULL, user_full_name VARCHAR(100) NOT NULL, user_recruiting_date DATE NOT NULL, user_salary INT NOT NULL, INDEX IDX_8D93D649749FE7D3 (user_position_id), PRIMARY KEY(user_id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_general_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE user ADD CONSTRAINT FK_8D93D649749FE7D31 FOREIGN KEY (user_position_id) REFERENCES position (position_id)');
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE user DROP FOREIGN KEY FK_8D93D649749FE7D31');
        $this->addSql('DROP TABLE position');
        $this->addSql('DROP TABLE user');
    }
}
