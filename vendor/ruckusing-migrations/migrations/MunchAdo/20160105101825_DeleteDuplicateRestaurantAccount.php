<?php

class DeleteDuplicateRestaurantAccount extends Ruckusing_Migration_Base
{
    public function up()
    {
        $this->execute("DELETE ra1 FROM restaurant_accounts ra1, restaurant_accounts ra2 WHERE ra1.id > ra2.id AND ra1.restaurant_id = ra2.restaurant_id");
    }//up()

    public function down()
    {
    }//down()
}
