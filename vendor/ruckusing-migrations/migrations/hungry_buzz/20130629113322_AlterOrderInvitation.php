<?php

class AlterOrderInvitation extends Ruckusing_Migration_Base
{
    public function up()
    {
    	$this->execute("ALTER TABLE `user_order_invitation` CHANGE `msg_status` `msg_status` ENUM( '0', '1', '2', '3' ) CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL COMMENT '0=>invited, 1=>accepted, 2=>denied, 3=>submitted'");
    }//up()

    public function down()
    {
    }//down()
}
