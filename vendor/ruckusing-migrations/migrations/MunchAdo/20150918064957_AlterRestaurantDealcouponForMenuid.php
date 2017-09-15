<?php

class AlterRestaurantDealcouponForMenuid extends Ruckusing_Migration_Base
{
    public function up()
    {
        $this->execute("ALTER TABLE `restaurant_deals_coupons` ADD `menu_id` BIGINT NULL AFTER `restaurant_id`");
    }//up()

    public function down()
    {
    }//down()
}
