<?php

class AddColumnNameInUserReservationAndOrders extends Ruckusing_Migration_Base
{
    public function up()
    {
    	$this->execute("ALTER TABLE  `user_orders` ADD  `is_reviewed` TINYINT NOT NULL DEFAULT  '0' AFTER  `is_deleted`");
    	$this->execute("ALTER TABLE  `user_reservations` ADD  `is_reviewed` TINYINT NOT NULL DEFAULT  '0' AFTER  `receipt_no`");
    	
    	$this->execute("ALTER TABLE  `user_orders` ADD  `review_id` INT( 11 ) NULL AFTER  `is_reviewed`");
    	$this->execute("ALTER TABLE  `user_reservations` ADD  `review_id` INT( 11 ) NULL AFTER  `is_reviewed`");
    }//up()

    public function down()
    {
    }//down()
}
