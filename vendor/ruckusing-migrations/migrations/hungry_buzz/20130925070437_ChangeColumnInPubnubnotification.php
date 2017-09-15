<?php

class ChangeColumnInPubnubnotification extends Ruckusing_Migration_Base
{
    public function up()
    {
    	$this->execute("ALTER TABLE `pubnub_notification` CHANGE `type` `type` TINYINT NOT NULL COMMENT '1=>order,2=>group order,3=>reservation,4=>reviews'");
    	$this->execute("ALTER TABLE `pubnub_notification` ADD `restaurant_id` INT NULL AFTER `type` ,
		ADD `channel` VARCHAR( 100 ) NULL AFTER `restaurant_id` ");
    }//up()

    public function down()
    {
    }//down()
}
