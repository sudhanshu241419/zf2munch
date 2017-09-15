<?php

class UpdateTableFeedType extends Ruckusing_Migration_Base
{
    public function up()
    {
        $this->execute("INSERT INTO `activity_feed_type` (`id` ,`feed_type` ,`feed_message` ,`feed_message_others`,`status`) VALUES ('0','checkin_with_friend_photo','You posted a pic to your check in at {{#restaurant_name#}} where you ordered the {{#friends#}}.', '{{#user_name#}} checked in at {{#restaurant_name#}} and ordered the {{#friends#}} with a side of a photo shoot.','1');");
    }//up()

    public function down()
    {
    }//down()
}
