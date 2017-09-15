<?php

class ChangeColumnOrderInvitation extends Ruckusing_Migration_Base
{
    public function up()
    {
    	$this->execute("ALTER TABLE `user_order_invitation` CHANGE `from_id` `from_id` int( 11 ) NULL DEFAULT NULL");
    	$this->execute("ALTER TABLE `user_order_invitation` CHANGE `friend_email` `friend_email` varchar( 150 ) NULL DEFAULT NULL");
    }//up()

    public function down()
    {
    }//down()
}
