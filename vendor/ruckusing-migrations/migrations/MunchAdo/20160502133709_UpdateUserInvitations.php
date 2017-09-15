<?php

class UpdateUserInvitations extends Ruckusing_Migration_Base
{
    public function up()
    {
     $this->execute("ALTER TABLE  `user_invitations` ADD  `cronUpdate` TINYINT( 1 ) NOT NULL DEFAULT  '0' AFTER  `assignMuncher`;");
     $this->execute("update `user_invitations` set `cronUpdate`=1;");
    }//up()

    public function down()
    {
    }//down()
}
