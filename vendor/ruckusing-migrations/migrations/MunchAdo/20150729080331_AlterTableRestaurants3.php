<?php

class AlterTableRestaurants3 extends Ruckusing_Migration_Base {

    public function up() {
        $this->execute("ALTER TABLE  `restaurants` ADD  `created_on` INT UNSIGNED NULL DEFAULT NULL AFTER  `updated_on`");
    }

    public function down() {
        
    }
}
