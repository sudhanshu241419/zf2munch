<?php

class RestaurantUploadMenu extends Ruckusing_Migration_Base
{
    public function up()
    {
     $this->execute("CREATE TABLE IF NOT EXISTS `restaurant_upload_menu` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `restaurant_id` int(11) NOT NULL,
  `dirPath` text COLLATE utf8_unicode_ci NOT NULL,
  `readStatus` tinyint(1) NOT NULL DEFAULT '0',
  `addDate` datetime NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=32 ;");   
    }//up()

    public function down()
    {
    }//down()
}
