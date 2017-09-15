<?php

class UpdateactivityfeedtypeOther extends Ruckusing_Migration_Base
{
    public function up()
    {
        $this->execute('UPDATE `activity_feed_type` SET `feed_message_others`= "{{#user_name#}} picked up some takeout from {{#restaurant_name#}}." WHERE feed_type = "asap_takeout_order_confirmed"');
    }//up()

    public function down()
    {
    }//down()
}
