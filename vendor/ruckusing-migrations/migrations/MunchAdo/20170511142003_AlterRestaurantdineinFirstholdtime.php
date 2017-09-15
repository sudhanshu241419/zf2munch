<?php

class AlterRestaurantdineinFirstholdtime extends Ruckusing_Migration_Base
{
    public function up()
    {
        $this->execute("ALTER TABLE `restaurant_dinein`  ADD `first_hold_time` INT NOT NULL DEFAULT '0' AFTER `hold_time`");
    }//up()

    public function down()
    {
    }//down()
}
