<?php

class AlterUserReservationIsModify extends Ruckusing_Migration_Base
{
    public function up()
    {
        $this->execute("ALTER TABLE `user_reservations` ADD `is_modify` TINYINT NOT NULL DEFAULT '0' AFTER `cronUpdateForCancelation`");
    }//up()

    public function down()
    {
    }//down()
}
