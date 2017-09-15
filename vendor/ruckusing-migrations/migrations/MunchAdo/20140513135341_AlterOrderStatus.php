<?php

class AlterOrderStatus extends Ruckusing_Migration_Base
{
    public function up()
    {
    	$this->execute("ALTER TABLE  `user_orders` CHANGE  `status`  `status` ENUM(  'placed',  'ordered',  'confirmed',  'delivered',  'arrived',  'cancelled',  'rejected',  'archived' ) CHARACTER SET latin1 COLLATE latin1_swedish_ci NULL DEFAULT NULL");
    }//up()

    public function down()
    {
    }//down()
}
