<?php

class DropRestaurantRating extends Ruckusing_Migration_Base
{
    public function up()
    {
        $this->execute("ALTER TABLE  `restaurants` DROP  `restaurant_rating`;");
    }//up()

    public function down()
    {
    }//down()
}
