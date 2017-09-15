<?php

class Addsnagspotactivitytype extends Ruckusing_Migration_Base
{
    public function up()
    {
        $this->execute("INSERT INTO `MunchAdo`.`activity_feed_type` (`id`, `feed_type`, `feed_message`, `feed_message_others`, `status`) VALUES ('92', 'snag_a_spot_confirm', 'You snagged a spot at {{#restaurant_name#}}. We''ve heard good things.', '{{#user_name#}} snagged a spot at {{#restaurant_name#}}', '1')");
    }//up()

    public function down()
    {
    }//down()
}
