<?php

class AddColumnInDealsCoupons extends Ruckusing_Migration_Base
{
    public function up()
    {
    	$this->execute("ALTER TABLE `restaurant_deals_coupons` ADD `deal_for`  TINYINT NULL DEFAULT NULL COMMENT '1=>delevary_takeout,2=>''dine-in,3=>''all''' AFTER `type`");
    	$this->execute("ALTER TABLE `restaurant_deals_coupons` ADD `max_daily_quantity` INT NULL AFTER `discount`");
    	$this->execute("ALTER TABLE `restaurants_details` ADD `delivery_time` INT NOT NULL AFTER `delivery_area`");
    	$this->execute("ALTER TABLE `restaurant_accounts` ADD `title` VARCHAR( 50 ) CHARACTER SET latin1 COLLATE latin1_swedish_ci NULL DEFAULT NULL  AFTER `role`");
    }

    public function down()
    {
    }//down()
}


