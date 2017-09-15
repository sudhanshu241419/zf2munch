<?php

class UpdateFeedMessage extends Ruckusing_Migration_Base
{
    public function up()
    {
        $this->execute("UPDATE `MunchAdo`.`activity_feed_type` SET `feed_message` ='You RSVPed not a chance to {{#host_name#}} at {{#restaurant_name#}}.' WHERE `activity_feed_type`.`id` =19");
    }//up()

    public function down()
    {
    }//down()
}
