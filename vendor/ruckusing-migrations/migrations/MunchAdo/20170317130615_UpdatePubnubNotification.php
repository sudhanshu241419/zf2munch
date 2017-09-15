<?php

class UpdatePubnubNotification extends Ruckusing_Migration_Base
{
    public function up()
    {
        $this->execute('update `pubnub_notification` set `read_status`=1 where `channel` like "%dashboard_%"');
    }//up()

    public function down()
    {
    }//down()
}
