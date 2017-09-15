<?php

class UpdateTableNewFeedType301216 extends Ruckusing_Migration_Base
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
'0',  'order_rejected_by_restaurant', 'Your takeout order from {{#restaurant_name#}} was cancelled. Don''t let it get you down. Make another, and then another and another one after that!',  '',  '1'
);");
    }//up()

    public function down()
    {
    }//down()
}
