<?php

class ColumnInRestaurant extends Ruckusing_Migration_Base
{
    public function up()
    {
    	$this->execute("alter table restaurants add column total_seats integer(5) default 0");
    }//up()

    public function down()
    {
    }//down()
}
