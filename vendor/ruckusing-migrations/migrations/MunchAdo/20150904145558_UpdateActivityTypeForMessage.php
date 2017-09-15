<?php

class UpdateActivityTypeForMessage extends Ruckusing_Migration_Base
{
    public function up()
    {
        $this->execute("UPDATE `activity_feed_type` SET `feed_message` = 'You checked in at {{#restaurant_name#}} and ordered the {{#menu_item#}}.' WHERE `activity_feed_type`.`id` =24");
        $this->execute("UPDATE `activity_feed_type` SET `feed_message_others` = '{{#user_name#}} is enjoying a taste of the good life with the {{#menu_item#}} at {{#restaurant_name#}}.' WHERE `activity_feed_type`.`id` =24");
        $this->execute("UPDATE `activity_feed_type` SET `feed_message` = 'You posted a pic to your check in at {{#restaurant_name#}} where you ordered the {{#menu_item#}}.' WHERE `activity_feed_type`.`id` =34");
        $this->execute("UPDATE `activity_feed_type` SET `feed_message_others` = '{{#user_name#}} checked in at {{#restaurant_name#}} and ordered the {{#menu_item#}} with a side of a photo shoot.' WHERE `activity_feed_type`.`id` =34");
        $this->execute("UPDATE `activity_feed_type` SET `feed_message` = 'You checked in at {{#restaurant_name#}} with {{#friends#}} and ordered the {{#menu_item#}}.' WHERE `activity_feed_type`.`id` =35");
        $this->execute("UPDATE `activity_feed_type` SET `feed_message_others` = '{{#user_name#}} checked in at {{#restaurant_name#}} and ordered the {{#menu_item#}} with {{#friends#}}.' WHERE `activity_feed_type`.`id` =35");
        $this->execute("UPDATE `activity_feed_type` SET `feed_message` = 'You checked in at {{#restaurant_name#}} with {{#friends#}} while ordering the {{#menu_item#}} and sharing a pic.' WHERE `activity_feed_type`.`id` =36");
        $this->execute("UPDATE `activity_feed_type` SET `feed_message_others` = '{{#user_name#}} shared a pic while checking in at {{#restaurant_name#}} and ordering {{#menu_item#}} with {{#friends#}}.' WHERE `activity_feed_type`.`id` =36");
        $this->execute("UPDATE `activity_feed_type` SET `feed_type` = 'friend_accept_reservation_invite' WHERE `activity_feed_type`.`id` =6");
    }//up()

    public function down()
    {
    }//down()
}
