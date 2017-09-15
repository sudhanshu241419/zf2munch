<?php

class AlterTableUserOrderStatus extends Ruckusing_Migration_Base
{
    public function up()
    {
        $this->execute("ALTER TABLE  `user_orders` CHANGE  `status`  `status` ENUM(  'placed',  'ordered',  'confirmed',  'delivered',  'arrived',  'cancelled',  'rejected',  'archived',  'test' ) CHARACTER SET utf8 COLLATE utf8_unicode_ci NULL DEFAULT NULL");
    }//up()

    public function down()
    {
    }//down()
}
