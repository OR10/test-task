<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

// Set employee->parentId && employee->positionId without CASCADE deleting

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20180227111348 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE employee ADD employee_position_id INT DEFAULT NULL, ADD employee_parent_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE employee ADD CONSTRAINT FK_5D9F75A15CA2EF3B FOREIGN KEY (employee_position_id) REFERENCES position (position_id)');
        $this->addSql('ALTER TABLE employee ADD CONSTRAINT FK_5D9F75A177EAA232 FOREIGN KEY (employee_parent_id) REFERENCES employee (employee_id)');
        $this->addSql('CREATE INDEX IDX_5D9F75A15CA2EF3B ON employee (employee_position_id)');
        $this->addSql('CREATE INDEX IDX_5D9F75A177EAA232 ON employee (employee_parent_id)');
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE employee DROP FOREIGN KEY FK_5D9F75A15CA2EF3B');
        $this->addSql('ALTER TABLE employee DROP FOREIGN KEY FK_5D9F75A177EAA232');
        $this->addSql('DROP INDEX IDX_5D9F75A15CA2EF3B ON employee');
        $this->addSql('DROP INDEX IDX_5D9F75A177EAA232 ON employee');
        $this->addSql('ALTER TABLE employee DROP employee_position_id, DROP employee_parent_id');
    }
}
