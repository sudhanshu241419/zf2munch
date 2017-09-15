<?php

class SalesTaxForAustin extends Ruckusing_Migration_Base
{
    public function up()
    {
    	$this->execute("UPDATE `hungry_buzz`.`cities` SET `sales_tax` = '8.25' WHERE `cities`.`city_name` ='Austin' AND `state_code` = 'TX';");
    }//up()

    public function down()
    {
    }//down()
}
