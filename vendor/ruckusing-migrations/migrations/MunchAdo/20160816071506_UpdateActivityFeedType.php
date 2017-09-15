<?php

class UpdateActivityFeedType extends Ruckusing_Migration_Base
{
    public function up()
    {
     $this->execute("UPDATE `activity_feed_type` SET  `feed_message` =  'You loved the {{#food_item#}} from {{#restaurant_name#}}.',
`feed_message_others` =  '{{#user_name#}} loved the {{#food_item#}} from {{#restaurant_name#}}.' WHERE  `activity_feed_type`.`id` =12;");   
     $this->execute("UPDATE `activity_feed_type` SET  `feed_message` =  'You loved {{#restaurant_name#}}. Is that wedding bells we hear? No, it''s doorbells.',
`feed_message_others` =  '{{#user_name#}} loved {{#restaurant_name#}}.' WHERE  `activity_feed_type`.`id` =16;");   
     $this->execute("UPDATE `activity_feed_type` SET  `feed_message` =  'You’re craving the goodness of {{#restaurant_name#}}.',
`feed_message_others` =  '{{#user_name#}} is craving the goodness of {{#restaurant_name#}}.' WHERE  `activity_feed_type`.`id` =17;");   
     $this->execute("UPDATE `activity_feed_type` SET  `feed_message` =  'You''re craving the {{#menu_name#}} from {{#restaurant_name#}} hard.
',`feed_message_others` =  '{{#user_name#}} is craving the {{#menu_name#}} from {{#restaurant_name#}} hard.' WHERE  `activity_feed_type`.`id` =51;");   
     $this->execute("UPDATE `activity_feed_type` SET  `feed_message` =  'You canceled your reservation at {{#restaurant_name#}}. They must be devastated.' WHERE `activity_feed_type`.`id` =5;");   
     $this->execute("UPDATE `activity_feed_type` SET  `feed_message` = '{{#friend_name#}} joined your reservation at {{#restaurant_name#}} in a shared quest for food greatness.',
`feed_message_others` =  '{{#friend_name#}} joined {{#user_name#}}’s reservation at {{#restaurant_name#}} in a shared quest for food greatness
' WHERE  `activity_feed_type`.`id` =6;");   
     $this->execute("UPDATE `activity_feed_type` SET  `feed_message` = '{{#friends#}} declined to join you in breaking bread at {#restaurant_name#}}. Who needs ''em anyway?' WHERE  `activity_feed_type`.`id` =7;");   
     $this->execute("UPDATE `activity_feed_type` SET  `feed_message` = '{{#host_name#}} declined to join you in breaking bread at {{#host_name#}}. Who needs ''em anyway?' WHERE  `activity_feed_type`.`id` =19;");   
     $this->execute("UPDATE `activity_feed_type` SET  `feed_message` = 'You’re on the Munch Ado map with your check in at {{#restaurant_name#}}. We’ve got your back.',
`feed_message_others` =  '{{#user_name#}} just checked in to {{#restaurant_name#}}.' WHERE  `activity_feed_type`.`id` =22;");   
     $this->execute("UPDATE `activity_feed_type` SET  `feed_message` =  'You and {{#friends#}} checked in at {{#restaurant_name#}}. Another successful social outing!
',`feed_message_others` =  '{{#user_name#}} checked in with {{#friends#}} at {{#restaurant_name#}}.' WHERE  `activity_feed_type`.`id` =26;");   
     $this->execute("UPDATE `activity_feed_type` SET  `feed_message_others` =  '{{#user_name#}} checked in at {{#restaurant_name#}} and had the {{#menu_item#}}.
' WHERE  `activity_feed_type`.`id` =24;");   
     $this->execute("UPDATE `activity_feed_type` SET  `feed_message` =  'You posted a picture of your check in at {{#restaurant_name#}}. It''s got a certain je ne sais quoi.
',`feed_message_others` =  '{{#user_name#}} checked in at {{#restaurant_name#}} and posted a pic.' WHERE  `activity_feed_type`.`id` =25;");   
     $this->execute("UPDATE `activity_feed_type` SET  `feed_message` = 'You checked in at {{#restaurant_name#}}, had the {{#menu_item#}} and posted a pic. Looks good!' WHERE  `activity_feed_type`.`id` =34;");   
     $this->execute("UPDATE `activity_feed_type` SET  `feed_message` =  'You checked in at {{#restaurant_name#}} with {{#friends#}} and ordered the {{#menu_item#}}. Good get.
',`feed_message_others` =  '{{#user_name#}} checked in at {{#restaurant_name#}} and ordered the {{#menu_item#}} with {{#friends#}}.
' WHERE  `activity_feed_type`.`id` =35;");   
     $this->execute("UPDATE `activity_feed_type` SET  `feed_message` = 'You checked in at {{#restaurant_name#}} with {{#friends#}} while ordering the {{#menu_item#}} and shared a pic. Way to multi-task!' WHERE `activity_feed_type`.`id` =36;");   
     $this->execute("UPDATE `activity_feed_type` SET  `feed_message` = 'You and {{#friends#}} checked in at {{#restaurant_name#}} and posted a pic. {{#restaurant_name#}} looks so different through your lens.',`feed_message_others` =  '{{#user_name#}} check in at {{#restaurant_name#}} with {{#friends#}} and shared a pic.' WHERE  `activity_feed_type`.`id` =52;");   
     $this->execute("UPDATE `activity_feed_type` SET  `feed_message_others` =  '{{#restaurant_name#}} read and replied to {{#user_name#}}’s review.' WHERE `activity_feed_type`.`id` =13;");   
    }//up()

    public function down()
    {
    }//down()
}
