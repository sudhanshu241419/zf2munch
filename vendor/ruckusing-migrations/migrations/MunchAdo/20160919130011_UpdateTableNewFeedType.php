<?php

class UpdateTableNewFeedType extends Ruckusing_Migration_Base
{
    public function up()
    {
        $this->execute("UPDATE `activity_feed_type` SET `feed_message` = 'You reserved a table at {{#restaurant_name#}}. We’ve heard good things.' WHERE`activity_feed_type`.`id` =4;");
    }//up()

    public function down()
    {
    }//down()
}
