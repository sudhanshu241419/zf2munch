<?php

class RestaurantDineinCloseDays extends Ruckusing_Migration_Base
{
    public function up()
    {
        $this->execute("CREATE TABLE IF NOT EXISTS `restaurant_dinein_close_days` (`id` int(11) NOT NULL AUTO_INCREMENT,`restaurant_id` int(11) NOT NULL,`close_date` date NOT NULL,`close_from` time NOT NULL,`close_to` time NOT NULL,`whole_day` tinyint(1) NOT NULL DEFAULT '0' COMMENT '0=>not close for whole day,1=>close for whole day',PRIMARY KEY (`id`)) ENGINE=MYISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1 ;");
    }//up()

    public function down()
    {
    }//down()
}
