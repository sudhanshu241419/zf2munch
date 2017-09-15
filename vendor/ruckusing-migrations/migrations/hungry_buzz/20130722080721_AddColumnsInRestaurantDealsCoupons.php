<?php

class AddColumnsInRestaurantDealsCoupons extends Ruckusing_Migration_Base
{
    public function up()
    {
    	$this->add_column("restaurant_deals", "sold", "integer");
    	$this->add_column("restaurant_deals", "redeemed", "integer");
		$this->add_column("restaurant_coupons", "sold", "integer");
		$this->add_column("restaurant_coupons","redeemed", "integer");
       $this->execute("ALTER TABLE `restaurant_deals` CHANGE `sold` `sold` INT( 11 ) NULL DEFAULT '0',
CHANGE `redeemed` `redeemed` INT( 11 ) NULL DEFAULT '0'");
    }//up()

    public function down()
    {
    	$this->remove_column("restaurant_coupons","redeemed");
		$this->remove_column("restaurant_coupons", "sold");
		$this->remove_column("restaurant_deals","redeemed");
		$this->remove_column("restaurant_deals", "sold");
		
    }//down()
}
