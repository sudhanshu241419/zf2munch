<?php

class AlterUserReferrals extends Ruckusing_Migration_Base
{
    public function up()
    {
        $this->query("ALTER TABLE user_referrals MODIFY user_id INT NOT NULL");
        $this->query("ALTER TABLE user_referrals DROP PRIMARY KEY");
        $this->query("alter table user_referrals drop index user_id");
        $this->query("ALTER TABLE `user_referrals`  ADD `id` INT NOT NULL AUTO_INCREMENT PRIMARY KEY FIRST");
    }//up()

    public function down()
    {
    }//down()
}
