<?php

class CreateTableRestaurantSeats extends Ruckusing_Migration_Base
{
    public function up()
    {
    	$restaurant_seats = $this->create_table("restaurant_seats");

    		$restaurant_seats->column("restaurant_id", "integer");
    		$restaurant_seats->column("start_time", "datetime");
    		$restaurant_seats->column("end_time", "datetime");
    		$restaurant_seats->column("order_id", "integer");
    		$restaurant_seats->column("setting_time", "integer");
    		$restaurant_seats->column("type", "string");
    		$restaurant_seats->column("status", "string");
    		$restaurant_seats->finish();
    }//up()

    public function down()
    {
    	$this->drop_table("restaurant_seats");
    }//down()
}
