<?php

class UpdateTableRestaurantTags extends Ruckusing_Migration_Base {

    public function up() {
        $this->execute("DROP TABLE restaurant_tags;");
        $this->execute("CREATE TABLE IF NOT EXISTS `restaurant_tags` (
                        `id` bigint(20) NOT NULL AUTO_INCREMENT,
                        `restaurant_id` int(11) NOT NULL,
                        `tag_id` smallint(6) NOT NULL,
                        `status` tinyint(4) NOT NULL DEFAULT '1',
                        PRIMARY KEY (`id`),
                        KEY `restaurant_id` (`restaurant_id`),
                        KEY `tag_id` (`tag_id`)
                      ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1 ;");
    }

    public function down() {
        
    }

}
