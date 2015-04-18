<?php

namespace Gibson\Migration;

use Zend\Db\Metadata\MetadataInterface;
use ZfSimpleMigrations\Library\AbstractMigration;

class Version20150418151431 extends AbstractMigration
{
    public static $description = "Create User Data table";

    public function up(MetadataInterface $schema)
    {
        $this->addSql('CREATE TABLE gibson_user_data (
samAccountName  varchar(255) NOT NULL ,
phone  varchar(255) NULL ,
emergency_details  text NULL ,
emergency_details_expiry  date NULL ,
medical_information  text NULL ,
medical_information_expiry  date NULL ,
PRIMARY KEY (samAccountName));');
    }

    public function down(MetadataInterface $schema)
    {
        $this->addSql('DROP TABLE gibson_user_data;');
    }
}
