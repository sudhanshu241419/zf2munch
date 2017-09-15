<?php

class AddcoloumpubnubNotification extends Ruckusing_Migration_Base
{
    public function up()
    {
    	$this->execute("ALTER TABLE `pubnub_notification` ADD `read_status` INT NULL DEFAULT '0' COMMENT '0=>unread,1=>read' AFTER `type`");
    }//up()

    public function down()
    {
    }//down()
}
