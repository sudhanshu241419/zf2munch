<?php

class AddMilesAwayInUserOrder extends Ruckusing_Migration_Base
{
    public function up()
    {
    	$this->execute("ALTER TABLE `user_orders` ADD `miles_away` FLOAT NULL ");
    }//up()

    public function down()
    {
    	$this->remove_column("user_orders","miles_away");
    }//down()
}
