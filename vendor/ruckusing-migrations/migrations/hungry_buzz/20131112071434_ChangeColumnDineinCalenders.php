<?php

class ChangeColumnDineinCalenders extends Ruckusing_Migration_Base
{
    public function up()
    {
    	$this->execute("ALTER TABLE `restaurant_Dinein_calendars` ADD `date` DATE NULL AFTER `dinner_end_time` ,
                       ADD `no_of_seats` INT NULL AFTER `date`");
    	$this->execute("ALTER TABLE `user_reviews` ADD `is_read` TINYINT NOT NULL DEFAULT '0' COMMENT '0=>unread,1=>readed' AFTER `restaurant_response`");
        $this->execute("ALTER TABLE  `restaurant_deals_coupons` ADD  `coupon_code` VARCHAR( 50 ) NOT NULL AFTER  `redeemed`");
    }//up()

    public function down()
    {
    }//down()
}
