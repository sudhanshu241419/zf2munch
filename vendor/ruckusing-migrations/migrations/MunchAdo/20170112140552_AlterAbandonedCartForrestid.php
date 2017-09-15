<?php

class AlterAbandonedCartForrestid extends Ruckusing_Migration_Base
{
    public function up()
    {
        $this->execute("ALTER TABLE `abandoned_cart`  ADD `restaurant_id` INT NOT NULL AFTER `cart_data`;");
    }//up()

    public function down()
    {
    }//down()
}
