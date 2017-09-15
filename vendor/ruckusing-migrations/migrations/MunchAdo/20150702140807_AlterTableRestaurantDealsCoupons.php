<?php

class AlterTableRestaurantDealsCoupons extends Ruckusing_Migration_Base {

    public function up() {
        $this->execute("ALTER TABLE  `restaurant_deals_coupons` CHANGE  `discount_type`  `discount_type` VARCHAR( 20 ) CHARACTER SET latin1 COLLATE latin1_swedish_ci NULL DEFAULT  'flat'");
    }

    public function down() {
        
    }
}
