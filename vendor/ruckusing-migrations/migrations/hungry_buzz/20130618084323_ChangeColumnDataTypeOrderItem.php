<?php

class ChangeColumnDataTypeOrderItem extends Ruckusing_Migration_Base
{
    public function up()
    {
    	$this->execute("ALTER TABLE `pre_order_item` CHANGE `special_instruction` `special_instruction` TEXT  NULL DEFAULT NULL"); 
    }//up()

    public function down()
    {
    }//down()
}
