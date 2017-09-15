<?php

class InsertActivityFeedtype extends Ruckusing_Migration_Base
{
    public function up()
    {
        $this->execute("INSERT INTO `MunchAdo`.`activity_feed_type` (
`id` ,
`feed_type` ,
`feed_message` ,
`feed_message_others` ,
`status`
)
VALUES (
'0', 'food_crave', 'You know you want {{#menu_name#}} from {{#restaurant_name#}}', '', '1'
)");
    }//up()

    public function down()
    {
    }//down()
}
