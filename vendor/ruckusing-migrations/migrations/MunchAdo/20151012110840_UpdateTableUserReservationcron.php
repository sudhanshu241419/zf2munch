<?php

class UpdateTableUserReservationcron extends Ruckusing_Migration_Base
{
    public function up()
    {
        $this->execute("ALTER TABLE `user_reservations` ADD `cronUpdateForCancelation` TINYINT( 1 ) NOT NULL DEFAULT '0' AFTER `cronUpdate` ;");
    }//up()

    public function down()
    {
    }//down()
}
