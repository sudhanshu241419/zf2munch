<?php

class MinimumAmountInDealsAndCoupons extends Ruckusing_Migration_Base
{
    public function up()
    {
    	$this->execute("ALTER TABLE  `restaurant_deals_coupons` ADD  `minimum_order_amount` INT( 5 ) NULL");
    }//up()

    public function down()
    {
    }//down()
}
