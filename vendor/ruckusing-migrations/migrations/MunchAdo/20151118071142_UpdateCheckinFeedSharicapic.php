<?php

class UpdateCheckinFeedSharicapic extends Ruckusing_Migration_Base
{
    public function up()
    {
        $this->execute("UPDATE `activity_feed_type` SET `feed_message` = 'You checked in at {{#restaurant_name#}} with {{#friends#}} while ordering the {{#menu_item#}} and shared a pic.' WHERE `activity_feed_type`.`id` =36");
    }//up()

    public function down()
    {
    }//down()
}
