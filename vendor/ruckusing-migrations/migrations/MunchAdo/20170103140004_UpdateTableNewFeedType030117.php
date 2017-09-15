<?php

class UpdateTableNewFeedType030117 extends Ruckusing_Migration_Base
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
'0',  'tip_approved',  'You posted a tip about {{#restaurant_name#}}. Good looking out.',  '{{#user_name#}} posted a tip about {{#restaurant_name#}}.',  '1'
);"); 
     $this->execute("INSERT INTO `activity_feed_type` (
`id` ,
`feed_type` ,
`feed_message` ,
`feed_message_others` ,
`status`
)
VALUES (
'0',  'tip_disapproved',  'Your tip about {{#restaurant_name#}} was not approved. Sorry about that.',  '',  '1'
);");
     $this->execute("INSERT INTO `activity_feed_type` (
`id` ,
`feed_type` ,
`feed_message` ,
`feed_message_others` ,
`status`
)
VALUES (
'0',  'photo_approved',  'Your pics at {{#restaurant_name#}} have been added to {{#restaurant_name#}}s’ gallery. May they hang there forever.', '{{#user_name#}}''s pics at {{#restaurant_name#}} have been added to {{#restaurant_name#}}s’ gallery.',  '1'
);");
     $this->execute("INSERT INTO `activity_feed_type` (
`id` ,
`feed_type` ,
`feed_message` ,
`feed_message_others` ,
`status`
)
VALUES (
'0',  'photo_disapproved',  'Your picture from {{#restaurant_name#}} was not approved. Sorry about that.',  '',  '1'
);");
     $this->execute("ALTER TABLE  `user_restaurant_image` ADD  `cronUpdate` TINYINT( 1 ) NOT NULL DEFAULT  '0' AFTER  `sweepstakes_status_winner` ;");
     $this->execute("ALTER TABLE  `user_tips` ADD  `cronUpdate` TINYINT( 1 ) NOT NULL DEFAULT  '0' AFTER  `assignMuncher` ;");
     $this->execute("UPDATE `activity_feed_type` SET  `feed_message_others` = '{{#user_name#}}''s pics at {{#restaurant_name#}} have been added to {{#restaurant_name#}}''s gallery.' WHERE  `activity_feed_type`.`id` =71;");
    }//up()

    public function down()
    {
    }//down()
}
