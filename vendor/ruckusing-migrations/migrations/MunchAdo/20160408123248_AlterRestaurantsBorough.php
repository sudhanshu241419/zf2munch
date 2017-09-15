<?php

class AlterRestaurantsBorough extends Ruckusing_Migration_Base
{
    public function up()
    {
        $this->execute("ALTER TABLE `restaurants`  ADD `borough` VARCHAR(50) NULL DEFAULT NULL AFTER `zipcode`");
    }//up()

    public function down()
    {
    }//down()
}
