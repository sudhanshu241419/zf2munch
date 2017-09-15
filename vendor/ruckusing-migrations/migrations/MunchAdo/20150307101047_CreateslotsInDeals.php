<?php

class CreateslotsInDeals extends Ruckusing_Migration_Base
{
    public function up()
    {
    	$this->execute("ALTER TABLE  `restaurant_deals_coupons` ADD  `slots` VARCHAR( 512 ) NULL ,
ADD  `days` VARCHAR( 32 ) NULL ;");
    	$this->execute("ALTER TABLE  `restaurant_deals_coupons` CHANGE  `discount_type`  `discount_type` VARCHAR( 20 ) CHARACTER SET latin1 COLLATE latin1_swedish_ci NULL DEFAULT NULL ;");
    	$this->execute("ALTER TABLE  `restaurant_deals_coupons` CHANGE  `deal_for`  `deal_for` VARCHAR( 20 ) NULL DEFAULT NULL COMMENT '1=>delevary_takeout,2=>''dine-in,3=>''all''';");
    }//up()

    public function down()
    {
    }//down()
}
