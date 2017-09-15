<?php

class UpdateTableNewFeedTypeReservation extends Ruckusing_Migration_Base
{
    public function up()
    {
      $this->execute("UPDATE `activity_feed_type` SET `feed_message` ='{{#friends#}} declined to join you in breaking bread at {{#restaurant_name#}}. Who needs ''em anyway?' WHERE `activity_feed_type`.`id` =7;");  
    }//up()

    public function down()
    {
    }//down()
}
