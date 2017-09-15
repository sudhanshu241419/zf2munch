<?php

class AddOrderPassThroughFieldRestaurants extends Ruckusing_Migration_Base
{
    public function up()
    {
        $this->execute("ALTER TABLE `restaurants` ADD `order_pass_through` TINYINT NOT NULL DEFAULT '0' COMMENT 'If order_pass_through is 1 then cc detail will saved in table and payment will not done' AFTER `delivery_geo`");
    }//up()

    public function down()
    {
    }//down()
}
