<?php

class CreatePromotionRestaurantEvent extends Ruckusing_Migration_Base
{
    public function up()
    {
        $this->execute("CREATE TABLE IF NOT EXISTS `promotion_restaurant_event` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `promotionId` bigint(20) NOT NULL,
  `restaurantEventId` bigint(20) DEFAULT NULL,
  `restaurant_id` bigint(20) NOT NULL,
  `created_on` datetime NOT NULL,
  `updated_on` datetime NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1");
    }//up()

    public function down()
    {
    }//down()
}
