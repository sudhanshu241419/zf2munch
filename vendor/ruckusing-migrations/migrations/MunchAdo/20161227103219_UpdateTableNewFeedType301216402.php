<?php

class UpdateTableNewFeedType301216402 extends Ruckusing_Migration_Base
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
'0',  'friend_referral_ma',  'Your friend {{#inviter_name#}} has joined you in your food adventures!', '{{#invitee_name#}} joined {{#inviter_name#}} on Munch Ado and took the first step towards the future of food.',  '1'
);"); 
     
    $this->execute("INSERT INTO `activity_feed_type` (
`id` ,
`feed_type` ,
`feed_message` ,
`feed_message_others` ,
`status`
)
VALUES (
'0',  'friend_referral_dm',  'Your friend {{#inviter_name#}} joined you in {{#restaurant_name#}}’s Dine & More rewards program!', '{{#invitee_name#}} joined {{#inviter_name#}} in {{#restaurant_name#}}’s Dine & More rewards program!',  '1'
);");   
    }//up()

    public function down()
    {
    }//down()
}
