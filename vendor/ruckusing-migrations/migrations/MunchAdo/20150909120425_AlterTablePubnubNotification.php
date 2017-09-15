<?php

class AlterTablePubnubNotification extends Ruckusing_Migration_Base
{
    public function up()
    {
        $this->execute('ALTER TABLE `pubnub_notification` ADD `pubnub_info` TEXT NULL AFTER `status`;');
    }//up()

    public function down()
    {
    }//down()
}
