<?php

class ChangeIdTypeInPubnubNotification extends Ruckusing_Migration_Base
{
    public function up()
    {
    	$this->execute("ALTER TABLE `user_orders` CHANGE `status` `status` ENUM( 'ordered', 'confirmed', 'delivered', 'arrived', 'cancelled', 'frozen', 'rejected' ) CHARACTER SET latin1 COLLATE latin1_swedish_ci NULL DEFAULT NULL");
    	$this->execute('ALTER TABLE `pubnub_notification` ADD PRIMARY KEY ( `id` )');
    	$this->execute('ALTER TABLE `pubnub_notification` CHANGE `id` `id` INT( 11 ) NOT NULL AUTO_INCREMENT');
    }//up()

    public function down()
    {
    }//down()
}

