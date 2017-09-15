<?php

class UpdateRestaurantDealsCoupons extends Ruckusing_Migration_Base
{
    public function up()
    {
        $this->execute('ALTER TABLE  `restaurant_deals_coupons` ADD  `user_deals` TINYINT( 1 ) NOT NULL DEFAULT  "0" AFTER  `days`;');
    }//up()

    public function down()
    {
    }//down()
}
