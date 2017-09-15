<?php

class UpdateCheckinfeed extends Ruckusing_Migration_Base
{
    public function up()
    {
        $this->execute("UPDATE `activity_feed_type` SET `feed_message` = 'You\'re on the Munch Ado map at {{#restaurant_name#}}. We\'ve got your back.' WHERE `activity_feed_type`.`id` =22");
    }//up()

    public function down()
    {
    }//down()
}
