<?php

class AddcolumnInUserReservations extends Ruckusing_Migration_Base
{
    public function up()
    {
    	$this->execute("alter table user_reservations add column first_name varchar(100)");
    	$this->execute("alter table user_reservations add column last_name varchar(100)");
    	$this->execute("alter table user_reservations add column phone varchar(20)");
    	$this->execute("alter table user_reservations add column email varchar(255)");
    	$this->execute("alter table user_reservations add column reserved_seats integer(5)");
    }//up()

    public function down()
    {
    }//down()
}
