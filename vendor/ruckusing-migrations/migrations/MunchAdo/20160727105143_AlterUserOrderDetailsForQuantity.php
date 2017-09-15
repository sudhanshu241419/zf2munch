<?php

class AlterUserOrderDetailsForQuantity extends Ruckusing_Migration_Base
{
    public function up()
    {
        $this->execute("ALTER TABLE `user_order_details` CHANGE `quantity` `quantity` INT(11) NULL DEFAULT NULL");
    }//up()

    public function down()
    {
    }//down()
}
