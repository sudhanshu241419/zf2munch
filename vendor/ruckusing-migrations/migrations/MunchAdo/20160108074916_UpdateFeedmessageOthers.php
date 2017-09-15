<?php

class UpdateFeedmessageOthers extends Ruckusing_Migration_Base
{
    public function up()
    {
        $this->execute("UPDATE `MunchAdo`.`activity_feed_type` SET `feed_message_others` ='{{#user_name#}} loved the {{#food_item#}} from {{#restaurant_name#}}. Judge their taste by trying it yourself.' WHERE `activity_feed_type`.`id`=12");
    }//up()

    public function down()
    {
    }//down()
}
