<?php

class AlterUserpointRestaurantid extends Ruckusing_Migration_Base
{
    public function up()
    {
        $this->execute("ALTER TABLE `user_points`  ADD `restaurant_id` INT NOT NULL DEFAULT '0' AFTER `user_id`");
    }//up()

    public function down()
    {
    }//down()
}
