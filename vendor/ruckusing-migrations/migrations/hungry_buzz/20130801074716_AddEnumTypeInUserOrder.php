<?php

class AddEnumTypeInUserOrder extends Ruckusing_Migration_Base
{
    public function up()
    {
     $this->execute("ALTER TABLE `user_orders` CHANGE `status` `status` ENUM( 'placed', 'ordered', 'confirmed', 'delivered', 'arrived', 'cancelled', 'frozen', 'rejected' ) CHARACTER SET latin1 COLLATE latin1_swedish_ci NULL DEFAULT NULL");
    }//up()

    public function down()
    {
    }//down()
}
