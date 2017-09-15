<?php

class UpdateTableRestaurantAccount extends Ruckusing_Migration_Base
{
    public function up()
    {
        $this->execute("ALTER TABLE `restaurant_accounts` ADD  `salt` VARCHAR( 100 ) NOT NULL AFTER  `user_password`;");
    }//up()

    public function down()
    {
    }//down()
}
