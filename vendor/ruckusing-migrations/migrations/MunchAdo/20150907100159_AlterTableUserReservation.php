<?php

class AlterTableUserReservation extends Ruckusing_Migration_Base
{
    public function up()
    {
        $this->execute("ALTER TABLE `user_reservations` ADD `cronUpdate` ENUM( '0', '1' ) NOT NULL DEFAULT '0' AFTER `assignMuncher`;");
    }//up()

    public function down()
    {
    }//down()
}
