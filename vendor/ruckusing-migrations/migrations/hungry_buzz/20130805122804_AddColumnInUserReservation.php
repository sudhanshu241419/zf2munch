<?php

class AddColumnInUserReservation extends Ruckusing_Migration_Base
{
    public function up()
    {
    	$this->execute("ALTER TABLE `user_reservations` ADD `meal_slot` ENUM( 'breakfast', 'lunch', 'dinner' ) NULL AFTER `time_slot`");
    }//up()

    public function down()
    {
    }//down()
}
