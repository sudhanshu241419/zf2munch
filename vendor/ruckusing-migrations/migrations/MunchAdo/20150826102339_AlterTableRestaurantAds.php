<?php

class AlterTableRestaurantAds extends Ruckusing_Migration_Base {

    public function up() {
        $this->execute("ALTER TABLE  `restaurant_ads` ADD  `ad_id` VARCHAR( 100 ) NOT NULL AFTER  `restaurant_id` ,
ADD  `ad_text` TEXT NOT NULL AFTER  `ad_id` ;");
    }

    public function down() {
        
    }
}
