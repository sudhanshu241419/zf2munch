<?php

class UserInvitationsNewFieldInvitationStatus extends Ruckusing_Migration_Base
{
    public function up()
    {
         $this->execute("ALTER TABLE `user_invitations` ADD `invitation_status` ENUM( '0', '1', '2' ) NOT NULL DEFAULT '0' COMMENT '0=invitation, 1=Accept, 2=Decline' AFTER `status`");
    }//up()

    public function down()
    {
    }//down()
}
