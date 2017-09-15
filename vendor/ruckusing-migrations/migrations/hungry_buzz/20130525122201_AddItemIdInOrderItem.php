<?php

class AddItemIdInOrderItem extends Ruckusing_Migration_Base
{
    public function up()
    {
    	//$this->add_column("pre_order_item", "item_id", "integer");
    	//$this->execute('ALTER TABLE `pre_order_item` CHANGE `item_id` `item_id` INT( 11 ) NULL');
    }//up()

    public function down()
    {
    	$this->remove_column("pre_order_item", "item_id", "integer");
    }//down()
}
