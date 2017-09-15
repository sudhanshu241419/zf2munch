<?php

class UpdateTableNewFeedTypeReservation2 extends Ruckusing_Migration_Base
{
    public function up()
    {
        $this->execute("UPDATE `activity_feed_type` SET  `feed_message` =  '{{#restaurant_name#}} had to cancel your reservation. Don''t worry, you can book a table elsewhere!
' WHERE  `activity_feed_type`.`id` =5;");
    }//up()

    public function down()
    {
    }//down()
}
