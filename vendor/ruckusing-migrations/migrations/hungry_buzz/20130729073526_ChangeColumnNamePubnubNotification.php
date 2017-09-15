<?php

class ChangeColumnNamePubnubNotification extends Ruckusing_Migration_Base
{
    public function up()
    {

    	$this->execute("ALTER TABLE `pubnub_notification` CHANGE `notofication_msg` `notification_msg` VARCHAR( 255 ) CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL");
    	$this->execute("ALTER TABLE `pubnub_notification` CHANGE `created_on` `created_on` DATETIME NULL DEFAULT NULL");
    	$this->execute("ALTER TABLE `pubnub_notification` ADD `user_id` INT NULL AFTER `id`");
    }//up()


    public function down()
    {
    }//down()
}
