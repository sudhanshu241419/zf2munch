<?php

class UpdateFeedMessageActivityFeedType extends Ruckusing_Migration_Base
{
    public function up()
    {
        $this->execute("UPDATE `activity_feed_type` SET `feed_message` = '{{#restaurant_name#}} confirmed your order. Get hungry.' WHERE`activity_feed_type`.`id` =1");
    }//up()

    public function down()
    {
    }//down()
}
