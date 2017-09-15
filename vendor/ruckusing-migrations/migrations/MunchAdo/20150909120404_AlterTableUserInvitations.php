<?php

class AlterTableUserInvitations extends Ruckusing_Migration_Base
{
    public function up()
    {
        $this->execute("ALTER TABLE `user_invitations` CHANGE `invitation_status` `invitation_status` ENUM( '0', '1', '2', '3' ) CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL DEFAULT '0' COMMENT '0=invitation, 1=Accept, 2=Decline, 3=Unfriend';");
    }//up()

    public function down()
    {
    }//down()
}
