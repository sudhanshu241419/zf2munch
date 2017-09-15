<?php

class AlterRestaurantidRestaurantAccount extends Ruckusing_Migration_Base
{
    public function up()
    {
        $this->execute("ALTER TABLE `restaurant_accounts` ADD UNIQUE (`restaurant_id`)");
    }//up()

    public function down()
    {
    }//down()
}
