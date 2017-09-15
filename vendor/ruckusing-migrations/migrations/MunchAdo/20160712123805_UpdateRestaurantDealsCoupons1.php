<?php

class UpdateRestaurantDealsCoupons1 extends Ruckusing_Migration_Base
{
    public function up()
    {
        $this->execute("ALTER TABLE  `restaurant_deals_coupons` CHANGE  `deal_for`  `deal_for` VARCHAR( 32 ) CHARACTER SET latin1 COLLATE latin1_swedish_ci NULL DEFAULT NULL COMMENT  '1=>delevary_takeout,2=>''dine-in,3=>''all''';");
    }//up()

    public function down()
    {
    }//down()
}
