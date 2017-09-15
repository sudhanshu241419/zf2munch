<?php

class AddActiveFeedType extends Ruckusing_Migration_Base
{
    public function up()
    {
        $this->execute(" INSERT INTO  `MunchAdo`.`activity_feed_type` (
        `id` ,
        `feed_type` ,
        `feed_message` ,
        `feed_message_others` ,
        `status`
        )
        VALUES (
        NULL ,  'invitee_places_first_order_on_MA',  'Your friend, {{#user_name#}} placed their first order! ',  '',  '1'
        ), (
        NULL ,  'invitee_places_first_order_on_DM',  'Your friend, {{#user_name#}} placed their first order! ',  '',  '1'
        )");
    }//up()

    public function down()
    {
    }//down()
}
