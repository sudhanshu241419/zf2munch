<?php

class AddUserIpUserOrder extends Ruckusing_Migration_Base
{
    public function up()
    {
        $this->execute("ALTER TABLE `user_orders` ADD `user_ip` VARCHAR( 255 ) NULL AFTER `lname`");
    }//up()

    public function down()
    {
    }//down()
}
