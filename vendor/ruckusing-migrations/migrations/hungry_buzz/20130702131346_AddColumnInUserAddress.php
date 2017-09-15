<?php

class AddColumnInUserAddress extends Ruckusing_Migration_Base
{
    public function up()
    {
    	$this->execute('ALTER TABLE `user_addresses` ADD `zipcode` INT( 6 ) NULL AFTER `city` ');
    }//up()

    public function down()
    {
    	$this->remove_column("user_addresses","zipcode");
    }//down()
}
