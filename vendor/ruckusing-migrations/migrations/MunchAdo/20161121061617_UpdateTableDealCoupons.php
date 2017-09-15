<?php

class UpdateTableDealCoupons extends Ruckusing_Migration_Base
{
    public function up()
    {
        $this->execute("ALTER TABLE  `restaurant_deals_coupons` CHANGE  `trend`  `trend` TINYINT( 1 ) NULL DEFAULT NULL COMMENT '0=>all,1=>review,2=>checkin,3=>image,4=>delivery,5=>takeout,6=>reservation';");
        $this->execute("ALTER TABLE  `restaurant_deals_coupons` CHANGE  `type`  `type` ENUM(  'deals',  'coupons',  'offer' ) CHARACTER SET latin1 COLLATE latin1_swedish_ci NULL DEFAULT  'deals';");
        
    }//up()

    public function down()
    {
    }//down()
}
