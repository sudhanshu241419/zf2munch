<?php

class AlterUserPromocodeTableForRestaurantId extends Ruckusing_Migration_Base
{
    public function up()
    {
        $this->execute("ALTER TABLE  `user_promocodes` ADD  `restaurant_id` BIGINT NULL AFTER  `order_id`");
    }//up()

    public function down()
    {
    }//down()
}
