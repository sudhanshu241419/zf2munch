<?php

class AddSalesTaxInCities extends Ruckusing_Migration_Base
{
    public function up()
    {
    	$this->execute("ALTER TABLE `cities` ADD `sales_tax` FLOAT( 5, 2 ) NULL COMMENT 'values calculated in %' AFTER `longitude` ");
    }//up()

    public function down()
    {
    	$this->remove_column("cities","sales_tax");
    }//down()
}
