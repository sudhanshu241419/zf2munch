<?php

class ClientTestimonial extends Ruckusing_Migration_Base
{
    public function up()
    {
        $this->execute("CREATE TABLE IF NOT EXISTS `client_testimonial` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `restaurant_id` int(11) NOT NULL COMMENT 'Restaurant_id is the forgian key of restaurants table',
  `order_list` int(11) NOT NULL,
  `client_image` varchar(256) COLLATE utf8_unicode_ci NOT NULL,
  `client_name` varchar(256) COLLATE utf8_unicode_ci NOT NULL,
  `client_designation` varchar(256) COLLATE utf8_unicode_ci NOT NULL,
  `client_desc` text COLLATE utf8_unicode_ci NOT NULL,
  `status` tinyint(4) NOT NULL DEFAULT '0',
  `add_date` datetime NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1 ;"); 
    }//up()

    public function down()
    {
    }//down()
}
