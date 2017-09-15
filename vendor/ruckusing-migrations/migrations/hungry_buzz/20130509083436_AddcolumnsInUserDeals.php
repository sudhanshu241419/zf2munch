<?php

class AddcolumnsInUserDeals extends Ruckusing_Migration_Base
{
    public function up()
    {
    	$this->add_column("user_deals", "coupon_code", "string", array('limit' => 50));
    }//up()

    public function down()
    {
    	$this->remove_column("user_deals", "coupon_code", "string", array('limit' => 50));
    }//down()
}
