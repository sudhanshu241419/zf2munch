<?php

class AddfieldRestaurantDealsCouponRead extends Ruckusing_Migration_Base
{
    public function up()
    {
        $this->execute("ALTER TABLE `restaurant_deals_coupons`  ADD `read` TINYINT NOT NULL DEFAULT '0' AFTER `user_deals`");
    }//up()

    public function down()
    {
    }//down()
}
