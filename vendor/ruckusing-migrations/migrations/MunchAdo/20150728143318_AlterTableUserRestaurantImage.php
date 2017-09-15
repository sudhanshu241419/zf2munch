<?php

class AlterTableUserRestaurantImage extends Ruckusing_Migration_Base {

    public function up() {
        $this->execute("ALTER TABLE  `user_restaurant_image` ADD  `reason` VARCHAR( 255 ) NULL");
    }

    public function down() {
        
    }
}
