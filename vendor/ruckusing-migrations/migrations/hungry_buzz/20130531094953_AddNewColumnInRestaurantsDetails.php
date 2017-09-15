<?php

class AddNewColumnInRestaurantsDetails extends Ruckusing_Migration_Base
{
    public function up()
    {
    	$this->execute("alter table restaurants_details add column menu_available TINYINT(1)");
    	$this->execute("alter table restaurants_details add column menu_without_price TINYINT(1)");
    	$this->execute("alter table restaurants_details add column meals TINYINT(1)");
    	$this->execute("alter table restaurants_details add column delevery_charge_type varchar(255)");
    	$this->execute("alter table restaurants_details add column dining TINYINT(1)");
    }//up()


    public function down()
    {
    }//down()
}
