<?php

class UpdateTableUserOrderNotification extends Ruckusing_Migration_Base
{
    public function up()
    {
        $this->execute("ALTER TABLE `user_orders` ADD `cronUpdateNotification` TINYINT( 1 ) NOT NULL DEFAULT '0' AFTER `cronUpdate` ;");
    }//up()

    public function down()
    {
    }//down()
}
