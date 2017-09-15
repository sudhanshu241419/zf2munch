<?php

class Changecouponsfileds extends Ruckusing_Migration_Base
{
   public function up()
    {
    	$this->execute("ALTER TABLE restaurant_coupons CHANGE coupon_image_id image varchar(200)");
    	$this->add_column("restaurant_coupons", "trend", "boolean", array("default" => false));
    }//up()

    public function down()
    {
    	$this->execute("ALTER TABLE restaurant_coupons CHANGE image deal_image_id int(5)");
    	$this->add_column("restaurant_coupons", "trend");
    }//down()
}
