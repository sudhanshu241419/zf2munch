<?php

class UpdateRestaurantAccounts extends Ruckusing_Migration_Base
{
    public function up()
    {
    $this->execute("ALTER TABLE  `restaurant_accounts` ADD  `memail` VARCHAR( 50 ) NOT NULL COMMENT  'Restaurant Manager email' AFTER  `email`");      
    }//up()

    public function down()
    {
    }//down()
}
