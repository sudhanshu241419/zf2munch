<?php

class AlterPubnubNotificationfortype extends Ruckusing_Migration_Base
{
    public function up()
    {
        $this->execute("ALTER TABLE `pubnub_notification` CHANGE `type` `type` TINYINT( 4 ) NOT NULL COMMENT'0=>others,1=>order,2=>group order,3=>reservation,4=>reviews,6=>accept_friendship,7=>tip,8=>upload_photo,9=>bookmark'");
    }//up()

    public function down()
    {
    }//down()
}
