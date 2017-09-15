<?php

class AddColumnInUserReservations1 extends Ruckusing_Migration_Base
{
    public function up()
    {
    	$this->execute("alter table user_reservations add column receipt_no varchar(50)");
    }//up()

    public function down()
    {
    }//down()
}
