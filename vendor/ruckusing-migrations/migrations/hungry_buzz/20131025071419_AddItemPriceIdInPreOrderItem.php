<?php

class AddItemPriceIdInPreOrderItem extends Ruckusing_Migration_Base
{
    public function up()
    {
    	$this->execute("ALTER TABLE `pre_order_item` ADD `item_price_id` INT NOT NULL DEFAULT '0'");
    }//up()

    public function down()
    {
    }//down()
}
