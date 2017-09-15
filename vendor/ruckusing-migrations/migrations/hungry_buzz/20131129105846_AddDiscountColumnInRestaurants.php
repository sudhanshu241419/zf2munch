<?php

class AddDiscountColumnInRestaurants extends Ruckusing_Migration_Base
{
    public function up()
    {
    	$this->execute("ALTER TABLE `restaurants` ADD `discount` DECIMAL NOT NULL DEFAULT '0'");
    }//up()

    public function down()
    {
    }//down()
}
