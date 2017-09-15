<?php

class UpdateTablePubnubNotification extends Ruckusing_Migration_Base
{
    public function up()
    {
        $this->execute("ALTER TABLE `pubnub_notification` ADD `cronUpdate` TINYINT( 1 ) NOT NULL DEFAULT '0' AFTER `pubnub_info` ;");
        $this->execute("update `pubnub_notification` set `cronUpdate`='1'");
    }//up()

    public function down()
    {
    }//down()
}
