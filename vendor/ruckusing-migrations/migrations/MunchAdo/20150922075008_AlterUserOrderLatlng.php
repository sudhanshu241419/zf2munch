<?php

class AlterUserOrderLatlng extends Ruckusing_Migration_Base
{
    public function up()
    {
        $this->execute("ALTER TABLE `user_orders` ADD `latitude` DOUBLE NOT NULL AFTER `address`");
        $this->execute("ALTER TABLE `user_orders` ADD `longitude` DOUBLE NOT NULL AFTER `latitude`");
    }//up()

    public function down()
    {
    }//down()
}
