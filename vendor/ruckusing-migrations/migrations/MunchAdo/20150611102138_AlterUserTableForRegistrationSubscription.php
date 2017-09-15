<?php

class AlterUserTableForRegistrationSubscription extends Ruckusing_Migration_Base
{
    public function up()
    {
         $this->execute("ALTER TABLE `users` ADD `registration_subscription` ENUM( '1', '0' ) NOT NULL DEFAULT '0' AFTER `bp_status` ;");
    }//up()

    public function down()
    {
    }//down()
}
