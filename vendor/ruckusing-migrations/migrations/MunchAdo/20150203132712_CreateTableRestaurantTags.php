<?php

class CreateTableRestaurantTags extends Ruckusing_Migration_Base {

    public function up() {
        $sql = "CREATE TABLE IF NOT EXISTS `restaurant_tags` (`id` int(11) NOT NULL AUTO_INCREMENT,`restaurant_id` int(11) NOT NULL,`tag` varchar(255) NOT NULL,PRIMARY KEY (`id`)) ENGINE=InnoDB  DEFAULT CHARSET=latin1";
        $this->execute($sql);
    }

//up()

    public function down() {
        
    }

//down()
}
