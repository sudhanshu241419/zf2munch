<?php

class AlterTableRestaurantDealsCoupons2 extends Ruckusing_Migration_Base {

    public function up() {
        $this->execute("ALTER TABLE  `restaurant_deals_coupons` CHANGE  `price`  `price` DECIMAL( 5, 2 ) NOT NULL DEFAULT  '0'");
        $this->execute("UPDATE restaurant_deals_coupons SET price = '0' WHERE price IS NULL");
    }

    public function down() {
        
    }
}
