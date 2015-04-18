<?php

namespace Gibson\Migration;

use Zend\Db\Metadata\MetadataInterface;
use ZfSimpleMigrations\Library\AbstractMigration;

class Version20150418200838 extends AbstractMigration
{
    public static $description = "Add Address Columns";

    public function up(MetadataInterface $schema)
    {
        $this->addSql('ALTER TABLE gibson_user_data
ADD COLUMN address_line1  varchar(255) NULL AFTER medical_information_expiry,
ADD COLUMN address_line2  varchar(255) NULL AFTER address_line1,
ADD COLUMN address_town  varchar(255) NULL AFTER address_line2,
ADD COLUMN address_city  varchar(255) NULL AFTER address_town,
ADD COLUMN address_county  varchar(255) NULL AFTER address_city,
ADD COLUMN address_postcode  varchar(255) NULL AFTER address_county;');
    }

    public function down(MetadataInterface $schema)
    {
        //throw new \RuntimeException('No way to go down!');
        $this->addSql('ALTER TABLE gibson_user_data
DROP COLUMN address_line1,
DROP COLUMN address_line2,
DROP COLUMN address_town,
DROP COLUMN address_city,
DROP COLUMN address_county,
DROP COLUMN address_postcode;');
    }
}
