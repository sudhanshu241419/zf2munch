<?php

class AddCreatedOnUserInvitation extends Ruckusing_Migration_Base
{
    public function up()
    {
    	$this->execute("ALTER TABLE `user_order_invitation` ADD `created_on` TIMESTAMP NULL ");
    }//up()

    public function down()
    {
    	$this->remove_column("user_order_invitation","created_on");
    }//down()
}
