<?php

class AddRestrauntNameInReservation extends Ruckusing_Migration_Base
{
    public function up()
    {
    	$this->add_column("user_reservations", "restaurant_name", "string");
    }//up()

    public function down()
    {
    	$this->remove_column("user_reservations", "restaurant_name");
    }//down()
}
