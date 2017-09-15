<?php

class AlterTableRestaurants extends Ruckusing_Migration_Base {

    public function up() {
        $this->execute("ALTER TABLE  `restaurants` ADD  `delivery_geo` VARCHAR( 255 ) NULL ;");
    }

    public function down() {
        
    }

}
