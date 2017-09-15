<?php

class AddLocalityInCities extends Ruckusing_Migration_Base
{
    public function up()
    {
    	$this->execute("ALTER TABLE  `cities` ADD  `locality` VARCHAR( 100 ) NULL AFTER  `city_name`");
    	$this->execute("UPDATE `cities` SET `locality`=`city_name` WHERE `latitude` AND `longitude` AND `status` = 1");
    	$this->execute("UPDATE  `hungry_buzz`.`cities` SET  `locality` =  'SF' WHERE  `cities`.`city_name` = 'San Francisco'");
    	$this->execute("UPDATE  `hungry_buzz`.`cities` SET  `locality` =  'KCMO' WHERE  `cities`.`city_name` = 'Kansas City'");
    }//up()

    public function down()
    {
    }//down()
}
