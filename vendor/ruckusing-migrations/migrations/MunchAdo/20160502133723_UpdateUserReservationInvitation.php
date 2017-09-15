<?php

class UpdateUserReservationInvitation extends Ruckusing_Migration_Base
{
    public function up()
    {
     $this->execute("ALTER TABLE  `user_reservation_invitation` ADD  `cronUpdate` TINYINT( 1 ) NOT NULL DEFAULT  '0' AFTER  `created_on` ;");
     $this->execute("update `user_reservation_invitation` set `cronUpdate`=1;");
    }//up()

    public function down()
    {
    }//down()
}
