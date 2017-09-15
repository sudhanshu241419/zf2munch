<?php

class AlterUserAccountSrvSource extends Ruckusing_Migration_Base
{
    public function up()
    {
        $this->execute("ALTER TABLE `user_account` CHANGE `user_source` `user_source` ENUM('fb','tw','ws','gp','iOS','sms','srv') CHARACTER SET latin1 COLLATE latin1_swedish_ci NULL DEFAULT NULL COMMENT 'srv=server'");
    }//up()

    public function down()
    {
    }//down()
}
