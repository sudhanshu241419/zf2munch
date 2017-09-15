<?php

class UpdateTableUserActionSettings extends Ruckusing_Migration_Base
{
    public function up()
    {
     $this->execute("ALTER TABLE `user_action_settings` ADD `email_sent` TINYINT( 1 ) NOT NULL DEFAULT '1' COMMENT 'Email sent is active if found 1 and if found 0 than its inactive' AFTER `tips` ,
ADD `notification_sent` TINYINT( 1 ) NOT NULL DEFAULT '1' COMMENT 'Notification sent is active if found 1 and if found 0 than its inactive' AFTER `email_sent` ,
ADD `sms_sent` TINYINT( 1 ) NOT NULL DEFAULT '1' COMMENT 'Sms sent is active if found 1 and if found 0 than its inactive' AFTER `notification_sent` ;");   
    }//up()

    public function down()
    {
    }//down()
}
