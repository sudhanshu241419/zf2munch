<?php

class CreateTableFeedType extends Ruckusing_Migration_Base
{
    public function up()
    {
$this->execute("INSERT INTO `activity_feed_type` (`id`, `feed_type`, `feed_message`, `feed_message_others`, `status`) VALUES (NULL, 'new_register', 'You joined Munch Ado and took your first step towards the future of food.', '{{#user_name#}} joined Munch Ado and took the first step towards the future of food.', '1');");    
$this->execute("INSERT INTO `activity_feed_type` (`id`, `feed_type`, `feed_message`, `feed_message_others`, `status`) VALUES (NULL, 'accept_friendship', 'Today, you and {{#inviter#}} became better friends in life, on the internet and in food by becoming friends on Munch Ado.', 'Today, {{#user#}} and {{#inviter#}} became better friends in life, on the internet and in food by becoming friends on Munch Ado.', '1');");
$this->execute("INSERT INTO `activity_feed_type` (`id`, `feed_type`, `feed_message`, `feed_message_others`, `status`) VALUES (NULL, 'restaurant_been', 'You’ve tried {{#restaurant_name#}}. Yum.', '{{#user_name#}} has tried {{#restaurant_name#}}', '1');");      
$this->execute("INSERT INTO `activity_feed_type` (`id`, `feed_type`, `feed_message`, `feed_message_others`, `status`) VALUES (NULL, 'food_try', 'You had the {{#menu_name#}} from {{#restaurant_name#}} marking yet another food conquest.', '{{#user_name#}} had the {{#menu_name#}} from {{#restaurant_name#}}.', '1');");  
$this->execute("INSERT INTO `activity_feed_type` (`id`, `feed_type`, `feed_message`, `feed_message_others`, `status`) VALUES (NULL, 'reservation_invite', '{{#inviter#}} invited you to their reservation. Lucky you!', '', '1');");
$this->execute("INSERT INTO `activity_feed_type` (`id`, `feed_type`, `feed_message`, `feed_message_others`, `status`) VALUES (NULL, 'user_cancel_reservation', '{{#friend_name#}} broke up the band by canceling the reservation at {{#restaurant_name#}}', '', '1');");
$this->execute("INSERT INTO `activity_feed_type` (`id`, `feed_type`, `feed_message`, `feed_message_others`, `status`) VALUES (NULL, 'friend_accept_prepaid_reservation_invite', '{{#friend_name#}} has joined you for a pre-paid reservation at {{#restaurant_name#}}. You two are on the bleeding edge of the future of food.', '{{#friend_name#}} has joined {{#user_name#}} for a pre-paid reservation at {{#restaurant_name#}}.', '1');");
$this->execute("INSERT INTO `activity_feed_type` (`id`, `feed_type`, `feed_message`, `feed_message_others`, `status`) VALUES (NULL, 'friend_deny_prepaid_reservation_invite', '{{#friends#}} declined your generous pre-paid reservation invitation to {{#restaurant_name#}}. Drats.', '', '1');");
$this->execute("INSERT INTO `activity_feed_type` (`id`, `feed_type`, `feed_message`, `feed_message_others`, `status`) VALUES (NULL, 'friend_referal', '{{#user_name#}} just used your referral code. Can you taste the $30?', '', '1');");
$this->execute("INSERT INTO `activity_feed_type` (`id`, `feed_type`, `feed_message`, `feed_message_others`, `status`) VALUES (NULL, '$5_friend_referal', '{{#user_name#}} just saved $5 on their first order because of you. You''re the best friend anyone could ask for.', '', '1');");
$this->execute("INSERT INTO `activity_feed_type` (`id`, `feed_type`, `feed_message`, `feed_message_others`, `status`) VALUES (NULL, '$30_friend_referal', 'Your friends just earned you $30 towards amazing eats. Thanks for letting them know about us!', '{{#user_name#}}’s friends just earned {{#user_name#}} $30 towards amazing eats.', '1');");
$this->execute("INSERT INTO `activity_feed_type` (`id`, `feed_type`, `feed_message`, `feed_message_others`, `status`) VALUES (NULL, '$30_another_friend_referal', 'Your friends just earned you another $30 towards amazing eats. Thanks for letting them know about us!', '{{#user_name#}}’s friends just earned {{#user_name#}} another $30 towards amazing eats.', '1');");    
    }//up()

    public function down()
    {
    }//down()
}
