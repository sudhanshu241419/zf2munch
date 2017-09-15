<?php

class Addtotalamountinorder extends Ruckusing_Migration_Base
{
    public function up()
    {
    	$this->add_column('user_orders', 'total_amount', 'float');
    }//up()

    public function down()
    {
    	$this->remove_column('user_orders', 'total_amount');
    }//down()
}
