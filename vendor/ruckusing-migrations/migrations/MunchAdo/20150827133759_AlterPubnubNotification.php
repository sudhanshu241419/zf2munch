<?php

class AlterPubnubNotification extends Ruckusing_Migration_Base
{
    public function up()
    {
        $this->execute("ALTER TABLE `pubnub_notification` ADD `status` TINYINT NOT NULL DEFAULT '0' AFTER `created_on`");
    }//up()

    public function down()
    {
    }//down()
}
