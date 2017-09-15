<?php

class AddFieldsInMenuTempTable extends Ruckusing_Migration_Base
{
    public function up()
    {
    	$this->execute("ALTER TABLE `menues_temp` ADD `cuisine` VARCHAR( 200 ) NULL DEFAULT NULL AFTER `parent_id`") ; 
    	$this->execute("ALTER TABLE `menues_temp` ADD `image_name` VARCHAR( 200 ) NULL DEFAULT NULL ") ;

    	$this->execute("ALTER TABLE `restaurant_deals` ADD `end_date` DATETIME NOT NULL AFTER `start_on`") ; 
    	$this->execute("ALTER TABLE `restaurant_coupons` ADD `end_date` DATETIME NOT NULL AFTER `start_on`") ; 
    }//up()

    public function down()
    {
    }//down()
}
