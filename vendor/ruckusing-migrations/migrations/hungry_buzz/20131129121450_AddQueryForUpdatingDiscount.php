<?php

class AddQueryForUpdatingDiscount extends Ruckusing_Migration_Base
{
    public function up()
    {
    	$this->execute("UPDATE restaurants rs INNER JOIN restaurant_discount rd 
					   ON rs.rest_code = rd.rest_code 
					   SET rs.discount = 1");
    }//up()

    public function down()
    {
    }//down()
}
