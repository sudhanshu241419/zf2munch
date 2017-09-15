<?php

class ColumnInRestaurantSeat extends Ruckusing_Migration_Base
{
    public function up()
    {
    	$this->execute("alter table restaurant_seats add column total_reserved_seats integer(5) default 0");
    }//up()

    public function down()
    {
    }//down()
}
