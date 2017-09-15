<?php

class CreateTableServers extends Ruckusing_Migration_Base {

    public function up() {
        $this->execute("CREATE TABLE IF NOT EXISTS `servers` (
            `id` int(11) NOT NULL AUTO_INCREMENT,
            `first_name` varchar(200) DEFAULT NULL,
            `last_name` varchar(200) DEFAULT NULL,
            `email` varchar(100) NOT NULL,
            `phone` varchar(20) DEFAULT NULL,
            `restaurant_id` int(11) NOT NULL,
            `password` varchar(50) NOT NULL,
            `code` varchar(10) NOT NULL,
            `date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            `status` tinyint(1) DEFAULT NULL,
            PRIMARY KEY (`id`)
            ) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=1;");
    }
    public function down() {
        
    }

//down()
}
