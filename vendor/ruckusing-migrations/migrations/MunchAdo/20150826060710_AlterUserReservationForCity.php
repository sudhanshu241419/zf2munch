<?php

class AlterUserReservationForCity extends Ruckusing_Migration_Base
{
    public function up()
    {
        $this->execute("ALTER TABLE  `user_reservations` ADD  `city_id` INT NULL DEFAULT NULL AFTER  `restaurant_id`");
    }//up()

    public function down()
    {
    }//down()
}
