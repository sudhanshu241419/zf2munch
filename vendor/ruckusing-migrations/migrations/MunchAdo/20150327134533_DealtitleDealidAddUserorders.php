<?php

class DealtitleDealidAddUserorders extends Ruckusing_Migration_Base
{
    public function up()
    {
        $this->execute("ALTER TABLE `user_orders` ADD `deal_id` BIGINT NOT NULL AFTER `deal_discount` ,
ADD `deal_title` VARCHAR( 255 ) NOT NULL AFTER `deal_id`");
    }//up()

    public function down()
    {
    }//down()
}
