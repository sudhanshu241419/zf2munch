<?php

class AlterUsersToAddBpstatus extends Ruckusing_Migration_Base
{
    public function up()
    {
        $this->execute("ALTER TABLE `users` ADD `bp_status` ENUM( '0', '1' ) NOT NULL DEFAULT '0' AFTER `tutorial` ;");
    }//up()

    public function down()
    {
    }//down()
}
