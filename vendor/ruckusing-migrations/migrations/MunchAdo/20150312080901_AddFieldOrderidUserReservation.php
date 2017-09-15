<?php

class AddFieldOrderidUserReservation extends Ruckusing_Migration_Base
{
    public function up()
    {
        $this->execute("ALTER TABLE `user_reservations` ADD `order_id` BIGINT( 20 ) NULL COMMENT 'pre-order-reservation order id' AFTER `host_name`");
 
    }//up()

    public function down()
    {
    }//down()
}
