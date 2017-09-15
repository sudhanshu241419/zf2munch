<?php

class UpdateTableNewFeedType3012160744 extends Ruckusing_Migration_Base
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
'0',  'register_dm',  'You joined {{#restaurant_name#}}''s Dine & More rewards program!', '{{#user_name#}} joined {{#restaurant_name#}}''s Dine & More rewards program!',  '1'
);");
    }//up()

    public function down()
    {
    }//down()
}
