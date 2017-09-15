<?php

class ModifyActivityFeedMessage extends Ruckusing_Migration_Base
{
    public function up()
    {
        $this->execute("UPDATE `MunchAdo`.`activity_feed_type` SET `feed_message` = '{{#restaurant_name#}} will be on its way in just an hour and half!' WHERE `activity_feed_type`.`id` =1");
        $this->execute("UPDATE `MunchAdo`.`activity_feed_type` SET `feed_message` = 'We''re reading your review of {{#restaurant_name#}} with bated breath.' WHERE `activity_feed_type`.`id` =9
");
        $this->execute("UPDATE `MunchAdo`.`activity_feed_type` SET `feed_message` = 'You''re a good tipper...right? Review your tip for {{#restaurant_name#}} and think about it.' WHERE `activity_feed_type`.`id` =10");
        $this->execute("UPDATE `MunchAdo`.`activity_feed_type` SET `feed_message` = 'That''s so sweet! Or Salty! Either way, we hope you and {{#restaurant_name#}} make it work!' WHERE `activity_feed_type`.`id` =12");
        $this->execute("UPDATE `MunchAdo`.`activity_feed_type` SET `feed_message` = 'You''re going to {{#restaurant_name#}}!' WHERE `activity_feed_type`.`id` =20");
        $this->execute("UPDATE `MunchAdo`.`activity_feed_type` SET `feed_message` = 'You''re on Munch Ado at {{#restaurant_name#}}. We''ve got your back.' WHERE `activity_feed_type`.`id` =22");
        $this->execute("UPDATE `MunchAdo`.`activity_feed_type` SET `feed_message_others` = '{{#user_name#}} posted a pic of {{#restaurant_name#}}. It''s got a certain je ne sais quoi.' WHERE `activity_feed_type`.`id` =25");
        $this->execute("UPDATE `MunchAdo`.`activity_feed_type` SET `feed_message` = '{{#friend_name#}} RSVPed A-doy to your reservation at {{#restaurant_name#}}.' WHERE `activity_feed_type`.`id` =6");
        $this->execute("UPDATE `MunchAdo`.`activity_feed_type` SET `feed_message` = '{{#friends#}} RSVPed No to your reservation at {{#restaurant_name#}}. Who needs em anyway?' WHERE `activity_feed_type`.`id` =7");
        $this->execute("UPDATE `MunchAdo`.`activity_feed_type` SET `feed_message` = 'You''ve mastered chopsticks, chowed down on all the chop suey you could find and conquered this diverse continent of cuisine. Just donâ€™t get too cocky and try to invade Russia in the winter.' WHERE `activity_feed_type`.`id` =37");
        $this->execute("UPDATE `MunchAdo`.`activity_feed_type` SET `feed_message` = 'You and your favorite meals are all grass fed and drowning in flaxseed oil and you celebrate life''s greatest moments by popping in an exercise tape or blu-ray. You''ve found your inner peace and made yourself into a mild muncher.' WHERE `activity_feed_type`.`id` =38");
        $this->execute("UPDATE `MunchAdo`.`activity_feed_type` SET `feed_message` = 'You spend your down time dreaming up new places to hide cheese in pizza crust and are an expert on everything from Hawaiian to New York Style. You''ve had pizza toppings piled so high you almost have to use a fork and knife. Almost, but never.' WHERE `activity_feed_type`.`id` =39");
        $this->execute("UPDATE `MunchAdo`.`activity_feed_type` SET `feed_message` = 'It''s a rare day you go without a grilled meat sandwich. You''ve got it down to a science of sans onions and extra pickles and know exactly where to find the best burger within walking distance.' WHERE `activity_feed_type`.`id` =40");
        $this->execute("UPDATE `MunchAdo`.`activity_feed_type` SET `feed_message` = 'The card and table is set declaring you''re about to arrive. The napkins are folded just the way you like and the chef is already starting preparations on your usual because you know how to tip a kitchen and wait staff as well as you know how to schmooze' WHERE `activity_feed_type`.`id` =41");
        $this->execute("UPDATE `MunchAdo`.`activity_feed_type` SET `feed_message` = 'You''re dining attire often consist of nothing more than PJs and slippers, but that''s the way weekend warriors roll before happy hour. Reheating is more your style, because baking and cooking require concentration which you can''t tear away from the l' WHERE `activity_feed_type`.`id` =42");
        $this->execute("UPDATE `MunchAdo`.`activity_feed_type` SET `feed_message` = 'You''re and adventurous eater who doesn''t like to stay still or stand on lines. We get it and you get it too. Paper or plastic is as silly a question as to stay or to go. You''re making your move and all the hot food is coming home with you.' WHERE `activity_feed_type`.`id` =43");
        $this->execute("UPDATE `MunchAdo`.`activity_feed_type` SET `feed_message` = 'You''ve provided enough of your two cents to receive this comment from the Munch Ado Crew: keep doing what you''re doing. Like a movie or food critic, everyone''s got their own taste and yours is being noticed. Keep up the good work and you may just ge' WHERE `activity_feed_type`.`id` =44");
        $this->execute("UPDATE `MunchAdo`.`activity_feed_type` SET `feed_message` = 'You''ve mastered chopsticks, chowed down on all the chop suey you could find and conquered this diverse continent of cuisine. Just don''t get too cocky and try to invade Russia in the winter.' WHERE `activity_feed_type`.`id` =37");
        
        
        
    }//up()

    public function down()
    {
    }//down()
}
