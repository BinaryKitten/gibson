<?php

namespace Gibson\Migration;

use ZfSimpleMigrations\Library\AbstractMigration;
use Zend\Db\Metadata\MetadataInterface;

class Version20150502202859 extends AbstractMigration
{
    public static $description = "Migration description";

    public function up(MetadataInterface $schema)
    {
        $this->addSql("ALTER TABLE gibson_user_rfid
MODIFY COLUMN description  varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '' AFTER rfidCode,
MODIFY COLUMN enabled  tinyint(1) UNSIGNED NOT NULL DEFAULT 0 AFTER description;
");
    }

    public function down(MetadataInterface $schema)
    {
        //throw new \RuntimeException('No way to go down!');
        $this->addSql("ALTER TABLE gibson_user_rfid
MODIFY COLUMN description  varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL AFTER rfidCode,
MODIFY COLUMN enabled  tinyint(1) UNSIGNED NULL DEFAULT 0 AFTER description;");
    }
}
