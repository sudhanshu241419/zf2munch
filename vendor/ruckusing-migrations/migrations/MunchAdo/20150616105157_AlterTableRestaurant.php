<?php

class AlterTableRestaurant extends Ruckusing_Migration_Base {

    public function up() {
        $this->execute("ALTER TABLE  `restaurants` CHANGE  `delivery_area`  `delivery_area` FLOAT( 4 ) UNSIGNED NULL DEFAULT NULL COMMENT  'in miles';");
    }

    public function down() {
        
    }
}
