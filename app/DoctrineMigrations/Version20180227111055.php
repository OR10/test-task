<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

// Remove onDelete=CASCADE for employee->parentId

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20180227111055 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE employee DROP FOREIGN KEY FK_5D9F75A177EAA232');
        $this->addSql('DROP INDEX IDX_5D9F75A177EAA232 ON employee');
        $this->addSql('ALTER TABLE employee DROP employee_parent_id');
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE employee ADD employee_parent_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE employee ADD CONSTRAINT FK_5D9F75A177EAA232 FOREIGN KEY (employee_parent_id) REFERENCES employee (employee_id) ON DELETE CASCADE');
        $this->addSql('CREATE INDEX IDX_5D9F75A177EAA232 ON employee (employee_parent_id)');
    }
}
