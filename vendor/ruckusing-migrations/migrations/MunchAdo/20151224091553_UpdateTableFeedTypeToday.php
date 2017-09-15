<?php

class UpdateTableFeedTypeToday extends Ruckusing_Migration_Base
{
    public function up()
    {
     $this->execute("UPDATE `activity_feed_type` SET `feed_message` = 'You posted a pic to your check in at {{#restaurant_name#}} with {{#friends#}}.' WHERE `activity_feed_type`.`id` =52;");  
     $this->execute("UPDATE `activity_feed_type` SET `feed_message_others` = '{{#user_name#}} checked in at {{#restaurant_name#}} with {{#friends#}} with a side of a photo shoot.' WHERE `activity_feed_type`.`id` =52;");
    }//up()

    public function down()
    {
    }//down()
}
