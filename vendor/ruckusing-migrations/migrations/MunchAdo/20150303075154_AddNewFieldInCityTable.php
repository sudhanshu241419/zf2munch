<?php

class AddNewFieldInCityTable extends Ruckusing_Migration_Base
{
    public function up()
    {
        //$this->execute("ALTER TABLE `cities` ADD `seo` ENUM( '1', '0' ) NOT NULL DEFAULT '0' COMMENT 'This field is used for seo module to active city for seo' AFTER `is_browse_only`");
    }//up()

    public function down()
    {
    }//down()
}
