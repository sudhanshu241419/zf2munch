<?php

class AddEmailInUserOrder extends Ruckusing_Migration_Base
{
    public function up()
    {
    	$this->execute("alter table `user_orders` add column email varchar(255) NULL");
    }//up()

    public function down()
    {
    	$this->remove_column("user_orders", "email");
    }//down()
}
