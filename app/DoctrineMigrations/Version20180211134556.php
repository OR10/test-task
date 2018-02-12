<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20180211134556 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE employee (employee_id INT AUTO_INCREMENT NOT NULL, employee_position_id INT DEFAULT NULL, employee_full_name VARCHAR(100) NOT NULL, employee_recruiting_date DATE NOT NULL, employee_salary INT NOT NULL, INDEX IDX_5D9F75A15CA2EF3B (employee_position_id), PRIMARY KEY(employee_id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_general_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE employee ADD CONSTRAINT FK_5D9F75A15CA2EF3B FOREIGN KEY (employee_position_id) REFERENCES position (position_id)');
        $this->addSql('DROP TABLE user');
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE user (user_id INT AUTO_INCREMENT NOT NULL, user_position_id INT DEFAULT NULL, user_full_name VARCHAR(100) NOT NULL COLLATE utf8_general_ci, user_recruiting_date DATE NOT NULL, user_salary INT NOT NULL, INDEX IDX_8D93D649749FE7D3 (user_position_id), PRIMARY KEY(user_id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE user ADD CONSTRAINT FK_8D93D649749FE7D3 FOREIGN KEY (user_position_id) REFERENCES position (position_id)');
        $this->addSql('DROP TABLE employee');
    }
}
