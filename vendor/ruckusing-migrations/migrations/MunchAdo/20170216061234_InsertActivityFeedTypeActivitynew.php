<?php

class InsertActivityFeedTypeActivitynew extends Ruckusing_Migration_Base
{
    public function up()
    {
        $this->execute("INSERT INTO `MunchAdo`.`activity_feed_type` (`id`, `feed_type`, `feed_message`, `feed_message_others`, `status`) VALUES (NULL ,  'review_approved',  'Your review of {{#restaurant_name#}} was published.',  '{{#user_name#}}\'s review of {{#restaurant_name#}} was published.',  '1'), (NULL ,  'review_disapproved',  'Your review of {{#restaurant_name#}} was not approved. Sorry about that.',  '',  '1'), (NULL ,  'reservation_cancel',  'You canceled your reservation at {{#restaurant_name#}}. They must be devastated.',  '',  '1'), (NULL ,  'reservation_reject',  '{{#restaurant_name#}} had to cancel your reservation. Don\'t worry, you can book a table elsewhere!',  '',  '1'), (NULL ,  'asap_delivery_order_confirmed',  'You ordered delivery from {{#restaurant_name#}}. Wise choice.', '{{#user_name#}} ordered delivery from {{#restaurant_name#}}.',  '1'), (NULL ,  'pre_order_delivery_confirmed',  'You ordered delivery from {{#restaurant_name#}}. Wise choice.', '{{#user_name#}} ordered delivery from {{#restaurant_name#}}.',  '1'), (NULL ,  'asap_delivery_order_cancelled',  'You canceled your delivery order from {{#restaurant_name#}}.',  '',  '1'), (NULL ,  'asap_delivery_order_rejected', 'Your delivery order from {{#restaurant_name#}} was canceled. Don\'t let it get you down. Make another, and then another and another one after that!',  '',  '1'), (NULL ,  'pre_order_delivery_canceled',  'You canceled your delivery pre-order from {{#restaurant_name#}}.',  '',  '1'), (NULL ,  'pre_order_delivery_modified',  'Your delivery pre-order at {{#restaurant_name#}} has been updated.', '{{#user_name#}} updated their delivery pre-order at {{#restaurant_name#}}.',  '1'), (NULL ,  'asap_takeout_order_confirmed',  'You picked up some takeout from {{#restaurant_name#}}. Good get. ', '{{#user_name#}} picked up some takeout from {{#restaurant_name}}.',  '1'), (NULL ,  'pre_order_takeout_order_confirmed',  'You picked up some takeout from {{#restaurant_name#}}. Good get. ', '{{#user_name#}} picked up some takeout from {{#restaurant_name#}}.',  '1'), (NULL ,  'asap_takeout_order_cancelled',  'You canceled your takeout order from {{#restaurant_name#}}. No food for you.',  '',  '1')");
    }//up()

    public function down()
    {
    }//down()
}
