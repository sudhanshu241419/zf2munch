<?php

class AlterTableUserReferralMailstatus extends Ruckusing_Migration_Base
{
    public function up()
    {
        $this->execute("ALTER TABLE  `user_referrals` ADD  `mail_status` TINYINT( 4 ) NOT NULL DEFAULT '0'");
    }//up()

    public function down()
    {
    }//down()
}
