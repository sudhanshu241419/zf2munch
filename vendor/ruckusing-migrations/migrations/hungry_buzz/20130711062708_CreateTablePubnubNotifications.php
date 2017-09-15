<?php

class CreateTablePubnubNotifications extends Ruckusing_Migration_Base
{
     public function up()
    {
     $this->execute("CREATE TABLE IF NOT EXISTS `pubnub_notification` (
                     `id`  int(11) DEFAULT NULL,
                     `user_id` int(11) NOT NULL,
                     `notification_msg` varchar(100) NOT NULL,
                     `type` varchar(100) NOT NULL,
                     `created_on` datetime DEFAULT NULL
                    ) ENGINE=InnoDB DEFAULT CHARSET=latin1;");
   
    }//up()

    public function down()
    {
      $this->drop_table("pubnub_notification");
    }//down()
}
