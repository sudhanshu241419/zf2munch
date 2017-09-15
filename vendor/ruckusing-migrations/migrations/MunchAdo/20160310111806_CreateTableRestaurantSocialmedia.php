<?php

class CreateTableRestaurantSocialmedia extends Ruckusing_Migration_Base {

    public function up() {
        $this->execute("CREATE TABLE `restaurant_socialmedia` (
            `id` int(11) NOT NULL AUTO_INCREMENT,
            `restaurant_id` int(11) NOT NULL,
            `fb_like_url` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
            `fb_checkin_url` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
            `foursquare_rating_url` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
            `cuisine_one_liner` text COLLATE utf8_unicode_ci,
            `famous_dish_one_liner` text COLLATE utf8_unicode_ci,
            `ambience_one_liner` text COLLATE utf8_unicode_ci,
            `chef_feature_one_liner` text COLLATE utf8_unicode_ci,
            PRIMARY KEY (`id`)
           ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci");
    }

    public function down() {
        
    }
}
