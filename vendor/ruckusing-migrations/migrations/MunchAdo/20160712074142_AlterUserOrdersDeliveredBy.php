<?php

class AlterUserOrdersDeliveredBy extends Ruckusing_Migration_Base
{
    public function up()
    {
        $this->execute("ALTER TABLE  `user_orders` ADD  `delivered_by` VARCHAR( 20 ) NULL AFTER  `status`");
    }//up()

    public function down()
    {
    }//down()
}
