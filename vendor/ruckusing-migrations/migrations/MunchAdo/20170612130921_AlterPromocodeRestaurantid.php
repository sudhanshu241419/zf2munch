<?php

class AlterPromocodeRestaurantid extends Ruckusing_Migration_Base
{
    public function up()
    {
        $this->execute("ALTER TABLE `promocodes`  ADD `restaurant_id` INT NULL DEFAULT NULL AFTER `end_date`");
    }//up()

    public function down()
    {
    }//down()
}
