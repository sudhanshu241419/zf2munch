<?php

class CreateTableActivityFeedType extends Ruckusing_Migration_Base
{
    public function up()
    {
       /* $this->execute("CREATE TABLE IF NOT EXISTS `activity_feed_type` (
        `id` int(11) NOT NULL AUTO_INCREMENT,
        `feed_type` varchar(255) NOT NULL,
        `feed_message` tinytext NOT NULL,
        `feed_message_others` tinytext NOT NULL,
        `status` int(1) NOT NULL DEFAULT '1' COMMENT '1=>actvie,0=>inactive',
        PRIMARY KEY (`id`)
      ) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=37 ;");
        
        $this->execute("INSERT INTO `activity_feed_type` (`id`, `feed_type`, `feed_message`, `feed_message_others`, `status`) VALUES
        (1, 'order_confirm', '{{#restaurant_name#}} confirmed your order. Get hungry.', '', 1),
        (3, 'order_cancel', 'Your order from {{#restaurant_name#}} was canceled. Don''t let it get you down. Make another, and then another and another one after that!', '', 1),
        (4, 'reservation_confirmation', 'Reservation reserved at {{#restaurant_name#}}. Good get.', '', 1),
        (5, 'reservation_canceled_by_restaurant', 'Your reservation at {{#restaurant_name#}} was canceled quicker than a classic sitcom on FOX.', '', 1),
        (6, 'friend_accept_reservation_invite', '{{#friend_name#}} RSVPed “A-doy” to your reservation at {{#restaurant_name#}}.', '', 1),
        (7, 'friend_deny_reservation_invite', '{{#friends#}} RSVPed “No” to your reservation at {{#restaurant_name#}}. Who needs ‘em anyway?', '', 1),
        (8, 'buy_deal', 'Ooo, nice deal. The marketing geniuses at\r\n{{#restaurant_name#}} will be pleased.', '', 0),
        (9, 'post_review', 'We’re reading your review of {{#restaurant_name#}} with bated breath.', '', 1),
        (10, 'left_tip', 'You’re a good tipper...right? Review your tip for {{#restaurant_name#}} and think about it.', '', 1),
        (11, 'upload_photo', 'Nice job photog! Your pics at {{#restaurant_name#}} will go down in history.', '', 1),
        (12, 'food_loved', 'That’s so sweet! Or Salty! Either way, we hope you and {{#restaurant_name#}} make it work!', '{{#user_name#}} loved the food item from {{#restaurant_name#}}. Judge their taste by trying it yourself.', 1),
        (13, 'restaurant_review_response', '{{#restaurant_name#}} read and replied to your review. Eeep!', '', 1),
        (14, 'pre_order_delivery', 'Your pre-order from {{#restaurant_name#}} will be on its way in just an hour and half!', '', 2),
        (15, 'pre_order_takeout', 'Your takeout pre-order from {{#restaurant_name#}} will start preparations in an hour and half!', '', 2),
        (16, 'restaurant_loved', 'Aww, its so nice to see true love between you and {{#restaurant_name#}}.', 'OOO. Check it out, {{#user_name#}} loves {{#restaurant_name#}}. Is that wedding bells we hear? No, it’s doorbells.', 1),
        (17, 'restaurant_crave', 'You know you want {{#restaurant_name#}}.', '', 1),
        (18, 'badge_unlock_needed', 'Pump up your Munch Ado stats with just\r\n{{#number#}} more {{#action#}}', '', 1),
        (19, 'reservation_invite_deny', 'You RSVPed not a chance to Host at {{#restaurant_name#}}.', '', 1),
        (20, 'reservation_invite_accept', 'You’re going to {{#restaurant_name#}}!', '', 1),
        (21, 'avatar_earned', '{{##avatar_name#}} unlocked. Check out your new avatar!', '', 1),
        (22, 'checkin', 'You’re on Munch Ado at {{#restaurant_name#}}. We’ve got your back.', '', 1),
        (23, 'checkin_with_tip', 'You posted a tip about {{#restaurant_name#}}. Good looking out.', '{{#user_name#}} posted a tip about {{#restaurant_name#}}.', 1),
        (24, 'checkin_with_menu', 'You checked in at {{#restaurant_name#}} and ordered the menu item.', '{{#user_name#}} is enjoying a taste of the good life with the menu item at {{#restaurant_name#}}.', 1),
        (25, 'checkin_with_photo', 'You posted a picture of your check in at {{#restaurant_name#}}.', '{{#user_name#}} posted a pic of {{#restaurant_name#}}. It’s got a certain je ne sais quoi.', 1),
        (26, 'checkin_with_friend', 'You and {{#friends#}} checked in at {{#restaurant_name#}}. how was it?', '{{#user_name#}} is with {{#friends#}} at {{#restaurant_name#}}. Adorable.', 1),
        (27, 'checkin_with_tip_menu', 'You ordered the menu item at {{#restaurant_name#}} and left a tip. You so nice.', '{{#user_name#}} is at {{#restaurant_name#}} and got the menu item while leaving a tip. Showoff!', 1),
        (28, 'checkin_with_tip_photo', 'You shared a pic and a tip when you checked in at {{#restaurant_name#}}.', '{{#user_name#}} took a pic and shared a tip at {{#restaurant_name#}}.', 1),
        (29, 'checkin_with_tip_friend', 'You and {{#friends#}} checked in to {{#restaurant_name#}} and shared a tip.', '{{#user_name#}} and {{#friends#}} are at {{#restaurant_name#}} and {{#user_name#}} shared a tip. Hope it was consensual.', 1),
        (30, 'checkin_with_tip_menu_photo', 'You ordered the menu item at {{#restaurant_name#}} while posting a pic and a tip. Multitasker.', '{{#user_name#}} got the menu item at {{#restaurant_name#}} and posted a pic and a tip.', 1),
        (31, 'checkin_with_tip_menu_friend', 'You ordered the menu item at {{#restaurant_name#}} with {{#friends#}} and left a tip.', '{{#user_name#}} and {{#friends#}} checked in at {{#restaurant_name#}}. {{#user_name#}} left a tip and ordered the\r\nmenu item.', 1),
        (32, 'checkin_with_tip_photo_friend', 'You and {{#friends#}} checked in at {{#restaurant_name#}} and you shared pics and tips.', '{{#user_name#}} and {{#friends#}} are at {{#restaurant_name#}} sharing pics and tips.', 1),
        (33, 'checkin_with_tip_menu_photo_friend', 'You checked in with {{#friends#}} at {{#restaurant_name#}}, posted a tip, shared a photo and ate the menu item.', '{{#user_name#}} and {{#friends#}} are out at {{#restaurant_name#}}. {{#user_name#}} ordered the menu item, shared a\r\nphoto, and posted a tip. Must have built up quite the appetite.', 1),
        (34, 'checkin_with_menu_photo', 'You posted a pic to your check in at {{#restaurant_name#}} where you ordered the menu item.', '{{#user_name#}} checked in at {{#restaurant_name#}} and\r\nordered the menu item with a side of a photo shoot.', 1),
        (35, 'checkin_with_menu_friend', 'You checked in at {{#restaurant_name#}} with {{#friends#}} and ordered the menu item.', '{{#user_name#}} checked in at {{#restaurant_name#}} and ordered the menu item with {{#friends#}}.', 1),
        (36, 'checkin_with_menu_photo_friend', 'You checked in at {{#restaurant_name#}} with {{#friends#}} while ordering the menu item and sharing a pic.', '{{#user_name#}} shared a pic while checking in at {{#restaurant_name#}} and ordering menu item with {{#friends#}}.', 1);");*/
    }//up()

    public function down()
    {
    }//down()
}
