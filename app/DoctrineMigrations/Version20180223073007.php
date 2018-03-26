<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20180223073007 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE IF NOT EXISTS employee (employee_id INT NOT NULL, employee_position_id INT DEFAULT NULL, employee_parent_id INT DEFAULT NULL, employee_full_name VARCHAR(100) NOT NULL, employee_recruiting_date DATE NOT NULL, employee_salary INT NOT NULL, INDEX IDX_5D9F75A15CA2EF3B (employee_position_id), INDEX IDX_5D9F75A177EAA232 (employee_parent_id), PRIMARY KEY(employee_id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_general_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE employee ADD CONSTRAINT FK_5D9F75A15CA2EF3B FOREIGN KEY (employee_position_id) REFERENCES position (position_id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE employee ADD CONSTRAINT FK_5D9F75A177EAA232 FOREIGN KEY (employee_parent_id) REFERENCES employee (employee_id) ON DELETE CASCADE');
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE employee DROP FOREIGN KEY FK_5D9F75A177EAA232');
        $this->addSql('DROP TABLE employee');
    }
}
