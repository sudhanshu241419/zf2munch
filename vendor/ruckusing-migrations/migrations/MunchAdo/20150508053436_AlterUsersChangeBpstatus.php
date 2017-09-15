<?php

class AlterUsersChangeBpstatus extends Ruckusing_Migration_Base
{
    public function up()
    {
        $this->execute("ALTER TABLE `users` CHANGE `bp_status` `bp_status` TINYINT( 5 ) NULL DEFAULT NULL ;");
    }//up()

    public function down()
    {
    }//down()
}
