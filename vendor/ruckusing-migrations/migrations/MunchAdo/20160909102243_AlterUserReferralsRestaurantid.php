<?php

class AlterUserReferralsRestaurantid extends Ruckusing_Migration_Base
{
    public function up()
    {
        $this->execute("ALTER TABLE `user_referrals`  ADD `restaurant_id` INT NOT NULL DEFAULT '0' AFTER `inviter_id`");
    }//up()

    public function down()
    {
    }//down()
}
