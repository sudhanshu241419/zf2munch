<?php

class ChangeDataTypeInPreOrder extends Ruckusing_Migration_Base
{
    public function up()
    {
    	$this->execute("ALTER TABLE `pre_order` CHANGE `sub_total` `sub_total` DOUBLE( 5, 2 ) NULL DEFAULT '0.00'");
    	$this->execute("ALTER TABLE `pre_order` CHANGE `delivery_charges` `delivery_charges` DOUBLE( 5, 2 ) NULL DEFAULT '0.00'");
    	$this->execute("ALTER TABLE `pre_order` CHANGE `tax` `tax` DOUBLE( 5, 2 ) NULL DEFAULT '0.00'");
    	$this->execute("ALTER TABLE `pre_order` CHANGE `tip` `tip` DOUBLE( 5, 2 ) NULL DEFAULT '0.00'");
    
    }//up()

    public function down()
    {
    }//down()
}
