<?php

class AddColumnInDineinCalander extends Ruckusing_Migration_Base
{
    public function up()
    {
   /* $this->execute("ALTER TABLE `restaurant_Dinein_calendars` CHANGE `no_of_seats` `breakfast_seats` INT( 11 ) NULL DEFAULT NULL");
    $this->execute("ALTER TABLE `restaurant_Dinein_calendars` ADD `lunch_seats` INT NULL AFTER `breakfast_seats` ,
                   ADD `dinner_seats` INT NULL AFTER `lunch_seats`"); 
    */}//up()

    public function down()
    {
    }//down()
}
