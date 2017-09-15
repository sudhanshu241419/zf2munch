<?php

class AlterTableRestaurants2 extends Ruckusing_Migration_Base {

    public function up() {
        $this->execute("ALTER TABLE  `restaurants` CHANGE  `delivery_geo`  `delivery_geo` TEXT CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL");
    }

//up()

    public function down() {
        
    }

//down()
}
