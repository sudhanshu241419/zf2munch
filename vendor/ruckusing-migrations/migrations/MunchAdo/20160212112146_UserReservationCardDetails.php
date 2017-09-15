<?php

class UserReservationCardDetails extends Ruckusing_Migration_Base {

    public function up() {
        $this->execute("ALTER TABLE `user_reservation_card_details` ENGINE = MYISAM");
    }

    public function down() {
        
    }
}
