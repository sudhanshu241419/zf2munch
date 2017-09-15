<?php

class UpdateTableRestaurants2 extends Ruckusing_Migration_Base {

    public function up() {
        $this->execute("ALTER TABLE  `restaurants` ADD  `featured` TINYINT( 1 ) NOT NULL DEFAULT  '0'");
    }

    public function down() {
        
    }
}
