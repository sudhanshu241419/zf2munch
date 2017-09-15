<?php

class UpdateTableNewFeedType120117 extends Ruckusing_Migration_Base
{
    public function up()
    {
        $this->execute("INSERT INTO `activity_feed_type` (
`id` ,
`feed_type` ,
`feed_message` ,
`feed_message_others` ,
`status`
)
VALUES (
'0',  'reservation_modification',  'You’ve updated your reservation at {{#restaurant_name#}}. Mark your calendar.', '{{#user_name#}} updated their reservation at {{#restaurant_name#}}.',  '1'
);
");
    }//up()

    public function down()
    {
    }//down()
}
