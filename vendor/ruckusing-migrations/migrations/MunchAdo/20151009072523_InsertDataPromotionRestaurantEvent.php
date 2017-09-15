<?php

class InsertDataPromotionRestaurantEvent extends Ruckusing_Migration_Base
{
    public function up()
    {
        $this->execute("INSERT INTO `promotion_restaurant_event` (`id`, `promotionId`, `restaurantEventId`, `restaurant_id`, `created_on`, `updated_on`) VALUES
(1, 1, 1, 58285, '2015-10-09 00:00:00', '2015-10-09 00:00:00'),
(2, 1, 2, 58066, '2015-10-09 00:00:00', '2015-10-09 00:00:00')");
    }//up()

    public function down()
    {
    }//down()
}
