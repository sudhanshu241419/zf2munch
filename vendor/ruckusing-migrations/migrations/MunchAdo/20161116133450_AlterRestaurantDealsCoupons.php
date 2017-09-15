<?php

class AlterRestaurantDealsCoupons extends Ruckusing_Migration_Base
{
    public function up()
    {
        $this->execute("ALTER TABLE  `restaurant_deals_coupons` CHANGE  `status`  `status` TINYINT( 4 ) NULL DEFAULT  '1' COMMENT '1=>live,0=>close,2=>paused,3=>processing,4=>is_deleted'");
    }//up()

    public function down()
    {
    }//down()
}
