<?php

class CreateTableRestaurantAds extends Ruckusing_Migration_Base {

    public function up() {
        $this->execute("CREATE TABLE IF NOT EXISTS `restaurant_ads` (
                    `id` int(11) NOT NULL AUTO_INCREMENT,
                    `restaurant_id` int(11) NOT NULL,
                    `keywords` text COLLATE utf8_unicode_ci NOT NULL,
                    `start_date` datetime NOT NULL,
                    `end_date` datetime NOT NULL,
                    `status` tinyint(4) NOT NULL DEFAULT '0',
                    PRIMARY KEY (`id`),
                    KEY `restaurant_id` (`restaurant_id`)
                  ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1 ;");
    }

    public function down() {
        
    }
}
