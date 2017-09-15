<?php

class ChangeColumnUserReservations extends Ruckusing_Migration_Base
{
    public function up()
    {
    	$this->execute("alter table user_reservations add unique (receipt_no)");
    }//up()

    public function down()
    {
    }//down()
}
