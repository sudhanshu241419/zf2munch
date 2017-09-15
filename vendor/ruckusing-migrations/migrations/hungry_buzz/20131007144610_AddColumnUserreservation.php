<?php

class AddColumnUserreservation extends Ruckusing_Migration_Base
{
    public function up()
    {
    	$this->execute("ALTER TABLE `user_reservations` ADD `read` TINYINT( 1 ) NOT NULL COMMENT '0=>unread,1=>readed' AFTER `reserved_seats` ");
        $this->execute("ALTER TABLE `user_orders` ADD `read` TINYINT( 1 ) NOT NULL COMMENT '0=>unread,1=>readed' AFTER `approved_by`");
    }//up()

    public function down()
    {
    }//down()
}
