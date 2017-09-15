<?php

class AddOrderStatusColumnInUser extends Ruckusing_Migration_Base
{
    public function up()
    {
    	$this->execute("ALTER TABLE `users` ADD `order_msg_status` TINYINT( 1 ) NOT NULL DEFAULT '0'");
    }//up()

    public function down()
    {
    	$this->remove_column("users","order_msg_status");
    }//down()
}
