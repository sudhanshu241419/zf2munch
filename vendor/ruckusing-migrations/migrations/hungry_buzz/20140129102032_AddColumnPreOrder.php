<?php

class AddColumnPreOrder extends Ruckusing_Migration_Base
{
    public function up()
    {
    	$this->execute("ALTER TABLE `pre_order` ADD `order_type2` ENUM( 'p', 'b' ) NOT NULL DEFAULT 'p' COMMENT 'p-personal,b-bussiness' AFTER `order_type1`");
    }//up()

    public function down()
    {
    }//down()
}
