<?php

class AlterTableToAssignMuncher extends Ruckusing_Migration_Base
{
    public function up()
    {
        $this->execute("ALTER TABLE `user_checkin` ADD `assignMuncher` ENUM( '0', '1' ) NOT NULL DEFAULT '0' AFTER `created_at`");
        $this->execute("ALTER TABLE `user_orders` ADD `assignMuncher` ENUM( '0', '1' ) NOT NULL DEFAULT '0' AFTER `efax_sent`");
        $this->execute("ALTER TABLE `user_reservations` ADD `assignMuncher` ENUM( '0', '1' ) NOT NULL DEFAULT '0' AFTER `order_id`" );
        $this->execute("ALTER TABLE `user_invitations` ADD `assignMuncher` ENUM( '0', '1' ) NOT NULL DEFAULT '0' AFTER `invitation_status`");
        $this->execute("ALTER TABLE `user_reviews` ADD `assignMuncher` ENUM( '0', '1' ) NOT NULL DEFAULT '0' AFTER `owner_response_date`");
        $this->execute("ALTER TABLE `user_tips` ADD `assignMuncher` ENUM( '0', '1' ) NOT NULL DEFAULT '0' AFTER `created_at`");
        
    }//up()

    public function down()
    {
    }//down()
}
