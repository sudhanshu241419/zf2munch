<?php

class AlterRestaurantDealsCouponsDealUsedType extends Ruckusing_Migration_Base
{
    public function up()
    {
        $this->execute("ALTER TABLE `restaurant_deals_coupons`  ADD `deal_used_type` TINYINT NOT NULL DEFAULT '0' COMMENT '0=multi time used, 1= one time used' AFTER `read`");
    }//up()

    public function down()
    {
    }//down()
}
