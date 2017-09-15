<?php

class AlterRestaurantServerStatus extends Ruckusing_Migration_Base
{
    public function up()
    {
        $this->execute("ALTER TABLE `restaurant_servers`  ADD `status` TINYINT(1) NOT NULL DEFAULT '0' AFTER `date`");
    }//up()

    public function down()
    {
    }//down()
}
