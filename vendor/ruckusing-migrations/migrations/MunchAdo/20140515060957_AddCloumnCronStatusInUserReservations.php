<?php

class AddCloumnCronStatusInUserReservations extends Ruckusing_Migration_Base
{
    public function up()
    {
    	//$this->execute("ALTER TABLE  `user_reservations` ADD  `cron_status` TINYINT( 1 ) NOT NULL DEFAULT  '0' COMMENT  '0=>live, 1=>archived' AFTER  `review_id`");
    }//up()

    public function down()
    {
    }//down()
}
