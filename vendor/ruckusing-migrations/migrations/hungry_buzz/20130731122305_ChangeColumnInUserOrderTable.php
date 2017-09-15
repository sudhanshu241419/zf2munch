<?php

class ChangeColumnInUserOrderTable extends Ruckusing_Migration_Base
{
    public function up()
    {
    	$this->execute("ALTER TABLE `user_orders` CHANGE `zipcode` `zipcode` INT( 11 ) NULL DEFAULT NULL");
    }//up()

    public function down()
    {
    }//down()
}
