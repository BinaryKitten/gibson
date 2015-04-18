<?php

namespace Gibson\Migration;

use Zend\Db\Metadata\MetadataInterface;
use ZfSimpleMigrations\Library\AbstractMigration;

class Version20150418152434 extends AbstractMigration
{
    public static $description = "RFID Table";

    public function up(MetadataInterface $schema)
    {
        $this->addSql('CREATE TABLE gibson_user_rfid (
samAccountName  varchar(255) NOT NULL ,
rfidCode  varchar(255) NOT NULL ,
description  varchar(255) NULL ,
PRIMARY KEY (samAccountName, rfidCode),
UNIQUE INDEX no_rfid_same_across_users (rfidCode) USING BTREE 
);');
    }

    public function down(MetadataInterface $schema)
    {
        //throw new \RuntimeException('No way to go down!');
        $this->addSql('DROP TABLE gibson_user_rfid');
    }
}
