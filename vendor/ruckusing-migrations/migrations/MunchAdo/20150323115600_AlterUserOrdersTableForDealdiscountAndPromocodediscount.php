<?php

class AlterUserOrdersTableForDealdiscountAndPromocodediscount extends Ruckusing_Migration_Base
{
    public function up()
    {
        $this->execute("ALTER TABLE `user_orders` CHANGE `discount` `deal_discount` FLOAT NULL DEFAULT '0'");
        $this->execute("ALTER TABLE `user_orders` ADD `promocode_discount` FLOAT NOT NULL DEFAULT '0' AFTER `deal_discount`");
    }//up()

    public function down()
    {
    }//down()
}
