<?php

class AlterTableUserFriends extends Ruckusing_Migration_Base
{
    public function up()
    {
        $this->execute('ALTER TABLE `user_friends` ADD `invitation_id` INT( 11 ) NULL AFTER `status` ;');
    }//up()

    public function down()
    {
    }//down()
}
