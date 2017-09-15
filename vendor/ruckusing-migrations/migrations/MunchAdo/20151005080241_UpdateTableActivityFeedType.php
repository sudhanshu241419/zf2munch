<?php

class UpdateTableActivityFeedType extends Ruckusing_Migration_Base
{
    public function up()
    {
     $this->execute("UPDATE `activity_feed_type` SET `feed_message` = '{{#friend_name#}} RSVPed \"A-doy\" to your reservation at {{#restaurant_name#}}.' WHERE `activity_feed_type`.`id` =6;");
     $this->execute("UPDATE `activity_feed_type` SET `feed_message` = '{{#friends#}} RSVPed \"No\" to your reservation at {{#restaurant_name#}}. Who needs em anyway?' WHERE `activity_feed_type`.`id` =7;");
    }//up()

    public function down()
    {
    }//down()
}
