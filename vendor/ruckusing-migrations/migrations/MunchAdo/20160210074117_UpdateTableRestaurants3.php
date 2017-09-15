<?php

class UpdateTableRestaurants3 extends Ruckusing_Migration_Base {

    public function up() {
        $this->execute("ALTER TABLE  `restaurants` CHANGE  `delivery_charge`  `delivery_charge` FLOAT( 5, 2 ) NULL DEFAULT  '0'");
    }

    public function down() {
        
    }
}
