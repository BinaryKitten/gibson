<?php

namespace Gibson\Migration;

use Zend\Db\Metadata\MetadataInterface;
use ZfSimpleMigrations\Library\AbstractMigration;

class Version20150418183629 extends AbstractMigration
{
    public static $description = "Add Enabled Field to RFID";

    public function up(MetadataInterface $schema)
    {
        $this->addSql('ALTER TABLE gibson_user_rfid ADD COLUMN enabled  tinyint(1) NULL AFTER description;');
        $this->addSql('UPDATE gibson_user_rfid SET enabled=1;');
    }

    public function down(MetadataInterface $schema)
    {
        //throw new \RuntimeException('No way to go down!');
        $this->addSql('ALTER TABLE gibson_user_rfid DROP COLUMN enabled;');
    }
}
