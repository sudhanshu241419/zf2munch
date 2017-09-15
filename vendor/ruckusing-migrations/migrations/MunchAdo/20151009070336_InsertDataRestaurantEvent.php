<?php

class InsertDataRestaurantEvent extends Ruckusing_Migration_Base
{
    public function up()
    {
        $this->execute("INSERT INTO `restaurant_events` (`restaurantEventId`, `restaurantEventName`, `restaurantEventDesc`, `restaurantEventStartDate`, `restaurantEventEndDate`, `restaurantEventStatus`, `restaurant_id`, `menu_id`, `created_on`, `updated_on`) VALUES
(1, 'Opening eve', 'We are celebrating opening eve', '2015-10-16 20:38:32', '2015-10-16 23:00:00', 1, 58285, NULL, '2015-10-09 00:00:00', '2015-10-09 00:00:00'),
(2, 'Opening eve', 'We are celebrating opening eve', '2015-10-19 19:30:00', '2015-10-09 23:00:00', 1, 58066, NULL, '2015-10-09 00:00:00', '2015-10-09 00:00:00');");
    }//up()

    public function down()
    {
    }//down()
}
