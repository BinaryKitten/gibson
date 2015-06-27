<?php

namespace Gibson\Migration;

use ZfSimpleMigrations\Library\AbstractMigration;
use Zend\Db\Metadata\MetadataInterface;

class Version20150626231746 extends AbstractMigration
{
    public static $description = "Address Conversion & add nickname";

    public function up(MetadataInterface $schema)
    {
        $this->addSql('ALTER TABLE gibson_user_data
DROP COLUMN address_line2,
DROP COLUMN address_town,
DROP COLUMN address_city,
DROP COLUMN address_county,
DROP COLUMN address_postcode,
CHANGE COLUMN address_line1 address  text CHARACTER SET utf8 COLLATE utf8_general_ci NULL AFTER medical_information_expiry,
ADD COLUMN nickname  varchar(255) NOT NULL AFTER samAccountName;
');
    }

    public function down(MetadataInterface $schema)
    {
        //throw new \RuntimeException('No way to go down!');
        $this->addSql('ALTER TABLE gibson_user_data
DROP COLUMN nickname,
CHANGE COLUMN address address_line1  varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NULL AFTER medical_information_expiry,
ADD COLUMN address_line2  varchar(255) NULL AFTER address_line1,
ADD COLUMN address_town  varchar(255) NULL AFTER address_line2,
ADD COLUMN address_city  varchar(255) NULL AFTER address_town,
ADD COLUMN address_county  varchar(255) NULL AFTER address_city,
ADD COLUMN address_postcode  varchar(255) NULL AFTER address_county;');
    }
}
