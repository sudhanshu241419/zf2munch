<?php

class UserReservationCardDetails extends Ruckusing_Migration_Base
{
    public function up()
    {
       $this->execute("ALTER TABLE  `user_reservation_card_details` ADD  `delete_status` TINYINT( 1 ) NOT NULL DEFAULT  '0' AFTER  `stripe_cus_id` ;"); 
    }//up()

    public function down()
    {
    }//down()
}
