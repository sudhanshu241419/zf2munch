<?php

class AlterUserReservationForUserIp extends Ruckusing_Migration_Base
{
    public function up()
    {
        $this->execute("ALTER TABLE `user_reservations` ADD `user_ip` VARCHAR( 255 ) NULL DEFAULT NULL AFTER `host_name`");
    }//up()

    public function down()
    {
    }//down()
}
