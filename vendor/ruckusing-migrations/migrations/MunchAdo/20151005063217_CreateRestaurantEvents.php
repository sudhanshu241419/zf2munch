<?php

class CreateRestaurantEvents extends Ruckusing_Migration_Base
{
    public function up()
    {
        $this->execute("CREATE TABLE IF NOT EXISTS `restaurant_events` (
  `restaurantEventId` bigint(20) NOT NULL AUTO_INCREMENT,
  `restaurantEventName` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `restaurantEventDesc` text COLLATE utf8_unicode_ci NOT NULL,
  `restaurantEventStartDate` datetime NOT NULL,
  `restaurantEventEndDate` datetime NOT NULL,
  `restaurantEventStatus` tinyint(4) NOT NULL,
  `restaurant_id` bigint(20) NOT NULL,
  `created_on` datetime NOT NULL,
  `updated_on` datetime NOT NULL,
  PRIMARY KEY (`restaurantEventId`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1 ;");
    }//up()

    public function down()
    {
    }//down()
}
